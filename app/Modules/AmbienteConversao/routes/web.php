<?php

use App\Aluno;
use App\Autodeclaracao_Deficiencia;
use App\Autodeclaracao_Raca;
use App\CPF;
use App\Curso;
use App\Cidade;
use App\Estado;
use App\Lead;
use App\Campanha;
use App\Campanha_Tag;
use App\Unidade;
use App\Prova;
use App\Prova_Data;
use App\Midia_Tipo;
use App\Settings;

use App\System\Email;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

// Middleware de dados padrão
$middle_dados = function ($req, Closure $next) use ($module) {
	$dados = [];

	// Pasta de recursos
	$dir = isset($module->options['diretorio']) ? $module->options['diretorio'] : $dados['campanha'];
	$dir = APP_BASE . '/ambiente_conversao/' . $dir;

	// Vestibular atual
	$campanha = Campanha::find($module->options['campanha']);

	$dados['campanha'] = $campanha->id;
	$dados['diretorio'] = $dir;

	// TAGS personalizadas

	$tags = Campanha_Tag::with('campanha')->get()->filter(function ($tag) use ($campanha, $req) {
		if ($tag->campanha->id == $campanha->id) {

			$pagina = $tag->pagina;
			$c_path = $req->path();

			// Pré-filtrar
			$chars = ' \t\n\r\0\x0B/\\';
			$pagina = str_replace('/', '\/', trim($pagina, $chars));
			$c_path = trim($c_path, $chars);

			// Catch-all
			if ($pagina == '*')
				return true;

			// Validar
			if (strrpos($pagina, '*')) {
				// Ativar REGEX
				preg_match('/' . $pagina . '/', $c_path, $matches);
				if (!empty($matches))
					return true;
			} else {
				// Página padrão
				if ($pagina == $c_path)
					return true;
			}
		}
		return false;
	});

	$head = '';
	$foot = '';
	foreach ($tags as $tag) {
		if ($tag->topo)
			$head .= $tag->codigo;
		else
			$foot .= $tag->codigo;
	}

	View::share('tags', [
		'head' => $head,
		'foot' => $foot
	]);

	// Views e Templates
	View::addNamespace($module->namespace, $dir . '/views/');

	// Compartilhar dados com View
	View::share('dados', $dados);
	View::share('opcoes', $module->options);
	View::share('cursos', $campanha->cursos);
	View::share('settings', Settings::get_all());

	$req->session()->put('obj', $dados);
	return $next($req);
};

