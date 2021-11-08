<?php

use App\Unidade;

use Illuminate\Http\Request;

use Carbon\Carbon;

$middle = function (Request $req, $next) {
	return $next($req);
};

Route::group(['middleware' => $middle], function () {
	Route::get('/', function () {
		$unidades = Unidade::all();

		return view ('Admin::Unidades.index', [
			'unidades' => $unidades
		]);
	});
	Route::get('/{unidade}/edit', function ($unidade) {
		$unidade = Unidade::find($unidade);

		$dados = [
			'unidade' => $unidade
		];
		
		return view('Admin::Unidades.edit', $dados);
	});

	Route::post('/new', function (Request $req) {
		
		return redirect('unidades/' . $module->id . '/edit');
	});
	Route::post('/{unidade}/edit', function (Request $req, $unidade) {
		
		return back();
	});
});