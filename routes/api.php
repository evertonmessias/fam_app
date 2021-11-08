<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

global $ROUTER_FILE;
$ROUTER_FILE = 'api.php';

require_once (__DIR__ . '/../fn_helpers.php');
require_once (__DIR__ . '/module-loader.php');

load_routes();