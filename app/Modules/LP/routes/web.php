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
	$dir = APP_BASE . '/landing-pages/' . $dir;

	$dados['diretorio'] = $dir;

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
			return view('LP::home');
		});

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
	}
});

// Isso faz o sistema não buscar pastas padrão de views nem de recursos
$IGNORE_FILES = true;