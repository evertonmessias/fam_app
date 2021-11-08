<?php

use Illuminate\Http\Request;

$middle = function (Request $req, $next) {
	return $next($req);
};

Route::group(['middleware' => $middle], function () {
	Route::get('/', function () {
		return view ('Admin::CRM.index');
	});

	// Atendimento
	Route::group(['prefix' => 'atendimento'], function () { include (__DIR__ . '/atendimento.php'); });

	/* API */
	Route::group(['prefix' => 'api'], function () {
		include (__DIR__ . '/api.php');
	});
});