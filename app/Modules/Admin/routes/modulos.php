<?php

use App\Campanha;
use App\Module;

use Illuminate\Http\Request;

use Carbon\Carbon;

$middle = function (Request $req, $next) {
	return $next($req);
};

Route::group(['middleware' => $middle], function () {
	Route::get('/', function () {
		$modulos = Module::all();
		$modulos_disponivel = Module::available();

		return view ('Admin::Modulos.index', [
			'modulos' => $modulos,
			'modulos_disponivel' => $modulos_disponivel
		]);
	});
	Route::get('/{modulo}/edit', function ($modulo) {
		$modulo = Module::find($modulo);

		$dados = [
			'module' => $modulo
		];
		
		return view('Admin::Modulos.edit', $dados);
	});

	Route::post('/new', function (Request $req) {
		$module = new Module([
			'domain' => $req->domain,
			'www' => (isset($req->www) ? $req->www : false),
			'force_ssl' => (isset($req->force_ssl) ? $req->force_ssl : false),
			'root' => $req->root,
			'namespace' => $req->namespace
		]);
		$module->options = [];
		$module->save();
		return redirect('modulos/' . $module->id . '/edit');
	});
	Route::post('/{modulo}/edit', function (Request $req, $modulo) {
		$module = Module::find($modulo);
		$module->domain = $req->domain;
		$module->www = (isset($req->www) ? $req->www : false);
		$module->force_ssl = (isset($req->force_ssl) ? $req->force_ssl : false);
		$module->root = $req->root;
		$module->options = $req->options;
		$module->save();
		return back();
	});
});