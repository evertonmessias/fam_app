<?php

use App\Aluno;
use App\Campanha;
use App\Curso;
use App\Lead;
use App\Lead_Status;

use App\Helpers;

use App\Modules\Admin\Graficos;
use App\Modules\Admin\Grafico;
use App\Modules\Admin\GraficoTempo;
use App\Modules\Admin\GraficoRosca;
use App\Modules\Admin\GraficoGenero;
use App\Modules\Admin\GraficoGauge;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable as User;

// Autenticação
Auth::routes();

// TODO: Criar página de 'home' antes da dashboard (para não sobrecarregar o sistema)
Route::get('/', function (Request $req) {
	if (is_null(Auth::user()))
		return redirect('/login');
		
	if (Auth::user()->can('gerenciamento.campanhas')) return redirect('/campanhas');
	if (Auth::user()->can('gerenciamento.dashboard')) return redirect('/dashboard');
	if (Auth::user()->can('gerenciamento.alunos')) return redirect('/alunos');
	if (Auth::user()->can('financeiro.notas')) return redirect('/financeiro/notas');
	return '';
});

// Middleware para validar login
$middle_login = function ($req, Closure $next) {
	// Usuário logado, ok
	if (Auth::check()) {

        // Verificar se não é inativo
        if (Auth::user()->hasRole('desativado'))
            return redirect('/logout');

        // Continuar
        return $next ($req);
    }

	// Usuário não logado, aplicar código que era para ser executado por padrão (do 'Handler.php')
	if ($req->expectsJson()) {
	    return response()->json(['error' => 'Unauthenticated.'], 401);
	}

	return redirect()->guest('login');
};

// Middleware de dados padrão
$middle_dados = function ($req, Closure $next) {
	$dados = [];

	// Campanhas
	$campanhas = Campanha::orderBy('inicio', 'desc')->get();

	// Vestibular atual
	$campanha = $req->session()->get('campanha', function () use ($campanhas) {
		if (is_null($campanhas->first()))
			return null;

		return $campanhas->first()->id;
	});
	$campanha = Campanha::find($campanha);

	if (!is_null($campanha)) {
		// Datas mínimas e maximas
		$data_min = $campanha->inicio;
		$data_max = (strtotime($campanha->fim) > time()) ? date('Y-m-d') : $campanha->fim;

		$dados['campanhas'] = $campanhas;
		$dados['campanha'] = $campanha->id;
		$dados['campanha_atual'] = $campanha;

		$dados['data_min'] = $data_min;
		$dados['data_max'] = $data_max;
	}

	// Compartilhar dados com View
	View::share('dados', $dados);

	$req->session()->put('obj', $dados);
	return $next($req);
};

// Admin
Route::group(['middleware' => [$middle_login, 'auth', $middle_dados]], function () {

    include (__DIR__ . '/dashboard.php');

    // Switch de campanha

	Route::get('/dashboard/{campanha}', function (Request $req, $campanha_id) {
		try {
			$campanha = Campanha::find($campanha_id);
			$req->session()->put('campanha', $campanha->id);
		} catch (Exception $e) { }

		return redirect ('/dashboard');
		return back();
	});

    // AJAX
    Route::group(['prefix' => 'ajax'], function () { include (__DIR__ . '/ajax.php'); });

    // Campanhas
    Route::group(['prefix' => 'campanhas'], function () { include (__DIR__ . '/campanhas.php'); });

    // Módulos
    Route::group(['prefix' => 'modulos'], function () { include (__DIR__ . '/modulos.php'); });

    // Alunos
    Route::group(['prefix' => 'alunos'], function () { include (__DIR__ . '/alunos.php'); });

    // Unidades
    Route::group(['prefix' => 'unidades'], function () { include (__DIR__ . '/unidades.php'); });

    // Provas
    Route::group(['prefix' => 'provas'], function () { include (__DIR__ . '/provas.php'); });

    // Financeiro
    Route::group(['prefix' => 'financeiro'], function () { include (__DIR__ . '/financeiro.php'); });

    // Financeiro
    Route::group(['prefix' => 'dev'], function () { include (__DIR__ . '/developer.php'); });

    // Cursos
    Route::group(['prefix' => 'cursos'], function () { include (__DIR__ . '/cursos.php'); });

	// Escols
    Route::group(['prefix' => 'escolas'], function () { include (__DIR__ . '/escolas.php'); });

    // Usuários
    Route::group(['prefix' => 'users'], function () { include (__DIR__ . '/users.php'); });

    // Business Intelligence
    Route::group(['prefix' => 'bi'], function () { include (__DIR__ . '/bi/main.php'); });

    // CRM
    Route::group(['prefix' => 'crm'], function () { include (__DIR__ . '/crm/main.php'); });

    // TOP
    Route::group(['prefix' => 'top'], function () { include (__DIR__ . '/top.php'); });

    // TOP 2019 + pesquisa
    Route::group(['prefix' => 'top-pesquisa'], function () { include (__DIR__ . '/top-pesquisa.php'); });

    // Datastore
	Route::group(['prefix' => 'datastore'], function () { include (__DIR__ . '/datastore.php'); });
	
    // Revisaço
    Route::group(['prefix' => 'revisaco'], function () { include (__DIR__ . '/revisaco.php'); });

    // Faculdade Aberta
    Route::group(['prefix' => 'faculdadeaberta'], function () { include (__DIR__ . '/faculdadeaberta.php'); });
});
	
// Login
Route::get('/login', function () {
	// Usuário logado
	if (Auth::check()) return redirect('/');

	// Visitante
    return view ('Admin::login');
});

// Logout
Route::get('/logout', function () {
	Auth::logout ();
	return redirect ('/');
});

// Redireciona /home para dashboard
Route::get('/home', function () {
	return redirect('/dashboard');
});