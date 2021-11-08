<?php

use App\Aluno;
use App\Cidade;

use Illuminate\Http\Request;

use Carbon\Carbon;

// Alunos

Route::group(['prefix' => 'alunos'], function () {
	Route::get('/', function () {
		return response()->json(Aluno::select('id', 'nome', 'sobrenome', 'email', 'cpf','ingresso','distancia')->get()->sortBy('nome')->values());
	});
	Route::get('/{id}/', function ($id) {
		return response()->json(Aluno::find($id));
	});
	Route::get('/{id}/{property}', function ($id, $property) {
		return response()->json(Aluno::find($id)->{ $property });
	});
});

// Cidades

Route::group(['prefix' => 'cidades'], function () {
	Route::get('/', function () {
		return response()->json(Cidade::orderBy('nome', 'ASC')->get());
	});
	Route::get('/{id}/', function ($id) {
		$id = explode(',', $id);
		if (count($id) == 1) $id = $id[0];

		// Caso seja um array no parâmetro ID
		if (is_array($id)) {
			$cidades = [];
			foreach ($id as $o_id) {
				$cidades[$o_id] = Cidade::find($o_id);
			}
			return response()->json($cidades);
		}

		return response()->json(Cidade::find($id));
	});
	Route::get('/{id}/{property}', function ($id, $property) {
		$id = explode(',', $id);
		if (count($id) == 1) $id = $id[0];

		// Caso seja um array no parâmetro ID
		if (is_array($id)) {
			$cidades = [];
			foreach ($id as $o_id) {
				$cidades[$o_id] = Cidade::find($o_id)->{ $property };
			}
			return response()->json($cidades);
		}

		return response()->json(Cidade::find($id)->{ $property });
	});
});