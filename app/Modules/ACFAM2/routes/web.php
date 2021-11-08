<?php

use App\Aluno;
use App\Autodeclaracao_Deficiencia;
use App\Autodeclaracao_Raca;
use App\Celular;
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
use App\Telefone;

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
	View::addNamespace('AmbienteConversao', $dir . '/views/');

	// Compartilhar dados com View


	$cursograd = [];
	$cursotec = [];
	$cursodist = [];
	$cursopres = [];
	$cursototal = [];

	foreach ($campanha->cursos as $curso) {
		if ($curso->id != 79) $cursototal[] = $curso;
	}

	foreach ($campanha->cursos as $curso) {
		if ($curso->id != 79) {
			if ($curso->id == 49 || $curso->id == 63 || $curso->id == 67 || $curso->id == 56 || $curso->id == 57 || $curso->id == 52 || $curso->id == 50 || $curso->id == 65 || $curso->id == 54 || $curso->id == 59 || $curso->id == 61 || $curso->id == 84 || $curso->id == 85 || $curso->id == 86 || $curso->id == 87 || $curso->id == 88 || $curso->id == 89 ) {
				$cursotec[] = $curso;
			}
		}
	}
	foreach ($campanha->cursos as $curso) {
		if ($curso->id != 79) {
			if ($curso->id == 69 || $curso->id == 77 ||  $curso->id == 76 || $curso->id == 74 ||  $curso->id == 75) {
				$cursodist[] = $curso;
			}
		}
	}
	foreach ($campanha->cursos as $curso) {
		if ($curso->id != 79) {
			if ($curso->id != 49 && $curso->id != 63 && $curso->id != 67 && $curso->id != 56 && $curso->id != 57 && $curso->id != 52 && $curso->id != 50 && $curso->id != 65 && $curso->id != 54 && $curso->id != 59 && $curso->id != 61  && $curso->id != 84 && $curso->id != 85 && $curso->id != 86 && $curso->id != 87 ) {
				$cursograd[] = $curso;
			}
		}
	}
	foreach ($campanha->cursos as $curso) {
		if ($curso->id != 79) {
			if ($curso->id != 49 && $curso->id != 63 && $curso->id != 67 && $curso->id != 56 && $curso->id != 57 && $curso->id != 52 && $curso->id != 50 && $curso->id != 65 && $curso->id != 54 && $curso->id != 59 && $curso->id != 61 && $curso->id != 69 && $curso->id != 77 &&  $curso->id != 76 && $curso->id != 74 &&  $curso->id != 75) {
				$cursopres[] = $curso;
			}
		}
	}


	View::share('dados', $dados);
	View::share('opcoes', $module->options);
	View::share('cursostotal', $cursototal);
	View::share('cursosgrad', $cursograd);
	View::share('cursostec', $cursotec);
	View::share('cursosdist', $cursodist);
	View::share('cursospres', $cursopres);
	View::share('settings', Settings::get_all());

	$req->session()->put('obj', $dados);
	return $next($req);
};

