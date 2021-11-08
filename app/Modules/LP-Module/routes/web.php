<?php

use App\Datastore;
use App\Models\Escola;
use App\RDStationAPI;
use App\Settings;

use App\System\Email;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

// Middleware de dados padrão
$middle_dados = function ($req, Closure $next) use ($module) {
    $dados = [];

	// Pasta de recursos
	$dir = $module->options['diretorio'];
	$dir = APP_BASE . '/landing-pages/' . $dir;

    $dados['diretorio'] = $dir;
    
    // Importar o module.json
    if (!file_exists($dir . '/module.json')) throw new \Exception('A pasta especificada não possui definição module.json para módulos avançados.');
    $dados['landing-page'] = json_decode(file_get_contents($dir . '/module.json'));

    // Retornar dados necessários
    if (
        isset($dados['landing-page']->data)
        && isset($dados['landing-page']->data->table)
        && isset($dados['landing-page']->data->column)
        && isset($dados['landing-page']->data->match)
        ) {
        $data = DB::table(strval($dados['landing-page']->data->table))->where(strval($dados['landing-page']->data->column), strval($dados['landing-page']->data->match))->get();
        $data = $data->transform(function ($inscrito) {
            $inscrito->data = json_decode($inscrito->data);
            if (isset($inscrito->data->opcoes))
                $inscrito->data->opcoes = json_decode($inscrito->data->opcoes);
            return $inscrito;
        });
        
        View::share('data_requested', $data);
    }

	// Views e Templates
	View::addNamespace('LP', $dir . '/views/');

	// Compartilhar dados com View
	View::share('dados', $dados);
	View::share('opcoes', $module->options);
	View::share('settings', Settings::get_all());

	$req->session()->put('obj', $dados);
	return $next($req);
};

