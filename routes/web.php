<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

/*Route::get('/', function () {

    return view('welcome');
});
*/

global $ROUTER_FILE;
	$ROUTER_FILE = 'web.php';

require_once (__DIR__ . '/../fn_helpers.php');
require_once (__DIR__ . '/module-loader.php');

load_routes();