Route::group(['middleware' => [$middle_dados]], function () use ($module) {
	Route::get('/', function (Request $req) use ($module) {
		$dados = [];

		// Limpar sessões sempre que passar pela home
		$req->session()->flush();

		// Regenerar Token CSRF
		$req->session()->regenerateToken();

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

	// Apaga CPF (precisa de login) ***************************************

	Route::get('/apagaCPF', function (Request $req) use ($module) {

		return view('AmbienteConversao::apagaCPF');
	});

	Route::post('/apaga', function (Request $req) {

		$cpf = $req->input('cpf');
		$senha = $req->input('senha');
		// senha gerada pelo gordinho //IeVeeGh4ae
		if ($senha == "admin@fam#") {

			$host = getenv("DB_HOST");
			$user = getenv("DB_USERNAME");
			$pass = getenv("DB_PASSWORD");
			$database = getenv("DB_DATABASE");

			$conexao = new mysqli($host, $user, $pass, $database);

			$sql = "select * from alunos where cpf = $cpf";
			$consulta = $conexao->query($sql);

			if ($consulta) {

				$resultado = $consulta->fetch_assoc();
				$id = $resultado['id'];

				$sql1 = "DELETE FROM alunos WHERE id = $id";
				$sql2 = "DELETE FROM leads WHERE aluno_id = $id";
				$conexao->query("SET foreign_key_checks = 0");
				$apagado1 = $conexao->query($sql1);
				$apagado2 = $conexao->query($sql2);

				if (!$apagado1) {
					$frase = "ERRO ao apagar cpf";
				} else if (!$apagado2) {
					$frase = "ERRO ao apagar lead";
				} else {
					$frase = "CPF " . $resultado['cpf'] . " apagado!";
				}
			} else {
				$frase = "CPF não encontrado !";
			}
		} else {
			$frase = "SENHA INCORRETA !!!";
		}
		$dados = [
			'frase' => $frase
		];

		return view('AmbienteConversao::apagado', $dados);
	});

	// Inscrição Vestibular ***************************************

	Route::post('/inscricao', function (Request $req) use ($module) {
		$dados = $req->session()->get('obj');
		$campanha = Campanha::find($module->options['campanha']);
		$inscrito = $req->session()->get('aluno_inscrito');

		// Se estiver inscrito, voltar
		if ($inscrito) {
			return redirect('/inscricao/finaliza');
		}

		// Converte CPF em numérico, para processamento no BD

		$cpf = $req->input('cpf');
		$ingresso = $req->input('ingresso');
		$distancia = $req->input('distancia');

		$infosCandidato = $req->input('candidato');
		if (is_null($infosCandidato) || !is_array($infosCandidato)) $infosCandidato = [];

		// Validar CPF
		if (!CPF::validate($cpf)) {
			return redirect('/?e=CPF Inválido');
		}

		// Validar celular
		if (isset($infosCandidato['celular']) && strlen($infosCandidato['celular']) < 14) {
			return redirect('/?e=Celular inválido');
		}

		$aluno = Aluno::porCPF($cpf);


		// Aluno não cadastrado? Criamos um cadastro antes de atualizar os dados

		if (is_null($aluno)) {
			$aluno = new Aluno(array_merge([
				'cpf' => $cpf
			]));
		}

		if (isset($infosCandidato['nome']))
			$aluno->nome = $infosCandidato['nome'];

		if (isset($infosCandidato['sobrenome']))
			$aluno->sobrenome = $infosCandidato['sobrenome'];

		if (isset($infosCandidato['email']))
			$aluno->email = $infosCandidato['email'];

		if (isset($infosCandidato['celular']))
			$aluno->celular = $infosCandidato['celular'];

		$aluno->save();

		$curso = $req->input('curso') ? $req->input('curso') : null;
		if ($curso)
			$curso = Curso::find($curso);

		// Procurar leads do aluno para essa campanha
		$leads = $aluno->leads()->where('campanha_id', $campanha->id)->get();

		// Se não existir, cria um lead
		if (!$leads->count()) {
			$lead = new Lead();
			$lead->aluno()->associate($aluno->id);
			$lead->campanha()->associate($campanha->id);

			// Curso
			if ($curso) {
				$lead->curso()->associate($curso);
				$lead->opcao_curso_1 = $curso->id;
			}

			$lead->save();

			// Criar conversão no RD
			$lead->converter('LEAD', 'Cadastro via Ambiente de Conversão');
		} else if ($leads->first()->status->base_id > 0) {
			// Se existir e não estiver como LEAD, redirecionar o candidato para página de "Acompanhar Insrição"
			return redirect('/inscricao/acompanhar/' . $aluno->cpf);
		}

		// Atualizar lista de leads
		$leads = Lead::where(['aluno_id' => $aluno->id, 'campanha_id' => $campanha->id])->get();

		// Atualizar sessão
		$req->session()->put('aluno', $aluno);
		$req->session()->put('leads', $leads);
		$req->session()->put('campanha', $campanha);
		$req->session()->put('ingresso', $ingresso);
		$req->session()->put('distancia', $distancia);
		if ($curso) {
			$req->session()->put('curso', $curso->id);
		} else {
			$req->session()->put('curso', -1);
		}

		// Redirecionar
		return redirect('/inscricao');
	});

	Route::get('/inscricao', function (Request $req) use ($module) {
		$aluno = $req->session()->get('aluno');
		$ingresso = $req->session()->get('ingresso');
		$distancia = $req->session()->get('distancia');
		$leads = $req->session()->get('leads');
		$campanha = $req->session()->get('campanha');
		$inscrito = $req->session()->get('aluno_inscrito');
		$curso = $req->session()->get('curso');

		if (is_null($aluno) || is_null($leads) || !$leads->count())
			return redirect('/');

		if ($inscrito)
			return redirect('/inscricao/finaliza');

		// Listar Unidades
		$unidades = $campanha->unidades;

		// Listar datas de provas
		$data_provas = array();
		$listaprovas = Prova_Data::all();
		$hoje = date('Y/m/d 23:59:59');
		foreach ($listaprovas as $prova) {
			if (strtotime($prova->hora) > strtotime($hoje) && $prova->id != 2895 && $prova->id != 2896) {
				$data_provas[] = array($prova->id, date_format(date_create($prova->hora), "d/m/Y - H:i:s"));
			}
		} //echo json_encode($data_provas);

		// Listar Locais de Prova
		$locais_provas = $campanha->locais_provas;

		// Preencher dados do aluno que faltaram
		$aluno['nome'] = $aluno->nome;
		$aluno['sobrenome'] = $aluno->sobrenome;
		$aluno['telefone_ddd'] = isset($aluno->telefone) ? trim(substr($aluno->telefone, 1, 2)) : '';
		$aluno['telefone_numero'] = isset($aluno->telefone) ? trim(substr($aluno->telefone, 4, strlen($aluno->telefone) - 3)) : '';
		$aluno['celular_ddd'] = isset($aluno->celular) ? trim(substr($aluno->celular, 1, 2)) : '';
		$aluno['celular_numero'] = isset($aluno->celular) ? trim(substr($aluno->celular, 4, strlen($aluno->celular) - 3)) : '';
		$aluno['data_provas'] = $data_provas;
		$aluno['ingresso'] = $ingresso;
		$aluno['distancia'] = $distancia;
		//$aluno['campos_enem'] = $aluno->getCamposEnem();

		// Lead atual
		$lead = $leads->first();

		$dados = [
			'aluno' => $aluno,
			'lead' => $lead,
			'locais_prova' => $locais_provas,
			'unidade' => $unidades,
			'campanha' => $campanha,
			'curso' => $curso,
			'ingresso' => $ingresso,
			'distancia' => $distancia
		];

		if ($req->query('e'))
			$dados['error'] = $req->query('e');

		if (isset($_GET['debug']) && $_GET['debug'] == 'dump') dd($dados);
		return view('AmbienteConversao::inscricao', $dados);
	});

	Route::post('/inscricao/finaliza', function (Request $req) use ($module) {

		$campanha = $req->session()->get('campanha');
		$dados = $req->session()->get('obj');
		$leads = $req->session()->get('leads');
		$aluno = $req->session()->get('aluno');
		$post = $req->input('candidato');
		$dados_adicionais = $req->input('dados_adicionais');

		$opcoes_curso = collect($req->input('opcoes_curso'))->transform(function ($id) {
			return Curso::find($id);
		});
		$primeira_opcao = $opcoes_curso[0];

		$midia = Midia_Tipo::find($req->input('como_conheceu'));
		$data_prova = Prova_Data::find($req->input('data_prova'));


		// Cidade/Estado
		$cidade = Cidade::find($post['cidade']);
		$estado = $cidade->estado;

		$aluno->cidade()->associate($cidade);

		// Não permitir mudança de CPF
		unset($post['cpf']);

		$dadosRaw = $req->all();

		// Verificar se estamos usando Nome + Sobrenome

		if (!empty($req->input('candidato.primeiro_nome'))) {
			$post['nome'] = $dadosRaw['candidato']['nome'];
			$post['sobrenome'] = $dadosRaw['candidato']['sobrenome'];
		}

		// Verificar se estamos usando DDD + Numero no Celular
		if (!empty($req->input('candidato.celular_ddd'))) {
			$post['celular'] = $dadosRaw['candidato']['celular'] = trim(
				$req->input('candidato.celular_ddd') .
					' ' .
					$req->input('candidato.celular_numero')
			);
		}

		// Verificar se estamos usando DDD + Numero no Telefone
		if (!empty($req->input('candidato.telefone_ddd'))) {
			$post['telefone'] = $dadosRaw['candidato']['telefone'] = trim(
				$req->input('candidato.telefone_ddd') .
					' ' .
					$req->input('candidato.telefone_numero')
			);
		}

		// Formatar corretamente número de celular
		if (isset($dadosRaw['candidato']) && isset($dadosRaw['candidato']['celular'])) {
			$post['celular'] = $dadosRaw['candidato']['celular'] = (new Celular($dadosRaw['candidato']['celular']))->formatted();
		}

		// Formatar corretamente número de telefone
		if (isset($dadosRaw['candidato']) && isset($dadosRaw['candidato']['telefone'])) {
			$post['telefone'] = $dadosRaw['candidato']['telefone'] = (new Celular($dadosRaw['candidato']['telefone']))->formatted();
		}

		// Validação extra de dados
		$validator = Validator::make($dadosRaw, [
			'candidato.nome' => 'required',
			'candidato.sobrenome' => 'required',
			'candidato.email' => 'required',
			'candidato.sexo' => 'required',
			'candidato.celular' => 'required',
			'candidato.data_nascimento' => 'required',
			'candidato.estado' => 'required',
			'candidato.cidade' => 'required',
			'como_conheceu' => 'required',
			'data_prova' => 'required',
		]);

		if ($validator->fails())
			return redirect('/inscricao?e=Preencha+todos+os+dados');

		// Limpar dados antes de salvar
		unset($aluno->primeiro_nome);
		unset($aluno->sobrenome);
		unset($aluno->telefone_ddd);
		unset($aluno->telefone_numero);
		unset($aluno->sobrenome);
		unset($aluno->celular_ddd);
		unset($aluno->celular_numero);
		unset($aluno->deficiencia);
		unset($aluno->ingresso);
		unset($aluno->distancia);
		unset($aluno->data_provas);

		// Atualizar dados
		if (isset($post['nome'])) $aluno->nome = $post['nome'];
		if (isset($post['sobrenome'])) $aluno->sobrenome = $post['sobrenome'];
		if (isset($post['email'])) $aluno->email = $post['email'];
		if (isset($post['sexo'])) $aluno->sexo = $post['sexo'];
		if (isset($post['rg'])) $aluno->rg = $post['rg'];
		if (isset($post['celular'])) $aluno->celular = $post['celular'];
		if (isset($post['telefone'])) $aluno->telefone = $post['telefone'];
		if (isset($post['data_nascimento'])) $aluno->datanascimento = $post['data_nascimento'];
		//if (isset($post['enem'])) $aluno->enem = $post['enem'];
		if (isset($post['deficiencia'])) $aluno->deficiencia = $post['deficiencia'];
		if (isset($post['ingresso'])) $aluno->ingresso = $post['ingresso'];

		// Salvar dados
		$aluno->save();

		// Validação de idade => Se a idade do candidato no dia da inscrição for menor de 18 anos, os dados dos pais são obrigatórios
		/*		
		if ($aluno->idadeNaData(Carbon\Carbon::now()) < 18) {
			$validatorIdade = Validator::make($dados_adicionais, [
				'responsavel_nome' => 'required',
				'responsavel_cpf' => 'required',
				'responsavel_telefone' => 'required',
				'responsavel_nascimento' => 'required',
			]);
			if ($validatorIdade->fails()) {
				return redirect('/inscricao?e=Os+campos+de+responsavel+sao+obrigatorios+para+menores+de+idade');
			} else {
				// Salvar os os dados do responsavel
				$aluno->dados_adicionais('responsavel_cpf', $dados_adicionais['responsavel_cpf']);
				$aluno->dados_adicionais('responsavel_nome', $dados_adicionais['responsavel_nome']);
				$aluno->dados_adicionais('responsavel_telefone', $dados_adicionais['responsavel_telefone']);
				$aluno->dados_adicionais('responsavel_nascimento', $dados_adicionais['responsavel_nascimento']);
				$aluno->save();
			}
		}*/

		// Agendar prova
		$prova = new Prova();
		$prova->aluno()->associate($aluno);
		$prova->campanha()->associate($campanha);
		$prova->curso()->associate($primeira_opcao);
		$prova->data()->associate($data_prova);

		$prova->save();

		// Sempre o primeiro lead, pois será a única inscrição
		$lead = $leads->first();

		// Primeira opção de curso
		$lead->touch();
		$lead->curso()->associate($primeira_opcao);
		$lead->midia()->associate($midia);
		$lead->prova()->associate($prova);
		$lead->dados_adicionais = $dados_adicionais;

		// Opção 1
		$lead->opcao_curso_1 = $primeira_opcao->id;

		// Opção 2
		if (isset($opcoes_curso[1])) {
			$lead->opcao_curso_2 = $opcoes_curso[1]->id;

			// Opção 3
			if (isset($opcoes_curso[2]))
				$lead->opcao_curso_3 = $opcoes_curso[2]->id;
		}

		// Converter e salvar dados
		$lead->converter('INSC', 'Inscrição via Ambiente de Conversão [Prova: ' . $prova->data->hora . ']');
		$lead->save();

		//*******  Preparar e-mail *************

		/* Preparar dados
		$dados_email = array_merge([
			'aluno' => $aluno,
			'opcoes_curso' => $opcoes_curso,
			'prova' => $prova,
			'local' => $prova->local,
			'modulo' => $module
		]);*/

		/* Identidade de Gênero
		$assunto = 'Seja bem-vind' . $aluno->genero_letra . ', ' . $aluno->primeiro_nome . '!';
		$para = $aluno->email;		
		$mensagem = view('AmbienteConversao::bem-vindo', $dados_email);
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-type: text/html; charset=iso-8859-1';
		$headers[] = 'From: Vestibular FAM <no-reply@vestibularfam.com.br>';
		mail($para, $assunto, $mensagem, implode("\r\n", $headers));

		/* Criar e-mail LARAVEL
		$email = Email::create($assunto)
			->smtp_auth()
			->from('no-reply@vestibularfam.com.br', 'Vestibular FAM')
			->to($aluno->email, $aluno->nome)
			->html(view('AmbienteConversao::bem-vindo', $dados_email)->render());
		// Enviar
		$email->send();*/

		// Atualizar Sessão
		$req->session()->put('opcoes_curso', $opcoes_curso);
		$req->session()->put('prova', $prova);
		$req->session()->put('local', $prova->local);
		$req->session()->put('dados_adicionais', $dados_adicionais);
		// Para não deixar atualizar
		$req->session()->put('aluno_inscrito', "sim");

		return redirect('/inscricao/finaliza');
	});

	Route::get('/inscricao/finaliza', function (Request $req) use ($module) {
		$aluno = $req->session()->get('aluno');
		$prova = $req->session()->get('prova');
		$local = $req->session()->get('local');
		$opcoes_curso = $req->session()->get('opcoes_curso');
		$dados_adicionais = $req->session()->get('dados_adicionais');


		//*******  Preparar e-mail *************
		$dados_email = array_merge([
			'aluno' => $aluno,
			'opcoes_curso' => $opcoes_curso,
			'prova' => $prova,
			'local' => $prova->local,
			'modulo' => $module
		]);

		$assunto = 'Seja bem-vind' . $aluno->genero_letra . ', ' . $aluno->primeiro_nome . '!';
		$para = $aluno->email;
		$mensagem = view('AmbienteConversao::bem-vindo', $dados_email)->render();
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-type: text/html; charset=iso-8859-1';
		$headers[] = 'From: Vestibular FAM <noreply@vestibularfam.com.br>';

		/* Criar e-mail LARAVEL
		$email = Email::create($assunto)
			->smtp_auth()
			->from('no-reply@vestibularfam.com.br', 'Vestibular FAM')
			->to($aluno->email, $aluno->nome)
			->html(view('AmbienteConversao::bem-vindo', $dados_email)->render());
		// Enviar
		$email->send();*/

		$enviar = mail($para, $assunto, $mensagem, implode("\r\n", $headers));

		//if (isset($_GET['socorro'])) {
		//	dd($enviar, $para, $assunto, $mensagem, $headers);
		//}

		if ($enviar) {
			$msgemail = "***SUCESSO*** ! Enviamos um e-mail com mais detalhes sobre sua inscrição.";
		} else {
			$msgemail = "ERRO no E-Mail, por favor entre em contato conosco !";
		}
		// fim bloco email


		// WTF
		if (is_null($prova)) return redirect('/inscricao');

		$dados = [
			'aluno' => $aluno,
			'opcoes_curso' => $opcoes_curso,
			'prova' => $prova,
			'dados_adicionais' => $dados_adicionais,
			'msgemail' => $msgemail
		];

		if (isset($_GET['debug']) && $_GET['debug'] == 'dump') dd($dados);
		return view('AmbienteConversao::finaliza', $dados);
	});

	Route::get('/inscricao/acompanhar', function (Request $req) use ($module) {
		$aluno = $req->session()->get('aluno');
		$lead = $req->session()->get('lead');

		// Validação
		if (is_null($aluno) || is_null($lead))
			return redirect('/');

		// Recarregar dados do banco
		$aluno = Aluno::find($aluno->id);
		$lead = Lead::find($lead->id);

		// Listar Locais de Prova
		$locais_provas = $lead->campanha->locais_provas;

		$dados = [
			'aluno' => $aluno,
			'lead' => $lead,
			'prova' => $lead->prova,
			'locais_prova' => $locais_provas,
			'opcoes_curso' => [
				Curso::find($lead->opcao_curso_1),
				Curso::find($lead->opcao_curso_2),
				Curso::find($lead->opcao_curso_3)
			]
		];

		if (isset($_GET['debug']) && $_GET['debug'] == 'dump') dd($dados);
		return view('AmbienteConversao::acompanhar-inscricao', $dados);
	});
	Route::get('/inscricao/acompanhar/{cpf}', function (Request $req, $cpf) use ($module) {
		$aluno = Aluno::porCPF($cpf);
		$campanha = Campanha::find($module->options['campanha']);

		// Validar se CPF existe
		if (is_null($aluno))
			return redirect('/');

		// Pegar lead desta campanha
		$lead = $aluno->leads()->where('campanha_id', $campanha->id)->first();
		if (is_null($lead))
			return redirect('/');

		$req->session()->put('aluno', $aluno);
		$req->session()->put('lead', $lead);

		return redirect('/inscricao/acompanhar');
	});
	Route::post('/inscricao/reagendar', function (Request $req) use ($module) {
		$lead = $req->session()->get('lead');

		// Validar se temos uma sessão
		if (is_null($lead)) return redirect('/');

		// Recarregar dados do lead do banco
		$lead = Lead::find($lead->id);

		// Só permitir reagendamento em caso de ausente ou reprovado
		if ($lead->prova->status_id == 2 || $lead->prova->status_id == 3 || true) {
			$data = $req->input('data');
			$data = Prova_Data::find($data);

			$lead->prova->data()->associate($data);
			$lead->prova->save();

			$lead->converter('INSC', 'Reagendamento online via Ambiente de Conversão [Prova: ' . $lead->prova->data->hora . ']');
			$lead->save();
		}

		// Voltar ao acompanhamento de inscrição
		return redirect('/inscricao/acompanhar');
	});

	Route::post('/inscricao/finalizada', function (Request $req) use ($module) {

		$cpf = $req->input('cpf');
		$aluno = Aluno::porCPF($cpf);

		if (is_null($aluno))
			return redirect('/');

		// Dados do aluno

		$aluno->rg = $req->input('rg');
		$aluno->endereco = $req->input('endereco');
		$aluno->numero = $req->input('numero');
		$aluno->bairro = $req->input('bairro');
		$aluno->complemento = $req->input('complemento');
		$aluno->cep = $req->input('cep');
		$aluno->nome_social = $req->input('nome_social');

		# Loop de arquivos
		$i = 0;
		$total = 0;

		function limpaCPF($valor)
		{
			$valor = trim($valor);
			$valor = str_replace(".", "", $valor);
			$valor = str_replace("-", "", $valor);
			return $valor;
		}

		$path = "documentos/" . limpaCPF($cpf);

		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}

		foreach ($_FILES["arquivos"]["error"] as $key => $error) {
			request()->validate(['imagem' => 'mimes:pdf']);
			$novapasta = $path . "/" . limpaCPF($cpf) . "_" . $key . '.pdf';
			if (file_exists($_FILES["arquivos"]["tmp_name"][$key])) {
				move_uploaded_file($_FILES["arquivos"]["tmp_name"][$key], $novapasta);
				$total++;
			}
			$i++;
		}

		$aluno->arquivos = $total;

		// Aceite ( data e IP )

		function _date($format, $timezone)
		{
			$timestamp = false;
			$defaultTimeZone = 'UTC';
			if (date_default_timezone_get() != $defaultTimeZone) date_default_timezone_set($defaultTimeZone);
			$userTimezone = new DateTimeZone(!empty($timezone) ? $timezone : 'GMT');
			$gmtTimezone = new DateTimeZone('GMT');
			$myDateTime = new DateTime(($timestamp != false ? date("r", (int) $timestamp) : date("r")), $gmtTimezone);
			$offset = $userTimezone->getOffset($myDateTime);
			return date($format, ($timestamp != false ? (int) $timestamp : $myDateTime->format('U')) + $offset);
		}

		$aceitou = "Data: " . _date("d-m-Y, H:i:s", 'America/Sao_Paulo') . " ; IP: " . $_SERVER['REMOTE_ADDR'];

		$aluno->aceite = $aceitou;

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

	Route::post('/inscricao/adicionais', function (Request $req) use ($module) {
		$cpf = $req->input('cpf');
		$aluno = Aluno::porCPF($cpf);

		if (is_null($aluno)) {
			return view('AmbienteConversao::erro');
		} else {

			$campanha = Campanha::find($module->options['campanha']);
			$lead = $aluno->leads()->where('campanha_id', $campanha->id)->first();

			$dados = [
				'lead' => $lead,
				'aluno' => $aluno,
				'deficiencias' => Autodeclaracao_Deficiencia::all(),
				'racas' => Autodeclaracao_Raca::all()
			];

			return view('AmbienteConversao::adicionais', $dados);
		}
	});

	Route::get('/matricula', function (Request $req) use ($module) {
		return view('AmbienteConversao::matricula');
	});

	Route::get('/teste', function (Request $req) use ($module) {
		// senha gerada pelo gordinho //IeVeeGh4ae
		$para = "everton.messias@gmail.com";
		//$para = "everton@ic.unicamp.br";
		$nome = "Everton Messias";
		$assunto = 'Seja bem-vindo';
		//$mensagem = '<html><head><title>Teste</title></head><body><h1>Oii Amanda esse é um teste kkkkkkk!</h1></body></html>';
		$mensagem = "TESTANDOOOOOOOOOOOO";
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-type: text/html; charset=iso-8859-1';
		$headers[] = 'From: Vestibular FAM <noreply@vestibularfam.com.br>';

		$resp1 = mail($para, $assunto, $mensagem, implode("\r\n", $headers));

		//Criar e-mail LARAVEL
		$email = Email::create($assunto)
			->smtp_auth()
			->from('no-reply@vestibularfam.com.br', 'Vestibular FAM')
			->to($para, $nome)
			->html($mensagem);
		// Enviar
		$resp2 = $email->send();

		$hoje = date("d-m-Y , H:i:s");

		if ($resp1) {
			$frase1 = "Foi normal OK ==> para: $para em $hoje";
		} else {
			$frase1 = "ERRO normal";
		}

		if ($resp2) {
			$frase2 = "Foi Laravel OK ==> para: $para";
		} else {
			$frase2 = "ERRO Laravel";
		}

		$dados = [
			'frase1' => $frase1,
			'frase2' => $frase2
		];

		return view('AmbienteConversao::teste', $dados);
	});

	Route::get('/bem-vindo', function (Request $cpf) use ($module) {

		$cpf = $_GET['cpf'];

		$aluno = Aluno::porCPF($cpf);

		//print_r($aluno);

		// Validar se CPF existe
		if (is_null($aluno))
			return redirect('/');

		$dados = [
			'aluno' => $aluno
		];

		return view('AmbienteConversao::bem-vindo', $dados);
	});

	Route::post('/resultados', function (Request $req) use ($module) {
		$cpf = $req->input('cpf');
		$aluno = Aluno::porCPF($cpf);

		$req->session()->put('aluno', $aluno);

		return redirect('/resultados');
	});

	// Inscrição Bolsas ***************************************

	Route::post('/inscricaobolsa', function (Request $req) use ($module) {
		$dados = $req->session()->get('obj');
		$campanha = Campanha::find($module->options['campanha']);
		$inscrito = $req->session()->get('aluno_inscrito');

		/* Se estiver inscrito, voltar
		if ($inscrito) {
			return redirect('/inscricao/finaliza');
		}*/

		// Converte CPF em numérico, para processamento no BD

		$cpf = $req->input('cpf');
		$infosCandidato = $req->input('candidato');
		if (is_null($infosCandidato) || !is_array($infosCandidato)) $infosCandidato = [];

		// Validar CPF
		if (!CPF::validate($cpf)) {
			return redirect('/?e=CPF Inválido');
		}

		// Validar celular
		if (isset($infosCandidato['celular']) && strlen($infosCandidato['celular']) < 14) {
			return redirect('/?e=Celular inválido');
		}

		$aluno = Aluno::porCPF($cpf);


		// Aluno não cadastrado? Criamos um cadastro antes de atualizar os dados

		if (is_null($aluno)) {
			$aluno = new Aluno(array_merge([
				'cpf' => $cpf
			]));
		}

		if (isset($infosCandidato['nome']))
			$aluno->nome = $infosCandidato['nome'];

		if (isset($infosCandidato['sobrenome']))
			$aluno->sobrenome = $infosCandidato['sobrenome'];

		if (isset($infosCandidato['email']))
			$aluno->email = $infosCandidato['email'];

		if (isset($infosCandidato['celular']))
			$aluno->celular = $infosCandidato['celular'];

		$aluno->save();

		$curso = $req->input('curso') ? $req->input('curso') : null;
		if ($curso)
			$curso = Curso::find($curso);

		// Procurar leads do aluno para essa campanha
		$leads = $aluno->leads()->where('campanha_id', $campanha->id)->get();

		// Se não existir, cria um lead
		if (!$leads->count()) {
			$lead = new Lead();
			$lead->aluno()->associate($aluno->id);
			$lead->campanha()->associate($campanha->id);

			// Curso
			if ($curso) {
				$lead->curso()->associate($curso);
				$lead->opcao_curso_1 = $curso->id;
			}

			$lead->save();

			/* Criar conversão no RD
			$lead->converter('LEAD', 'Cadastro via Ambiente de Conversão');
		} else if ($leads->first()->status->base_id > 0) {
			// Se existir e não estiver como LEAD, redirecionar o candidato para página de "Acompanhar Insrição"
			return redirect('/inscricao/acompanhar/' . $aluno->cpf);*/
		}

		// Atualizar lista de leads
		$leads = Lead::where(['aluno_id' => $aluno->id, 'campanha_id' => $campanha->id])->get();

		// Atualizar sessão
		$req->session()->put('aluno', $aluno);
		$req->session()->put('leads', $leads);
		$req->session()->put('campanha', $campanha);
		if ($curso) {
			$req->session()->put('curso', $curso->id);
		} else {
			$req->session()->put('curso', -1);
		}

		// Redirecionar
		return redirect('/inscricaobolsa');
	});

	Route::get('/inscricaobolsa', function (Request $req) use ($module) {
		$aluno = $req->session()->get('aluno');
		$leads = $req->session()->get('leads');
		$campanha = $req->session()->get('campanha');
		$inscrito = $req->session()->get('aluno_inscrito');
		$curso = $req->session()->get('curso');

		if (is_null($aluno) || is_null($leads) || !$leads->count())
			return redirect('/');

		/*if ($inscrito)
			return redirect('/inscricao/finaliza');*/

		// Listar Unidades
		$unidades = $campanha->unidades;

		// Listar Locais de Prova
		$locais_provas = $campanha->locais_provas;

		// Preencher dados do aluno que faltaram
		$aluno['nome'] = $aluno->nome;
		$aluno['sobrenome'] = $aluno->sobrenome;
		$aluno['telefone_ddd'] = isset($aluno->telefone) ? trim(substr($aluno->telefone, 1, 2)) : '';
		$aluno['telefone_numero'] = isset($aluno->telefone) ? trim(substr($aluno->telefone, 4, strlen($aluno->telefone) - 3)) : '';
		$aluno['celular_ddd'] = isset($aluno->celular) ? trim(substr($aluno->celular, 1, 2)) : '';
		$aluno['celular_numero'] = isset($aluno->celular) ? trim(substr($aluno->celular, 4, strlen($aluno->celular) - 3)) : '';
		//$aluno['enem'] = $aluno->enem;
		//$aluno['campos_enem'] = $aluno->getCamposEnem();

		// Lead atual
		$lead = $leads->first();

		$dados = [
			'aluno' => $aluno,
			'lead' => $lead,
			'locais_prova' => $locais_provas,
			'unidade' => $unidades[0],
			'campanha' => $campanha,
			'curso' => $curso
		];

		if ($req->query('e'))
			$dados['error'] = $req->query('e');

		if (isset($_GET['debug']) && $_GET['debug'] == 'dump') dd($dados);
		return view('AmbienteConversao::inscricao', $dados);
	});

	Route::post('/inscricao/bolsa', function (Request $req) use ($module) {

		$campanha = $req->session()->get('campanha');
		$dados = $req->session()->get('obj');
		$leads = $req->session()->get('leads');
		$aluno = $req->session()->get('aluno');
		$post = $req->input('candidato');
		$dados_adicionais = $req->input('dados_adicionais');

		$opcoes_curso = collect($req->input('opcoes_curso'))->transform(function ($id) {
			return Curso::find($id);
		});
		$primeira_opcao = $opcoes_curso[0];

		$midia = Midia_Tipo::find($req->input('como_conheceu'));
		$data_prova = Prova_Data::find($req->input('data_prova'));

		// Cidade/Estado
		$cidade = Cidade::find($post['cidade']);
		$estado = $cidade->estado;

		$aluno->cidade()->associate($cidade);

		// Não permitir mudança de CPF
		unset($post['cpf']);

		$dadosRaw = $req->all();

		// Verificar se estamos usando Nome + Sobrenome

		if (!empty($req->input('candidato.primeiro_nome'))) {
			$post['nome'] = $dadosRaw['candidato']['nome'];
			$post['sobrenome'] = $dadosRaw['candidato']['sobrenome'];
		}

		// Verificar se estamos usando DDD + Numero no Celular
		if (!empty($req->input('candidato.celular_ddd'))) {
			$post['celular'] = $dadosRaw['candidato']['celular'] = trim(
				$req->input('candidato.celular_ddd') .
					' ' .
					$req->input('candidato.celular_numero')
			);
		}

		// Verificar se estamos usando DDD + Numero no Telefone
		if (!empty($req->input('candidato.telefone_ddd'))) {
			$post['telefone'] = $dadosRaw['candidato']['telefone'] = trim(
				$req->input('candidato.telefone_ddd') .
					' ' .
					$req->input('candidato.telefone_numero')
			);
		}

		// Formatar corretamente número de celular
		if (isset($dadosRaw['candidato']) && isset($dadosRaw['candidato']['celular'])) {
			$post['celular'] = $dadosRaw['candidato']['celular'] = (new Celular($dadosRaw['candidato']['celular']))->formatted();
		}

		// Formatar corretamente número de telefone
		if (isset($dadosRaw['candidato']) && isset($dadosRaw['candidato']['telefone'])) {
			$post['telefone'] = $dadosRaw['candidato']['telefone'] = (new Celular($dadosRaw['candidato']['telefone']))->formatted();
		}

		// Validação extra de dados
		$validator = Validator::make($dadosRaw, [
			'candidato.nome' => 'required',
			'candidato.sobrenome' => 'required',
			'candidato.email' => 'required',
			'candidato.sexo' => 'required',
			'candidato.celular' => 'required',
			'candidato.data_nascimento' => 'required',
			'candidato.estado' => 'required',
			'candidato.cidade' => 'required',
			'como_conheceu' => 'required'
		]);

		if ($validator->fails())
			return redirect('/inscricao?e=Preencha+todos+os+dados');

		// Limpar dados antes de salvar
		unset($aluno->primeiro_nome);
		unset($aluno->sobrenome);
		unset($aluno->telefone_ddd);
		unset($aluno->telefone_numero);
		unset($aluno->sobrenome);
		unset($aluno->celular_ddd);
		unset($aluno->celular_numero);
		unset($aluno->deficiencia);
		unset($aluno->ingresso);
		//unset($aluno->campos_enem);

		// Atualizar dados
		if (isset($post['nome'])) $aluno->nome = $post['nome'];
		if (isset($post['sobrenome'])) $aluno->sobrenome = $post['sobrenome'];
		if (isset($post['email'])) $aluno->email = $post['email'];
		if (isset($post['sexo'])) $aluno->sexo = $post['sexo'];
		if (isset($post['rg'])) $aluno->rg = $post['rg'];
		if (isset($post['celular'])) $aluno->celular = $post['celular'];
		if (isset($post['telefone'])) $aluno->telefone = $post['telefone'];
		if (isset($post['data_nascimento'])) $aluno->datanascimento = $post['data_nascimento'];
		//if (isset($post['enem'])) $aluno->enem = $post['enem'];
		if (isset($post['deficiencia'])) $aluno->deficiencia = $post['deficiencia'];
		if (isset($post['ingresso'])) $aluno->ingresso = $post['ingresso'];

		// Salvar dados
		$aluno->save();

		// Agendar prova
		$prova = new Prova();
		$prova->aluno()->associate($aluno);
		$prova->campanha()->associate($campanha);
		$prova->curso()->associate($primeira_opcao);
		$prova->data()->associate($data_prova);

		$prova->save();

		// Sempre o primeiro lead, pois será a única inscrição
		$lead = $leads->first();

		// Primeira opção de curso
		$lead->touch();
		$lead->curso()->associate($primeira_opcao);
		$lead->midia()->associate($midia);
		$lead->prova()->associate($prova);
		$lead->dados_adicionais = $dados_adicionais;

		// Opção 1
		$lead->opcao_curso_1 = $primeira_opcao->id;

		// Opção 2
		if (isset($opcoes_curso[1])) {
			$lead->opcao_curso_2 = $opcoes_curso[1]->id;

			// Opção 3
			if (isset($opcoes_curso[2]))
				$lead->opcao_curso_3 = $opcoes_curso[2]->id;
		}

		// Converter e salvar dados
		$lead->converter('INSC', 'Inscrição via Ambiente de Conversão [Prova: ' . $prova->data->hora . ']');
		$lead->save();

		//*******  Preparar e-mail *************

		$dados_email = array_merge([
			'aluno' => $aluno,
			'opcoes_curso' => $opcoes_curso,
			'prova' => $prova,
			'local' => $prova->local,
			'modulo' => $module
		]);
		$assunto = 'Seja bem-vind' . $aluno->genero_letra . ', ' . $aluno->primeiro_nome . '!';
		$para = $aluno->email;
		$mensagem = view('AmbienteConversao::bem-vindo', $dados_email)->render();
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-type: text/html; charset=iso-8859-1';
		$headers[] = 'From: Vestibular FAM <noreply@vestibularfam.com.br>';
		mail($para, $assunto, $mensagem, implode("\r\n", $headers));

		/* e-mail LARAVEL
		$email = Email::create($assunto)
			->smtp_auth()
			->from('no-reply@vestibularfam.com.br', 'Vestibular FAM')
			->to($aluno->email, $aluno->nome)
			->html(view('AmbienteConversao::bem-vindo', $dados_email)->render());
		// Enviar
		$email->send();*/

		// Atualizar Sessão
		$req->session()->put('opcoes_curso', $opcoes_curso);
		$req->session()->put('prova', $prova);
		$req->session()->put('local', $prova->local);
		$req->session()->put('dados_adicionais', $dados_adicionais);

		// Para não deixar atualizar
		$req->session()->put('aluno_inscrito', "sim");
		return redirect('/inscricao/bolsa');
	});

	Route::get('/inscricao/bolsa', function (Request $req) use ($module) {
		$aluno = $req->session()->get('aluno');
		$prova = $req->session()->get('prova');
		$local = $req->session()->get('local');
		$opcoes_curso = $req->session()->get('opcoes_curso');
		$dados_adicionais = $req->session()->get('dados_adicionais');

		// inicio bloco Enviar e-mail

		$inf_aluno = json_decode($aluno, TRUE);
		//print_r($inf_aluno);

		$nomealuno = $inf_aluno['nome'];
		$cpfaluno = $inf_aluno['cpf'];
		$emailaluno = $inf_aluno['email'];
		$fammsg = "Sua inscrição foi realizada! Aguarde o contato dos organizadores do evento via email ou telefone para confirmar sua inscrição";
		$famemail = 'informativo@fam.br';
		$famassunto = 'Mensagem Vestibular FAM';

		$cabecalho =
			'MIME-Version: 1.0' . "\r\n" .
			'Content-type: text/html; charset=UTF-8;' . "\r\n" .
			'From: ' . $emailaluno . "\r\n" .
			'Reply-To: ' . $famemail . "\r\n" .
			'X-Mailer: PHP/' . phpversion();

		$mensagem = "<h5>Nome: " . $nomealuno . "<br>CPF: " . $cpfaluno . "<br><br>FAM informa: </h5><p>" . $fammsg . "</p>";

		$enviar = mail($famemail, $famassunto, $mensagem, $cabecalho);

		if ($enviar) {
			$msgemail = "Sucesso !! ";
		} else {
			$msgemail = "";
		}

		// fim bloco

		// WTF

		$dados = [
			'aluno' => $aluno,
			'opcoes_curso' => $opcoes_curso,
			'prova' => $prova,
			'dados_adicionais' => $dados_adicionais,
			'msgemail' => $msgemail
		];

		if (isset($_GET['debug']) && $_GET['debug'] == 'dump') dd($dados);
		return view('AmbienteConversao::finaliza', $dados);
	});

	/////// fim bloco BOLSAS *******************************


	// Importar dados via CSV - atualizado dia 30/07/2020
	/*
	Route::get('/import_csv', function (Request $req) use ($module) {
		$tc = 15;	//total colunas
		echo "<img src='/documentos/foto.jpg'/><br><br>";
		$path = public_path('/documentos/bolsa.csv');
		$csv = file_get_contents($path);
		$vetor = explode(',', $csv);
		$linha = (count($vetor) - 1) / $tc;
		$matriz = array(array());
		$i = 0;
		$campanha = Campanha::find($module->options['campanha']);
		echo count($vetor) . " - " . $linha . "<br>";
		echo $campanha->nome . "<br><br>";
		for ($l = 0; $l < $linha; $l++) {
			for ($c = 0; $c < $tc; $c++) {
				$matriz[$l][$c] = $vetor[$i];
				$i++;
			}
		}
		for ($l = 1; $l < $linha; $l++) {
			$cpf = $matriz[$l][5]; // cpf
			$aluno = Aluno::porCPF($cpf);
			if (is_null($aluno)) {
				$aluno = new Aluno(array_merge([
					'cpf' => $cpf
				]));
			}
			$aluno->nome = $matriz[$l][0]; // nome
			$aluno->sobrenome = $matriz[$l][1]; // sobrenome
			$aluno->email = $matriz[$l][2]; // email
			$aluno->celular = $matriz[$l][3]; // celular
			$aluno->telefone = $matriz[$l][4]; // telefone
			$aluno->data_nascimento = $matriz[$l][6]; // datanascimento
			$aluno->rg = "xxxxxxxxxx";
			//$aluno->cidade = $matriz[$l][7]; // cidade
			//$aluno->estado = $matriz[$l][8]; // uf
			//$aluno->como_conheceu = $matriz[$l][9]; // como conheceu
			$aluno->sexo = $matriz[$l][10]; // sexo
			$aluno->deficiencia = $matriz[$l][11]; // deficiencia				
			$aluno->ingresso = $matriz[$l][13];	// ingresso				
			$curso_id = $matriz[$l][14];	// curso
			
			$aluno->save();
			
			$curso = Curso::find($curso_id);
			$lead = new Lead();
			$lead->aluno()->associate($aluno->id);
			$lead->campanha()->associate(33);
			// Curso
			if ($curso) {
				$lead->curso()->associate($curso);
				$lead->opcao_curso_1 = $curso->id;
				$cursonome = $curso->nome . " - " . $curso_id;
			} else {					
				$cursonome = $matriz[$l][12] . " - " . $matriz[$l][14]. " (Obs.)";
			}
			$lead->save();
			echo $l	. " , ";
			echo $matriz[$l][0] . " , "; // nome
			echo $matriz[$l][1] . " , "; // sobrenome
			echo $matriz[$l][2] . " , "; // email
			echo $matriz[$l][3] . " , "; // celular
			echo $matriz[$l][4] . " , "; // telefone
			echo $matriz[$l][5] . " , "; // cpf
			echo $matriz[$l][6] . " , "; // datanascimento
			//echo $matriz[$l][7]." , "; // cidade
			//echo $matriz[$l][8]." , "; // uf
			//echo $matriz[$l][9]." , "; // como conheceu
			echo $matriz[$l][10] . " , "; // sexo
			echo $matriz[$l][11] . " , "; // deficiencia
			echo $cursonome . " , ";	// curso		
			echo $matriz[$l][13] . ", <br> ";	// ingresso		
		}
	});
	*/

	// Update Leads via CSV - atualizado dia 30/07/2020
	/*
	Route::get('/arruma', function (Request $req) use ($module) {
		$tc = 15;	//total colunas
		echo "<img src='/documentos/foto.jpg'/><br><br>";
		$path = public_path('/documentos/bolsa.csv');
		$csv = file_get_contents($path);
		$vetor = explode(',', $csv);
		$linha = (count($vetor) - 1) / $tc;
		$matriz = array(array());
		$i = 0;		
		echo count($vetor) . " - " . $linha . "<br>";		
		for ($l = 0; $l < $linha; $l++) {
			for ($c = 0; $c < $tc; $c++) {
				$matriz[$l][$c] = $vetor[$i];
				$i++;
			}
		}
		for ($l = 1; $l < $linha; $l++) {
			$cpf = $matriz[$l][5]; // cpf
			$aluno = Aluno::porCPF($cpf);
			if (is_null($aluno)) {
				$aluno = new Aluno(array_merge([
					'cpf' => $cpf
				]));
			}						
			$curso_id = $matriz[$l][14];	// curso			
			
			$curso = Curso::find($curso_id);			
			// Curso
			if ($curso) {				
				$cursonome = $curso->nome . " - " . $curso_id;
			} else {					
				$cursonome = $matriz[$l][12] . " - " . $matriz[$l][14]. " (Obs.)";
			}
			$conexao = new mysqli('localhost','root','b2smkt@2018#','fam_app');
			
			$sql = "update leads l JOIN alunos a ON a.id = l.aluno_id set l.curso_id = ".$curso_id.", l.opcao_curso_1 = ".$curso_id." WHERE a.cpf = ".$cpf." and l.campanha_id = '33'";
			$saida = $conexao->query($sql);	
			if($saida){
				$resp = "OK";
			}else{
				$resp = "ERRO";
			}
			echo $l	. " , ";
			echo $resp	. " , ";
			echo $matriz[$l][0] . " , "; // nome
			echo $matriz[$l][1] . " , "; // sobrenome
			echo $matriz[$l][2] . " , "; // email
			echo $matriz[$l][3] . " , "; // celular
			echo $matriz[$l][4] . " , "; // telefone
			echo $matriz[$l][5] . " , "; // cpf
			echo $matriz[$l][6] . " , "; // datanascimento
			echo $matriz[$l][10] . " , "; // sexo
			echo $matriz[$l][11] . " , "; // deficiencia
			echo $cursonome . " , ";	// curso		
			echo $matriz[$l][13] . ", <br> ";	// ingresso		
		}
	});
	*/
	Route::get('/resultados', function (Request $req) use ($module) {
		$aluno = $req->session()->get('aluno');

		if (is_null($aluno))
			return view('AmbienteConversao::resultados');

		return view('AmbienteConversao::resultados', [
			'provas' => $aluno->provas->sortByDesc('data'),
			'aluno' => $aluno
		]);
	});


	Route::any('/assets/{all?}', function (Request $req, $file) use ($module) {
		$dados = $req->session()->get('obj');

		$file_path = $dados['diretorio'] . '/assets/' . $file;

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

	Route::any('{all?}', function (Request $req, $file) use ($module) {
		// Não permitir assets fora da pasta assets
		abort(403);
	})->where('all', '.+');
});

// Isso faz o sistema não buscar pastas padrão de views nem de recursos
$IGNORE_FILES = true;
