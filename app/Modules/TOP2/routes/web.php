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

use App\RDStation_Univestibular;

use App\System\Email;
use App\System\Event;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

Route::get('/', function (Request $req) use ($module) {
	return view ('TOP2::index', [
		'api_path' => $req->root() . '/api',
		'module' => $module
	]);
});

Route::any('/assets/{all?}', function (Request $req, $file) use ($module) {
	$dados = $req->session()->get('obj');

	$file_path = realpath(__DIR__ . '/../') . '/assets/' . $file;

	// Retornar 404 se arquivo não existir
	if (!file_exists($file_path))
		abort(404);

	// Criar cache de mime-type, 7 dias
	$mime = Cache::remember('mime-' . md5($file_path), 60 * 24 * 7, function () use ($file_path) {
		// Pega o Mime-Type do arquivo
		return fn_mime_content_type($file_path);
	});

	return response()->file($file_path, ['Content-Type' => $mime]);
})->where('all', '.+');

Route::any('{all?}', function (Request $req, $file) use ($module) {
	// Não permitir assets fora da pasta assets
	abort(403);
})->where('all', '.+');

// Isso faz o sistema não buscar pastas padrão de views nem de recursos
$IGNORE_FILES = true;