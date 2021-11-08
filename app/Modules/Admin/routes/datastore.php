<?php

use App\Aluno;
use App\Datastore;

use Illuminate\Http\Request;

use Carbon\Carbon;

$middle = function (Request $req, $next) {
	return $next($req);
};

Route::group(['middleware' => $middle], function () {
    Route::get('/', function () {
        return view('Admin::Datastore.index');
    });
    Route::get('/identificador/{identifier}', function ($identifier) {
        return view('Admin::Datastore.filter', [
            'identifier' => $identifier
        ]);
    });
    Route::get('/entrada/{id}', function ($id) {
        $entrada = Datastore::find($id);
        
        // Verificar se a entrada possui algum Aluno
        if (isset($entrada->data['email']))
            $entrada->aluno = Aluno::select('id', 'nome')->where('email', $entrada->data['email'])->first();
        else
            $entrada->aluno = null;
            
        // Campos de nome e e-mail também, se houver
        $entrada->nome = isset($entrada->data['nome']) ? $entrada->data['nome'] : '';
        $entrada->email = isset($entrada->data['email']) ? $entrada->data['email'] : '';

        return view('Admin::Datastore.view', [
            'entrada' => $entrada
        ]);
    });

    // API
    Route::group(['prefix' => 'api'], function () {
        $index = function ($entradas) {
            $entradas->transform(function ($entrada) {
                // Verificar se a entrada possui algum Aluno
                if (isset($entrada->data['email']))
                    $entrada->aluno = Aluno::select('id', 'nome')->where('email', $entrada->data['email'])->first();
                else
                    $entrada->aluno = null;

                // Campos de nome e e-mail também, se houver
                $entrada->nome = isset($entrada->data['nome']) ? $entrada->data['nome'] : '';
                $entrada->email = isset($entrada->data['email']) ? $entrada->data['email'] : '';

                // Número de campos
                $entrada->qtd_campos = count($entrada->data);

                // Não incluir os dados na resposta padrão por motivos de performance
                unset($entrada->data);

                // Retornar
                return $entrada;
            });

            return [
                'entradas' => $entradas->sortByDesc('id')
            ];
        };

        Route::get('/', function () use ($index) {
            $entradas = Datastore::all();
            $indentificadores = $entradas->pluck('identifier');

            return response()->json($index($entradas));
        });
        Route::get('/identifier/{identifier}', function ($identifier) use ($index) {
            $entradas = Datastore::where('identifier', $identifier)->get();

            return response()->json($index($entradas));
        });
    });
});