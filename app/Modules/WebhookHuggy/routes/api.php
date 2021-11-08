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

Route::group([], function () use ($module) {
	Route::post('/', function (Request $req) use ($module) {
		$token = $module->options['token'];

		// Logar (debug)
		Log::info('HUGGY::' . json_encode($req->input('messages')));

		// Validar token da Huggy
		if ($req->input('messages.token') != $token)
			return ['status' => 'error', 'message' => 'Token mismatch.'];

		// Precisamos SEMPRE retornar o token, para validação com a Huggy.
		return $token;
	});
});