Route::group(['middleware' => [$middle_dados]], function () use ($module) {
	Route::get('/', function (Request $req) use ($module) {
		$dados = [];

		// Quando a opção "desativar" for marcada, não exibir a home, porém deixar outras páginas ainda acessíveis
		if (isset($module->options['desativar']) && $module->options['desativar']) {
			if (!empty($module->options['redirecionar']))
				return redirect($module->options['redirecionar']);
			return view('AmbienteConversao::desativado');
		}

		if ($req->query('e'))
			$dados['error'] = $req->query('e');

		return view('AmbienteConversao::index', $dados);
	});

	// Candidato

	Route::post('/candidato', function (Request $req) use ($module) {
		$dados = $req->session()->get('obj');
		$campanha = Campanha::find($module->options['campanha']);
		$post = $req->aluno;

		// Converte CPF em numérico, para processamento no BD

		$cpf = $post['cpf'];

		// Validar CPF
		if (!CPF::validate($cpf)) {
			return redirect('/?e=CPF Inválido');
		}

		$aluno = Aluno::porCPF($cpf);

		$req->session()->put('candidato', $aluno);

		return redirect('/candidato');
	});
	Route::get('/candidato', function (Request $req) use ($module) {
		$candidato = $req->session()->get('candidato');
		$campanha = Campanha::find($module->options['campanha']);

		if (is_null($candidato))
			return redirect('/');

		$dados = [
			'aluno' => $candidato,
			'provas' => $candidato->provas()->where('campanha_id', $campanha->id)->with('leads', 'leads.curso')->get(),
			'incompletos' => $candidato->leads()->where('campanha_id', $campanha->id)->where('status_id', 'LEAD')->with('curso')->get()
		];

		return view('AmbienteConversao::candidato', $dados);
	});
	Route::get('/candidato/editar', function (Request $req) use ($module) {
		$candidato = $req->session()->get('candidato');
		$campanha = Campanha::find($module->options['campanha']);

		if (is_null($candidato))
			return redirect('/');

		$prova = $candidato->provas()->where('campanha_id', $campanha->id)->with('leads', 'leads.curso')->first();
		$lead = $prova->leads->reverse()->first();

		$req->session()->put('lead', $lead);

		return redirect('/inscricao?edit=1');
	});
	Route::get('/candidato/editar/{id}', function (Request $req, $id) use ($module) {
		$candidato = $req->session()->get('candidato');
		$lead = Lead::find($id);

		if (is_null($candidato) || is_null($lead) || $lead->aluno_id != $candidato->id)
			return redirect('/');

		$req->session()->put('lead', $lead);

		return redirect('/inscricao');
	});

	// Inscrição

	Route::post('/inscricao', function (Request $req) use ($module) {
		$dados = $req->session()->get('obj');
		$campanha = Campanha::find($module->options['campanha']);
		$post = $req->aluno;

		// Converte CPF em numérico, para processamento no BD

		$cpf = $post['cpf'];

		// Validar CPF
		if (!CPF::validate($cpf)) {
			return redirect('/?e=CPF Inválido');
		}

		$aluno = Aluno::porCPF($cpf);

		$curso = Curso::where('codigo', $req->curso)->first();

		// Aluno não cadastrado? Criamos um cadastro antes de atualizar os dados

		$testEmail = Aluno::where('email', $req->input('aluno.email'))->first();
		if (is_null($aluno)) {
			// Validar E-mail
			if (!is_null($testEmail)) {
				return redirect('/?e=Email já cadastrado com outro CPF');
			}

			if (count(explode(' ', $post['nome'])) < 2) {
				return redirect('/?e=Por favor, insira seu nome completo');
			}

			$aluno = new Aluno([
				'nome' => $post['nome'],
				'cpf' => $cpf
			]);
		} else {
			// Validar E-mail
			if (!is_null($testEmail) && $testEmail->id != $aluno->id) {
				return redirect('/?e=Email já cadastrado com outro CPF. E-mail atual: ' . $aluno->email);
			}
		}

		// Não permitir mudança de CPF ou de Nome

		unset ($post['nome']);
		unset ($post['cpf']);

		// Atualizar dados

		foreach ($post as $prop => $valor) {
			$aluno->{$prop} = $valor;
		}

		// Validar E-mail
		if (empty($aluno->email))
			return redirect('/?e=E-mail Inválido');

		$aluno->save();

		// Procurar leads do aluno para esse curso, nessa campanha

		$leads = Lead::where(['aluno_id' => $aluno->id, 'curso_id' => $curso->id, 'campanha_id' => $campanha->id]);

		// Se não existir, cria um lead

		if (!$leads->count()) {
			$lead = new Lead();
			$lead->aluno()->associate($aluno->id);
			$lead->campanha()->associate($campanha->id);
			$lead->curso()->associate($curso->id);

			// Retrocompatibilidade
			$lead->opcao_curso_1 = $curso->id;

			// Copiar dados do último lead deste candidato
			$extra = $aluno->leads()->where('status_id', '!=', 'LEAD')->orderBy('updated_at', 'DESC')->first();
			if (!is_null($extra)) {
				// Verificação extra because REASONS
				if ($lead->campanha_id == $extra->campanha_id)
					$lead->prova()->associate($extra->prova);
				
				$lead->midia()->associate($extra->midia);
				$lead->dados_adicionais = $extra->dados_adicionais;

				// Segunda Inscricao
				$lead->dados_adicionais('segunda_inscricao', true);
			}

			$lead->save();

			$lead->converter('LEAD', 'Cadastro via Ambiente de Conversão [E-mail: ' . $aluno->email . ']');
		}

		$lead = $leads->first();
		$req->session()->put('lead', $lead);

		return redirect('/inscricao');
	});

	Route::get('/inscricao', function (Request $req) use ($module) {
		$lead = $req->session()->get('lead');

		if (is_null($lead))
			return redirect('/');

		$lead = Lead::find($lead->id);

		if (is_null($lead))
			return redirect('/');

		// dd($lead->aluno);

		$unidades = $lead->campanha->unidades;
		// $unidade = Unidade::find(1); // TODO: implementar unidades nas campanhas

		// $locais_provas = $unidade->locais_provas;
		$locais_provas = $lead->campanha->locais_provas;

		$estados = [];
		foreach(Estado::cursor() as $estado) {
			$estados[$estado->uf] = $estado->cidades;
		}

		$dados = [
			'lead' => $lead,
			'aluno' => $lead->aluno,
			'curso' => $lead->curso,
			'cidades' => $estados,
			'estados' => Estado::all(),
			'locais_prova' => $locais_provas,
			'unidade' => $unidades[0],
			'campanha' => $lead->campanha,
			'editando' => isset($_GET['edit'])
		];

		return view('AmbienteConversao::inscricao', $dados);
	});

	Route::post('/inscricao/finaliza', function (Request $req) use ($module) {
		$dados = $req->session()->get('obj');
		$lead = $req->session()->get('lead');
		$lead = Lead::find($lead->id);
		$post = $req->aluno;

		// Atualizar data updated_at
		$lead->touch();

		$dados_adicionais = [];
		if (isset($req->dados_adicionais))
			$dados_adicionais = $req->dados_adicionais;

		$lead->dados_adicionais = $dados_adicionais;

		$estado = Estado::where('uf', $req->uf)->first();
		$cidade = $estado->cidades()->where('nome', $req->cidade)->first();

		$aluno = $lead->aluno;
		$aluno->cidade()->associate($cidade);

		// Não permitir mudança de CPF ou de Nome

		unset ($post['nome']);
		unset ($post['cpf']);

		// Atualizar dados

		foreach ($post as $prop => $valor) {
			$aluno->{$prop} = $valor;
		}

		$lead->midia()->associate(Midia_Tipo::find($req->conheceu));

		// Procurar provas deste lead

		if (is_null($lead->prova)) {
			// Criar objeto de prova

			$prova = new Prova();
			$prova->aluno()->associate($aluno);
			$prova->campanha()->associate($lead->campanha);
			$prova->curso()->associate($lead->curso);
			$prova->data()->associate(Prova_Data::find($req->data));

			$prova->save();

			// Associa a prova e faz conversão do lead

			$lead->prova()->associate($prova);
		} else {
			// Reagendamento

			$prova = $lead->prova;

			$prova->data()->associate(Prova_Data::find($req->data));
			$prova->save();
		}

		// Validação
		if (
			is_null ($lead->prova) ||
			is_null ($lead->prova->data) ||
			is_null ($lead->midia) || 
			is_null ($lead->curso) || 
			is_null ($lead->aluno) ||
			is_null ($lead->aluno->cidade)
		) return redirect('/inscricao');

		$lead->converter('INSC', 'Inscrição via Ambiente de Conversão [Prova: ' . $lead->prova->data->hora . ']');

		// Salvar dados

		$aluno->save();
		$lead->save();

		// Preparar e-mail

		// Identidade de Gênero
		$assunto = 'Seja bem-vind' . $aluno->genero_letra . ', ' . $aluno->primeiro_nome . '!';

		// Preparar dados
		$dados_email = array_merge([
			'aluno' => $aluno,
			'lead' => $lead,
			'curso' => $lead->curso,
			'prova' => $lead->prova,
			'local' => $lead->prova->local,
			'modulo' => $module
		]);

		// Criar e-mail
		$email = Email::create($assunto)
			->smtp_auth()
			->from('no-reply@vestibularfam.com.br', 'Vestibular FAM')
			->to($aluno->email, $aluno->nome)
			->html(view('AmbienteConversao::bem-vindo', $dados_email)->render());

		// Enviar
		$email->send();

		return redirect('/inscricao/finaliza');
	});

	Route::get('/inscricao/finaliza', function (Request $req) use ($module) {
		$lead = $req->session()->get('lead');

		// WTF
		if (is_null($lead)) return redirect('/inscricao');

		$lead = Lead::find($lead->id);

		if (is_null($lead))
			return redirect('/inscricao');

		$dados = [
			'lead' => $lead,
			'aluno' => $lead->aluno,
			'curso' => $lead->curso,
			'prova' => $lead->prova,
			'dados_adicionais' => $lead->dados_adicionais
		];

		return view('AmbienteConversao::finaliza', $dados);
	});

	Route::post('/inscricao/adicionais', function (Request $req) use ($module) {
		$cpf = $req->input('cpf');
		$aluno = Aluno::porCPF($cpf);

		if (is_null($aluno))
			return redirect('/');

		// Dados do aluno

		$aluno->endereco = $req->input('endereco');
		$aluno->numero = $req->input('numero');
		$aluno->complemento = $req->input('complemento');
		$aluno->bairro = $req->input('bairro');

		// Salvar dados adicionais

		foreach ($req->dados_adicionais as $prop => $val) {
			$aluno->dados_adicionais($prop, $val);
		}

		// Salvar aluno

		$aluno->save();

		$aluno->converter('Preencheu Dados Adicionais');

		// Redirecionar

		return view('AmbienteConversao::finaliza-adicionais');
	});

	Route::get('/inscricao/adicionais', function (Request $req) use ($module) {
		$cpf = $req->input('cpf');
		$aluno = Aluno::porCPF($cpf);

		if (is_null($aluno))
			return redirect('/');

		$dados = [
			'aluno' => $aluno,
			'deficiencias' => Autodeclaracao_Deficiencia::all(),
			'racas' => Autodeclaracao_Raca::all()
		];

		return view('AmbienteConversao::adicionais', $dados);
	});

	Route::post('/resultados', function (Request $req) use ($module) {
		$cpf = $req->input('cpf');
		$aluno = Aluno::porCPF($cpf);

		$req->session()->put('aluno', $aluno);

		return redirect ('/resultados');
	});

	Route::get('/resultados', function (Request $req) use ($module) {
		$aluno = $req->session()->get('aluno');

		if (is_null($aluno))
			return view ('AmbienteConversao::resultados');

		return view ('AmbienteConversao::resultados', [
			'provas' => $aluno->provas->sortByDesc('data'),
			'aluno' => $aluno
		]);
	});

	Route::any('{all?}', function (Request $req, $file) use ($module) {
		$dados = $req->session()->get('obj');

		$file_path = $dados['diretorio'] . '/assets/' . $file;
		// return File::get($file_path);

		// Retornar 404 se arquivo não existir
		if (!file_exists($file_path))
			abort(404);

		// Criar cache de mime-type, 7 dias
		$mime = Cache::remember('mime-' . md5($file_path), 60 * 24 * 7, function () use ($file_path) {
			// Pega o Mime-Type do arquivo
			return fn_mime_content_type($file_path);
		});

		return response()->file($file_path, ['Content-Type' => $mime]);
	})->where('all', '.+');
});

// Isso faz o sistema não buscar pastas padrão de views nem de recursos
$IGNORE_FILES = true;