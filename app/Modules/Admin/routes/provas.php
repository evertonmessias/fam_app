<?php

use App\Prova_Data;
use App\Prova_Local;
use App\Unidade;

use Illuminate\Http\Request;

use Carbon\Carbon;

$middle = function (Request $req, $next) {
	return $next($req);
};

Route::group(['middleware' => $middle], function () {

	/* Datas de Provas */

	Route::group(['prefix' => 'datas'], function () {
		Route::get('/', function () {
			$datas = Prova_Data::all();
			$unidades = Unidade::all();

			return view ('Admin::Provas.Datas.index', [
				'datas' => $datas,
				'unidades' => $unidades
			]);
		});
		Route::get('/{id}/', function ($id) {
			$data = Prova_Data::find($id);

			$dados = [
				'data' => $data
			];
			
			return view('Admin::Provas.Datas.view', $dados);
		});
		Route::get('/{id}/edit', function ($id) {
			$data = Prova_Data::find($id);

			$dados = [
				'data' => $data
			];
			
			return view('Admin::Provas.Datas.edit', $dados);
		});

		Route::post('/{id}/edit', function (Request $req, $id) {
			$data = Prova_Data::find($id);

            $data->hora = $req->data . ' ' . $req->hora;
            if ($req->hora_final) {
                $data->hora_final = $req->data . ' ' . $req->hora_final;
            } else if ($data->hora_final) {
                $data->hora_final = null;
            }
			$data->maximo = $req->maximo;
			$data->disponivel = $req->ativar;

			$data->save();
			
			return redirect('provas/datas/' . $data->id .'/edit');
		});
		Route::post('/new', function (Request $req) {
			$data = new Prova_Data();

			$data->hora = $req->data . ' ' . $req->hora;
            if ($req->hora_final)
                $data->hora_final = $req->data . ' ' . $req->hora_final;
			$data->maximo = $req->maximo;
			$data->disponivel = $req->ativar;
			$data->local()->associate(Prova_Local::find($req->local));

			$data->save();
			
			return redirect('provas/');
			return redirect('provas/datas/' . $data->id . '/edit');
		});
	});

	/* Locais de Provas */

	Route::group(['prefix' => 'locais'], function () {
		Route::get('/', function () {
			$locais = Prova_Local::all();
			$unidades = Unidade::all();

			return view ('Admin::Provas.Locais.index', [
				'locais' => $locais,
				'unidades' => $unidades
			]);
		});
		Route::get('/{id}/', function ($id) {
			$local = Prova_Local::find($id);

			$dados = [
				'local' => $local
			];
			
			return view('Admin::Provas.Locais.view', $dados);
		});
		Route::get('/{id}/edit', function ($id) {
			$local = Prova_Local::find($id);

			$dados = [
				'local' => $local
			];
			
			return view('Admin::Provas.Locais.edit', $dados);
		});

		Route::post('/{id}/edit', function (Request $req, $id) {
			$local = Prova_Local::find($id);

			$local->local = $req->local;
			$local->endereco = $req->endereco;
			$local->telefone = $req->telefone;
			$local->email = $req->email;
			$local->coordenadas = $req->coordenadas;

			$local->save();
			
			return redirect('provas/locais/' . $local->id .'/edit');
		});
		Route::post('/new', function (Request $req) {
			$unidade = Unidade::find($req->unidade);

			$local = new Prova_Local();
			$local->endereco = $req->endereco;
			$local->telefone = $req->telefone;
			$local->email = $req->email;
			$local->local = $req->local;
			$local->unidade()->associate($unidade);

			$local->save();
			
			return redirect('provas/locais/' . $local->id . '/');
		});
	});

	/* Resultados de Provas */

	/*Route::group(['prefix' => 'resultados'], function () {
		Route::get('/', function () {
			$locais = Prova_Local::all();
			$unidades = Unidade::all();

			return view ('Admin::Provas.Locais.index', [
				'locais' => $locais,
				'unidades' => $unidades
			]);
		});
		Route::get('/{id}/', function ($id) {
			$local = Prova_Local::find($id);

			$dados = [
				'local' => $local
			];
			
			return view('Admin::Provas.Locais.view', $dados);
		});
		Route::get('/{id}/edit', function ($id) {
			$local = Prova_Local::find($id);

			$dados = [
				'local' => $local
			];
			
			return view('Admin::Provas.Locais.edit', $dados);
		});

		Route::post('/{id}/edit', function (Request $req, $id) {
			$local = Prova_Local::find($id);

			$local->local = $req->local;
			$local->endereco = $req->endereco;
			$local->telefone = $req->telefone;
			$local->email = $req->email;
			$local->coordenadas = $req->coordenadas;

			$local->save();
			
			return redirect('provas/locais/' . $local->id .'/edit');
		});
		Route::post('/new', function (Request $req) {
			$unidade = Unidade::find($req->unidade);

			$local = new Prova_Local();
			$local->endereco = $req->endereco;
			$local->telefone = $req->telefone;
			$local->email = $req->email;
			$local->local = $req->local;
			$local->unidade()->associate($unidade);

			$local->save();
			
			return redirect('provas/locais/' . $local->id . '/');
		});
	});*/

	Route::get('/', function () {
		return redirect('/provas/datas/');
	});
});