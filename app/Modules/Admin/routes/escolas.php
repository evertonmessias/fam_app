<?php

use App\Models\Escola;
use Illuminate\Http\Request;

$middle = function (Request $req, $next) {
	return $next($req);
};

Route::group(['middleware' => $middle], function () {

	// Listar todas as escolas	
	
	Route::get('/', function () {
		$escolas = Escola::all();

		return view ('Admin::Escolas.index', [
			'escolas' => $escolas
		]);
	});
});