Route::group(['middleware' => [$middle_dados]], function () use ($module) {
    $options = $module->options;

	// Testar se estamos com uma LP de redirecionamento
	if (isset($options['redirecionar']) && isset($options['redirecionar_url']) && $options['redirecionar'] == true && !empty($options['redirecionar_url'])) {
		// Redireciona a LP
		Route::any('/{all?}', function (Request $req) use ($options) {
			return redirect($options['redirecionar_url']);
		});
	} else {
		// LP padrão

		// Home
		Route::get('/', function (Request $req) use ($module) {
            // Obter dados da sessão
            $datastoreObject = Datastore::find($req->session()->get('datastore'));
            $sessionData = $req->session()->get('session-data');
            $lastData = $req->session()->get('last-data');

			return view('LP::index', [
                'session' => $sessionData,
                'last' => $lastData,
                'datastore' => is_null($datastoreObject) ? [] : $datastoreObject->data
            ]);
        });

        //everton

        Route::post('/inscricao/fam-na-escola', function (Request $req) use ($module) {

          $nome = $req['escola'];
          $cidade = $req['cidade'];
          $responsavel = $req['responsavel'];
          $responsavel_email = $req['responsavel_email'];
          $responsavel_telefone = $req['responsavel_telefone'];
          $data = $req['data'];
          
          //echo $nome."<br>".$cidade."<br>".$responsavel."<br>".$responsavel_email."<br>".$responsavel_telefone."<br>".$data."<br>";
          
          $escola = new Escola();
          $escola->nome = $nome;
          $escola->cidade = $cidade;
          $escola->responsavel = $responsavel;
          $escola->responsavel_email = $responsavel_email;
          $escola->responsavel_telefone = $responsavel_telefone;
          $escola->data = $data;
          $escola->save();

          $dado = array_merge([
              'nome' => $nome,
              'cidade' => $cidade,
              'responsavel' => $responsavel,
              'responsavel_email' => $responsavel_email,
              'responsavel_telefone' => $responsavel_telefone,
              'data' => $data
          ]);

          // Preparar e-mail

		// Identidade de Gênero
		$assunto = 'Seja bem-vindo' . $escola->nome . '!';

        // Preparar dados
        
		// Criar e-mail
		$email = Email::create($assunto)
			->smtp_auth()
			->from('no-reply@vestibularfam.com.br', 'Vestibular FAM')
			->to($responsavel_email, $nome)
			->html(view('LP::obrigado', $dado)->render());

		// Enviar
		$email->send();

			return view('LP::obrigado',$dado);
        });

        // Assets
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

        // Outros GETs
		Route::get('/{any?}', function (Request $req, $page) use ($module) {
            // Obter lista de pages do módulo de LP atual
            $meta = $req->session()->get('obj');

            // Verificar se temos pages cadastrados no módulo
            if (!isset($meta['landing-page']->pages)) abort(404);

            // Obter a page atual
            if (!isset($meta['landing-page']->pages->{$page})) {
                abort(404);
                throw new \Exception('A página especificada não foi encontrada.');
            }
            $page = $meta['landing-page']->pages->{$page};

            // Verificar se a page atual possui view
            if (!isset($page->view)) throw new \Exception('A page atual não possui uma view configurada.');

            // Obter dados da sessão
            $datastoreObject = Datastore::find($req->session()->get('datastore'));
            $sessionData = $req->session()->get('session-data');
            $lastData = $req->session()->get('last-data');

            // Verificar se a página possui alguma regra
            $defaultRules = [
                'requires-session' => false,
                'requires-datastore' => false
            ];
            $requestRules = array_merge($defaultRules, isset($page->rules) ? json_decode(json_encode($page->rules), true) : []);

            // Validar regras
            if ($requestRules['requires-session'] && (is_null($sessionData) || empty($sessionData))) throw new \Exception('Nenhuma sessão definida.');
            if ($requestRules['requires-datastore'] && is_null($datastoreObject)) throw new \Exception('Nenhum datastore encontrado.');

            // Retornar view
			return view('LP::' . $page->view, [
                'session' => $sessionData,
                'last' => $lastData,
                'datastore' => is_null($datastoreObject) ? [] : $datastoreObject->data
            ]);
        })->where('any', '.+');
        
        // POSTs
        Route::post('/{any?}', function (Request $req, $endpoint) use ($module) {
            // Obter lista de endpoints do módulo de LP atual
            $meta = $req->session()->get('obj');
            $landingPage = $meta['landing-page'];
            $initialEndpoint = $endpoint;

            // Verificar se temos identificador padrão no módulo
            if (!isset($landingPage->identifier)) throw new \Exception('O módulo atual não possui identificador.');

            // Verificar se temos endpoints cadastrados no módulo
            if (!isset($landingPage->endpoints)) throw new \Exception('O módulo atual não possui nenhum endpoint POST.');

            // Obter o endpoint atual
            if (!isset($landingPage->endpoints->{$endpoint})) {
                abort(404);
                throw new \Exception('O endpoint especificado não foi encontrado.');
            }
            $endpoint = $landingPage->endpoints->{$endpoint};

            // Verificar se o endpoint atual possui regras de validação e callback
            if (!isset($endpoint->validation)) throw new \Exception('O endpoint atual não possui validação POST configurada.');
            if (!isset($endpoint->callback)) throw new \Exception('O endpoint atual não possui callback POST configurado.');

            // Obter dados da sessão
            $datastoreObject = Datastore::find($req->session()->get('datastore'));
            
            // Verificar se o endpoint está passando alguma opção
            $defaultSettings = [
                'datastore-mode' => 'passthrough',
                'fetch-rules' => [],
                'identifier' => $landingPage->identifier,
                'debug' => false,
                'conversion' => null,
                'conversion_user' => null
            ];
            $requestSettings = array_merge($defaultSettings, isset($endpoint->settings) ? json_decode(json_encode($endpoint->settings), true) : []);

            // Extrair regras de validação
            $validationRules = json_decode(json_encode($endpoint->validation), true);

            // Validar o POST e extraír dados filtrados
            $resultData = $req->validate($validationRules);

            // Inicia ações no banco de dados
            DB::beginTransaction();

            // Armazenar os dados no Datastore
            switch ($requestSettings['datastore-mode']) {
                case 'fetch-or-create':
                    // Verificar se as regras de fetch existem
                    $fetchRules = $requestSettings['fetch-rules'];
                    if (empty($fetchRules)) throw new \Exception('Por favor, informe pelo menos uma regra no setting fetch-rules.');

                    // Processamos as regras
                    foreach ($fetchRules as $k => $v) {
                        $fetchRules[$k] = $resultData[$v];
                    }

                    // Obtemos o Datastore
                    $datastoreObject = Datastore::retrieve($requestSettings['identifier'], $fetchRules)->first();

                    // Verificar se o Datastore existe
                    if (is_null($datastoreObject)) {
                        // Se não existir, criar novo Datastore
                        $datastoreObject = Datastore::store($requestSettings['identifier'], $resultData);
                    }

                    // Armazenar os dados na datastore da sessão
                    $req->session()->put('datastore', $datastoreObject->id);
                    break;
                case 'create':
                    // Criar novo Datastore
                    $datastoreObject = Datastore::store($requestSettings['identifier'], $resultData);

                    // Armazenar os dados na datastore da sessão
                    $req->session()->put('datastore', $datastoreObject->id);
                    break;
                case 'update':
                case 'append':
                    // Verificar se o Datastore existe
                    if (is_null($datastoreObject)) throw new \Exception('Sessão não existente. Impossível atualizar.');

                    // Atualizar o Datastore existente
                    $datastoreObject->identifier = $requestSettings['identifier'];
                    $datastoreObject->appendData($resultData);
                    $datastoreObject->save();

                    // Armazenar os dados na datastore da sessão
                    $req->session()->put('datastore', $datastoreObject->id);
                    break;
                case 'replace':
                    // Verificar se o Datastore existe
                    if (is_null($datastoreObject)) throw new \Exception('Sessão não existente. Impossível substituir.');

                    // Substituir o Datastore existente
                    $datastoreObject->identifier = $requestSettings['identifier'];
                    $datastoreObject->data = $resultData;
                    $datastoreObject->save();
                    break;
                case 'passthrough':
                default:
                    // Por padrão, não fazer nada, deixar que os dados sejam passthrough (ou seja, apenas passa para a sessão)
                    break;
            }

            // Preparar para conversão (integração no RD Station)
            $rdDados = null;
            if (!is_null($requestSettings['conversion'])) {
                if (is_null($requestSettings['conversion_user'])) throw new \Exception('Campo identificador não especificado para conversão.');

                $rdDados = [
                    'email' => $datastoreObject->data[$requestSettings['conversion_user']],
                    'dados' => array_merge($datastoreObject->data, [
                        'identificador' => $requestSettings['conversion']
                    ])
                ];
            }

            // Estamos em modo debug? Caso estiver, não finalizar, apenas fazer rollback e rodar um dd
            if ($requestSettings['debug'] === true) {
                DB::rollBack();
                dd([
                    'identifier' => $requestSettings['identifier'],
                    'conversion' => $requestSettings['conversion'],
                    'conversion_user' => $requestSettings['conversion_user'],
                    'conversion_data' => $rdDados,
                    'request' => [
                        'input' => $req->all(),
                        'rules' => $validationRules,
                        'settings' => $requestSettings,
                        'data' => $resultData
                    ],
                    'session' => [
                        'datastore' => $datastoreObject
                    ],
                    'endpoint' => $endpoint,
                    'landing-page' => $landingPage,
                ]);
            }

            // Salvar alterações no banco de dados
            DB::commit();

            // Armazenar últimos dados na sessão
            $req->session()->put('last-data', $resultData);

            // Atualizar dados da sessão também (passthrough)
            $sessionData = $req->session()->get('session-data');
            if (is_null($sessionData)) $sessionData = [];
            $sessionData = array_merge($sessionData, $resultData);
            $req->session()->put('session-data', $sessionData);

            // Realizar conversão (integração no RD Station)
            if (!is_null($rdDados)) {
                $rdAPI = new RDStationAPI (Settings::get('rd_token_privado', env('RD_TOKEN_PRIVADO')), Settings::get('rd_token', env('RD_TOKEN')));
                $rdAPI->sendNewLead($rdDados['email'], $rdDados['dados']);
            }

            // E-mail de confirmação
            if (isset($landingPage->email) && $initialEndpoint == $landingPage->email->endpoint) {
                // Preparar e-mail
    
                // Assunto
                $assunto = $landingPage->email->subject;

                $dadosArray = json_decode($datastoreObject);
    
                // Preparar dados
                $dados_email = array_merge([
                    'dados' => $dadosArray->data
                ]);
    
                // Criar e-mail
                $email = Email::create($assunto)
                    ->smtp_auth()
                    ->from($landingPage->email->from, $landingPage->email->fromName)
                    ->to($dadosArray->data->{$landingPage->email->toEmailField}, $dadosArray->data->{$landingPage->email->toNameField})
                    ->html(view('LP::' . $landingPage->email->emailView, $dados_email)->render());
    
                // Enviar
                $email->send();
            }

            // Obter callback do endpoint e redirecionar
            return redirect($endpoint->callback);
        })->where('any', '.+');

		// Outras páginas da LP (se tiver)
		if (isset($module) && isset($module['diretorio'])) {
			$dir = isset($options['diretorio']) ? $options['diretorio'] : $dados['campanha'];
			$dir = APP_BASE . '/landing-pages/' . $dir;
			$dirHandle = opendir($dir . '/views/');
			while($view = readdir($dirHandle)) {
				if ($view == '.' || $view == '..' || false === stripos($view, '.twig') || is_dir($view)) continue;
				$viewName = substr($view, 0, -strlen('.twig'));
				
				Route::get('/' . $viewName, function (Request $req) use ($module, $viewName) {
					return view('LP::' . $viewName);
				});
			}
			closedir($dirHandle);
		}

		Route::any('{all?}', function (Request $req, $file) use ($module) {
			// Não permitir assets fora da pasta assets
			abort(403);
		})->where('all', '.+');
	}
});

// Isso faz o sistema não buscar pastas padrão de views nem de recursos
$IGNORE_FILES = true;