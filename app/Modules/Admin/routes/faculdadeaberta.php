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
        $inscritos = Datastore::where('identifier', 'faculdadeaberta')->get();
        $inscritos = $inscritos->transform(function ($inscrito) {
            if ((isset($inscrito->data)) && (!is_array($inscrito->data)))
                $inscrito->data = json_decode($inscrito->data);
            return $inscrito;
        });
        View::share('inscritos', $inscritos);
        return view('Admin::FaculdadeAberta.index');
    });
    Route::get('/data-periodo/{data}', function ($data) {
        $inscritos = Datastore::where('identifier', 'faculdadeaberta')->where('data->data_e_periodo', 'like', '%' . $data . '%')->get();
        $inscritos = $inscritos->transform(function ($inscrito) {
            if ((isset($inscrito->data)) && (!is_array($inscrito->data)))
                $inscrito->data = json_decode($inscrito->data);
            return $inscrito;
        });
        View::share('inscritos', $inscritos);
        View::share('data', $data);
        return view('Admin::FaculdadeAberta.filter');
    });
});