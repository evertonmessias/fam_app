<?php

use App\Aluno;
use App\Campanha;
use App\Curso;
use App\Helpers;
use App\Prova;
use App\Lead;

use App\System\API_Key;

use Illuminate\Http\Request;

$token = function (Request $req, $next) {
	$token = $req->query('token');
	$passcode = $req->input('passcode');

	if (is_null($token))
		return response('Acesso negado: Token de acesso inválido.', 401)->header('Content-Type', 'text/plain');

	if ($token = API_Key::auth($token))
		$req->token = $token;
	else
		return response('Acesso negado: Token de acesso inválido.', 401)->header('Content-Type', 'text/plain');

	if (!is_null($passcode)) {
		if ($passcode = API_Key::auth($token, $passcode))
			$req->token = $passcode;
		else
			return response('Acesso negado: Token de acesso inválido.', 401)->header('Content-Type', 'text/plain');
	}

	return $next($req);
};

// API pública

Route::group(['prefix' => 'public'], function () {
	// Cadastro one-step
	Route::get('/onestep/{email}', function ($email) {
		$a = Aluno::where('email', $email)->first();

		if (is_null($a))
			return null;

		return [
			'nome' => $a->nome,
			'cpf' => $a->cpf,
			'celular' => $a->celular,
			'curso_latest' => $a->leads[0]->curso->codigo
		];
	});
});

Route::group(['middleware' => [$token]], function () use ($module) {

// API de Leads

Route::group(['prefix' => 'leads'], function () use ($module) {
	Route::get('/', function () use ($module) {
		$campanha = Campanha::find($module->options['campanha']);
		return $campanha->leads;
	});
	Route::get('/{id}', function ($id) use ($module) {
		$lead = Lead::with('prova', 'aluno', 'curso')->find($id);

		if (is_null($lead))
			return null;

		return $lead->toArray();
	});
	Route::get('/{id}/aluno', function ($id) use ($module) {
		$lead = Lead::find($id);

		if (is_null($lead))
			return null;

		return redirect('/api/alunos/' . $lead->aluno->cpf);
	});
	Route::get('/{id}/curso', function ($id) use ($module) {
		$lead = Lead::find($id);

		if (is_null($lead))
			return null;

		return redirect('/api/cursos/' . $lead->curso->id);
	});
	Route::get('/{id}/prova', function ($id) use ($module) {
		$lead = Lead::find($id);

		if (is_null($lead))
			return null;

		return redirect('/api/provas/' . $lead->prova->id);
	});
});

// API de Cursos

Route::group(['prefix' => 'cursos'], function () use ($module) {
	Route::get('/', function () use ($module) {
		$campanha = Campanha::find($module->options['campanha']);
		return $campanha->cursos;
	});
	Route::get('/{id}', function ($id) use ($module) {
		$curso = Curso::find($id);

		if (is_null($curso))
			return null;

		return $curso->toArray();
	});
});

// API de Provas

Route::group(['prefix' => 'provas'], function () use ($module) {
	Route::get('/', function () use ($module) {
		$campanha = Campanha::find($module->options['campanha']);
		return $campanha->cursos;
	});
	Route::get('/{id}', function ($id) use ($module) {
		$prova = Prova::with('data', 'data.local', 'aluno')->find($id);

		if (is_null($prova))
			return null;

		return array_merge($prova->toArray(), ['local' => $prova->local]);
	});
});

// API de Alunos

Route::group(['prefix' => 'alunos'], function () use ($module) {
	Route::get('/', function () use ($module) {
		$campanha = Campanha::find($module->options['campanha']);

		return $campanha->alunos;
	});
	Route::get('/{cpf}', function ($cpf) use ($module) {
		$aluno = Aluno::porCPF($cpf);

		if (is_null($aluno))
			return null;

		return $aluno->preparar_conversao();
	});
	Route::get('/{cpf}/leads', function ($cpf) use ($module) {
		$aluno = Aluno::porCPF($cpf);

		if (is_null($aluno))
			return null;

		return $aluno->leads;
	});
});

/**
 * Retro-compatibilidade com o sistema antigo (lliure) para integrar com o CRM atual
 * 
 * Não faço a menor idéia de como o lliure funciona, isso é apenas uma conversão.
 * Altere por sua conta e risco.
 */ 

Route::group(['prefix' => 'listizer'], function () use ($module) {
	Route::get('/leads', function () use ($module) {
		$campanha = Campanha::find($module->options['campanha']);

		$leads = $campanha->leads_total();

		return leads2listizer($leads);
	});
	Route::get('/leads/{id}', function ($id) use ($module) {
		return lead2listizer(Lead::find($id));
	});
	Route::get('/aluno/{cpf}/leads', function ($cpf) use ($module) {
		$aluno = Aluno::porCPF($cpf);

		if (is_null($aluno))
			return null;

		return leads2listizer($aluno->leads());
	});
});

});