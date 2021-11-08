<?php

use App\Aluno;
use App\Curso;
use App\RDStation_Univestibular;

use App\System\API_Key;
use App\System\API_Totp;
use App\System\Event;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Dados da API
$api_key = isset($module->options['api_key']) ? $module->options['api_key'] : null;
$api_time = 15 * 60;

// API pública e autenticação TOTP com CORS
$cors = function (Request $req, $next) {
	header("Access-Control-Allow-Origin: *");

	// ALLOW OPTIONS METHOD
	$headers = [
		'Access-Control-Allow-Methods'=> 'POST, GET, OPTIONS, PUT, DELETE',
		'Access-Control-Allow-Headers'=> 'Content-Type, Accepts, Origin'
	];
	
	if ($req->getMethod() == "OPTIONS") {
		// The client-side application can set only headers allowed in Access-Control-Allow-Headers
		return Response::make('OK', 200, $headers);
	}

	$response = $next($req);
	foreach($headers as $key => $value)
		$response->header($key, $value);
	return $response;
};
$totpTokenCheck = function (Request $req, $next) use ($api_key, $api_time) {
	$api_key = API_Key::where('key', $api_key)->first();
	$totp = $req->input('token');

	if (!API_Totp::validate($api_key, $totp, $api_time)) 
		return response('Acesso negado: Token de acesso inválido.', 401)->header('Content-Type', 'text/plain');

	return $next($req);
};

Route::group(['prefix' => 'public'], function () {
	// Cadastro one-step
	Route::get('/onestep/{email}', function ($email) {
		$a = Aluno::where('email', $email)->first();

		if (is_null($a))
			return null;

		return [
			'nome' => $a->nome,
			'cpf' => $a->cpf,
			'celular' => $a->celular,
			'curso_latest' => $a->leads[0]->curso->codigo
		];
	});
});

// API standalone
Route::group(['prefix' => 'app', 'middleware' => [$cors]], function () use ($api_key, $api_time, $totpTokenCheck) {
	// Admin -> Atualizar Dados Locais
	Route::get('/update-local-data', function () use ($api_key, $api_time) {
		// Gerar token TOTP
		$api_key = API_Key::where('key', $api_key)->first();
		$api_totp = API_Totp::generate($api_key, $api_time);

		// Pegar perguntas
		$perguntas = DB::table('top_perguntas')->orderBy('order', 'ASC')->get();
		$perguntas = $perguntas->transform(function ($pergunta) {
			$pergunta->respostas = json_decode($pergunta->respostas);
			return $pergunta;
		});

		// Pegar áreas e cursos
		$areas = DB::table('cursos_categorias')->get();
		$areas = $areas->transform(function ($area) {
			$area->cursos = Curso::select('nome', 'landing_page')->where('categoria_id', $area->id)->get();
			return $area;
		})->keyBy('id');

		// Retornar
		return response()->json([
			'token' => $api_totp['public_string'],
			'perguntas' => $perguntas,
			'areas' => $areas,
			'version' => date('Y-m-d')
		]);
	});

	// Admin -> Atualizar Dados Locais
	Route::group(['middleware' => [$totpTokenCheck]], function () {
		Route::post('/upload-lead', function (Request $req) {
			// Obter dados do lead
			$lead = $req->input('lead');

			// Descobrir a Área
			$area = DB::table('cursos_categorias')->where('id', $lead['resultado'])->first();

			// Descobrir se o lead é de algum candidato
			$aluno = Aluno::where('email', $lead['email'])->first();
			if (!is_null($aluno))
				$aluno = $aluno->id;

			// Preparar colunas do banco
			$dados = [
				'email' => $lead['email'],
				'aluno_id' => $aluno,
				'resultados' => json_encode($lead['score_raw']),
				'raw' => json_encode($lead)
			];
			
			// Salvar no banco
			DB::table('top_resultados')->insert($dados);
			
			// Registrar evento nos logs
			$event = Event::register('top-finished', 'Teste de Orientação Profissional');
			$event->meta('email', $dados['email']);
			$event->meta('area', $area->id);
			
			// Preparar dados RD Station
			if(isset($lead['email'])) {
				$dados_rd = [];
				$dados_rd['email'] = $lead['email'];
				$dados_rd['top_area_id'] = $area->id;
				$dados_rd['top_area'] = $area->nome;

				if(isset($lead['nome'])) $dados_rd['nome'] = $lead['nome'];

				// Converter no RD Station
				if (!env('APP_DEBUG')) {
					$rd = new RDStation_Univestibular ();
					$rd->converter_manual($lead['email'], 'Teste de Orientação Profissional', $dados_rd); // Converte o Lead
				}
			}

			// Confirmação que foi salvo, o resto será exibido no front-end
			return [ 'success' => true ];
		});
	});
});

Route::get('/questions', function () {
	$questoes = DB::table('top_perguntas')->orderBy('order', 'ASC')->get();
	$questoes = $questoes->transform(function ($questao) {
		$questao->respostas = json_decode($questao->respostas);
		return $questao;
	});
	return $questoes;
});