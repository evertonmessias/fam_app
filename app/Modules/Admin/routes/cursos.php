<?php

use App\Curso;

use Illuminate\Http\Request;

use Carbon\Carbon;

$middle = function (Request $req, $next) {
	return $next($req);
};

Route::group(['middleware' => $middle], function () {

	// Listar todos os cursos

	Route::get('/', function () {
		$cursos = Curso::all();

		return view ('Admin::Cursos.index', [
			'cursos' => $cursos
		]);
	});

	// Visualizar curso específico

	Route::get('/{id}', function ($curso) {
		if ($curso == 'new')
			return view ('Admin::Cursos.view');

		$curso = Curso::find($curso);

		if (is_null($curso))
			return redirect('/cursos');

		// dd($curso);
		return view ('Admin::Cursos.view', [
			'curso' => $curso
		]);
	});
	Route::post('/{id}', function (Request $req, $curso) {

		if ($curso == 'new')
			$curso = new Curso();
		else
			$curso = Curso::find($curso);

		if (is_null($curso))
			return rediect('/cursos');

		$curso->nome = $req->input('nome');
		$curso->codigo = $req->input('codigo');
		$curso->duracao = $req->input('duracao');
		$curso->valor = $req->input('valor');
		$curso->vagas = $req->input('vagas');
		$curso->landing_page = $req->input('landing_page');

		$curso->dados_adicionais = array_merge($curso->dados_adicionais, $req->dados_adicionais);

		$curso->save();

		return redirect('/cursos/' . $curso->id);
	});
});