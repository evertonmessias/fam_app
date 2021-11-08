<?php

use App\Datalist;
use App\DatalistModel;
use App\Lead;
use App\Lead_Status;
use App\RDStation_Univestibular;
use App\Settings;

use App\Integracoes\Legacy as Legacy;

use App\System\API_Key;
use App\System\Event;
use App\System\Migration;
use App\System\Process;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;

$middle = function (Request $req, $next) {
	return $next($req);
};

Route::group(['middleware' => $middle], function () {

	// Lista, Roda migrations e atualiza banco de dados
	Route::get('/migrations/', function () {
		return view ('Admin::Developer.Migrations', [
			'migrations' => Migration::all()
		]);
	});
	Route::post('/migrations/', function () {
		Artisan::call('migrate');
		return redirect('/dev/migrations/');
	});
	Route::post('/migrations/base', function () {
		Process::init('developer')->text('Migrações CRM forçadas')->run(function() {
			Legacy\Migracao_Base::rodar_migracoes();
		});

		return redirect('/dev/migrations/');
	});

	Route::get('/', function () {
		return redirect('/provas/datas/');
	});

	// Servidor
	Route::prefix('server')->group(function () {
		Route::post('/flush-pagespeed-cache', function () {
			$scriptDir = realpath(__DIR__ . '/../scripts/');
			$script = $scriptDir . '/flush-pagespeed-cache.sh';

			$output = 'Executando: ' . $script . "\r\n";
			$output .= shell_exec($script);
			$output .= 'Execução finalizada.';

			return response($output)
                ->header('Content-Type', 'text/plain');
		});
	});

	// E-mail diário
	Route::get('/email-diario', function (Request $req) {

		$email_diario = Datalist::on('email-diario');

		if ($remove_id = $req->query('remove')) {
			$email_diario->find($remove_id)->first()->delete();
			return redirect('/dev/email-diario');
		}

		return view('Admin::Developer.EmailDiario', [
			'emails' => $email_diario->get()
		]);
	});
	Route::post('/email-diario', function (Request $req) {

		$email_diario = Datalist::on('email-diario');

		if ($email = $req->input('email')) {
			$email_diario->insert($email);
		}

		return redirect('/dev/email-diario');
	});

	Route::group(['prefix' => 'logs'], function () {
		Route::get('/process', function () {
			$procs = Process::all()->sortByDesc('updated_at');
			return view ('Admin::Developer.Logs.Process', [
				'procs' => $procs
			]);
		});
		Route::get('/event', function () {
			$events = Event::all()->sortByDesc('time');
			return view ('Admin::Developer.Logs.Events', [
				'events' => $events
			]);
		});
		Route::get('/error', function () {
			$files = collect(Storage::allFiles('error-logs'))->transform(function ($filename) {
				return substr($filename, strlen('error-logs/'));
			});

			return view ('Admin::Developer.Logs.Error', [
				'logs' => $files
			]);
		});
		Route::get('/error/{file}', function ($file) {
			return response(Storage::get('error-logs/' . $file))
				->header('Content-Type', 'text/plain');
		});
	});

	// Configurações de Integrações com APIs

	Route::group(['prefix' => 'api'], function () {

		// API Interna

		Route::get('/', function () {
			$api_keys = API_Key::all();

			return view ('Admin::Developer.API.Internal', [
				'keys' => $api_keys
			]);
		});
		Route::post('/', function () {
			dd(API_Key::generate());
			return redirect('/dev/api/');
		});

		// RD Station

		Route::get('/rd-station', function () {

			$token = RDStation_Univestibular::token();

			return view ('Admin::Developer.API.RDStation', [
				'lead_status' => Lead_Status::orderBy('base_id')->get(),
				'rd_token' => $token['token'],
				'rd_token_privado' => $token['token_privado'],
				'rd_scripts' => Settings::get('rd_scripts')
			]);
		});
		Route::post('/rd-station', function (Request $req) {

			// Salvar token

			RDStation_Univestibular::token($req->input('rd_token'), $req->input('rd_token_privado'));

			// Salvar tags e scripts

			Settings::set('rd_scripts', $req->input('rd_scripts'));

			// Atualizar campos

			foreach ($req->status as $codigo => $valores) {
				$status = Lead_Status::find($codigo);
				foreach($valores as $prop => $val) {
					$status->{$prop} = $val;
				}
				$status->save();
			}

			return redirect ('/dev/api/rd-station');
		});
		Route::post('/rd-station/export', function () {
			Process::init('developer')->text('Exportação RD Station')->run(function() {
				set_time_limit(300);
				set_time_limit(600);
				set_time_limit(0);

				$total = Lead::count();
				$c = 0;

				foreach (Lead::cursor() as $lead) {
					$c++;
					
					try {
						$lead->converter_rd();
					} catch (\Exception $e) {

					}
				}

				return redirect ('/dev/api/rd-station');
			});
		});
	});
});