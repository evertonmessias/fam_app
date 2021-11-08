<?php

use Illuminate\Http\Request;

$middle = function (Request $req, $next) {
	return $next($req);
};

Route::group(['middleware' => $middle], function () {
	// Home do BI é sempre Dashboard
	Route::get('/', function () { return redirect ('/dashboard'); });

	// Atendimento
	Route::group(['prefix' => 'performance'], function () { include (__DIR__ . '/performance.php'); });
});