<?php

use App\Module;
use Illuminate\Support\Facades\Cache;

// Debugbar
Route::get( '/_debugbar/assets/stylesheets', '\Barryvdh\Debugbar\Controllers\AssetController@css' );
Route::get( '/_debugbar/assets/javascript', '\Barryvdh\Debugbar\Controllers\AssetController@js' );

// Global, permitir debug
Route::get('/__clockwork/{id}', 'Clockwork\Support\Laravel\Controllers\CurrentController@getData')->where('id', '[0-9\.]+');

function fn_mime_content_type($filename) {

	// Cache file mime type, since we shouldn't be worrying about it so much

	return Cache::remember('mime://' . $filename, 60 * 24 * 7, function () use ($filename) {

		$mime_file = __DIR__ . '/../config/mime-types.php';

		// Atualiza definição de Mime-Type direto do Apache

		if (!file_exists($mime_file)) {
			$url = 'http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';
			$s=array();
		    foreach(@explode("\n",@file_get_contents($url))as $x)
		        if(isset($x[0])&&$x[0]!=='#'&&preg_match_all('#([^\s]+)#',$x,$out)&&isset($out[1])&&($c=count($out[1]))>1)
		            for($i=1;$i<$c;$i++)
		                $s[]='\''.$out[1][$i].'\' => \''.$out[1][0].'\'';
		    file_put_contents($mime_file, '<?php ' . (@sort($s)?'function fn_mime_types () { return array('."\n".implode($s,',' . "\n")."\n".'); }':false));
		}

		// Importa definições

		require_once ($mime_file);

	    $ext = explode('.',$filename);
	    $ext = array_pop($ext);
	    $ext = strtolower($ext);

	    $mime_types = fn_mime_types();

	    if (array_key_exists($ext, $mime_types)) {
	        return $mime_types[$ext];
	    }
	    elseif (function_exists('finfo_open')) {
	        $finfo = finfo_open(FILEINFO_MIME);
	        $mimetype = finfo_file($finfo, $filename);
	        finfo_close($finfo);
	        return $mimetype;
	    }
	    else {
	        return 'application/octet-stream';
	    }

	});
}

global $mod_installed;
global $mod_preloaded;

// Antes de tudo, já vamos pegar todos módulos instalados e importar suas rotas web
try {
	$mod_installed = Module::all();
} catch (Exception $e) {
	// Caso não funcione, tentar rodar as migrations
	//Artisan::call('migrate');
	//Artisan::call('db:seed');
	$mod_installed = Module::all();
}

/*$mod_installed = array();

$test = new Module();
$test->www = true;
$test->root = '/';
$test->domain = 'localhost';
$test->namespace = 'Admin';
$mod_installed[] = $test;*/

$mod_preloaded = array();

function fix_dir ($path) { return str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path); };

function load_routes () {
	global $mod_installed;
	global $mod_preloaded;

	foreach ($mod_installed as $module) {

		// Localização do módulo
		$module_dir = function ($module) { return Module::directory($module); };

		// Modificador www.*
		$domains = [$module->domain];
		if ($module->www) $domains[] = 'www.' . $module->domain;

		// Importar views do módulo
		if (!in_array($module->namespace, $mod_preloaded)) {
			View::addNamespace($module->namespace, realpath($module_dir ($module) . '/views/'));
			$mod_preloaded[] = $module->namespace;
		}

		// Carregar rotas do módulo
		$routes = function () use (&$module_dir, $module) {
			global $ROUTER_FILE;

			$IGNORE_FILES = false;

			$load_router_file = $module_dir ($module) . '/routes/' . $ROUTER_FILE;

			if (!file_exists($load_router_file))
				return;
			
			require ($load_router_file);

			if ($IGNORE_FILES === true) return;

			// Router especial para arquivos
			Route::any('{all?}', function ($file) use (&$module_dir, $module) {

				$file_path = realpath($module_dir ($module) . fix_dir('assets/' . $file));
				$public_path = realpath(__DIR__ . '/../public/' . $file);
				// return File::get($file_path);

				if (empty($file_path) || !file_exists($file_path))
					$file_path = $public_path;

				if (empty($file_path) || !file_exists($file_path))
					abort (404);

				// Pega o Mime-Type do arquivo
				$mime = fn_mime_content_type($file_path);

				return response()->file($file_path, ['Content-Type' => $mime]);
			})->where('all', '.+');
		};

		// Cria rotas
		foreach ($domains as $domain) {
			Route::group(['domain' => $domain, 'prefix' => $module->root], $routes);
		}
	}
}