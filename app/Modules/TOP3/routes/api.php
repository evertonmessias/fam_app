<?php

use App\Aluno;
use App\Campanha;
use App\Curso;
use App\Helpers;
use App\Prova;
use App\Lead;

use App\System\API_Key;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$token = function (Request $req, $next) {
	$token = $req->query('token');
	$passcode = $req->input('passcode');

	if (is_null($token))
		return response('Acesso negado: Token de acesso inválido.', 401)->header('Content-Type', 'text/plain');

	if ($token = API_Key::auth($token))
		$req->token = $token;
	else
		return response('Acesso negado: Token de acesso inválido.', 401)->header('Content-Type', 'text/plain');

	if (!is_null($passcode)) {
		if ($passcode = API_Key::auth($token, $passcode))
			$req->token = $passcode;
		else
			return response('Acesso negado: Token de acesso inválido.', 401)->header('Content-Type', 'text/plain');
	}

	return $next($req);
};

// API pública

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
Route::group(['prefix' => 'app'], function () {
	// Admin -> Atualizar Dados Locais
	Route::get('/update-local-data', function () {
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
			'perguntas' => $perguntas,
			'areas' => $areas,
			'version' => date('Y-m-d')
		]);
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
Route::get('/questions-research', function () {
	$questoes = DB::table('perguntas_pesquisas')->where("identifier", "top")->orderBy('ordem', 'ASC')->get();
	$questoes = $questoes->transform(function ($questao) {
		$questao->respostas = json_decode($questao->respostas);
		return $questao;
	});
	return $questoes;
});