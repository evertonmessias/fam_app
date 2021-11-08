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


Route::get('/', function () use ($module) {
	$options = [];
	if (isset($module->options))
		$options = $module->options;

	return view ('TOP::index', $options);
});

// Quiz
Route::post('/quiz', function (Request $req) use ($module) {
	$options = [];
	if (isset($module->options))
		$options = $module->options;

	$nome = $req->input('nome');
	$email = $req->input('email');

	if (empty($nome) || empty($email))
		return redirect('/');

	$req->session()->put('cadastro', [
		'nome' => $nome,
		'email' => $email
	]);

	return view ('TOP::pergunta', $options);
});
Route::get('/quiz', function () {
	return redirect ('/');
});

// Resultados
Route::post('/resultados', function (Request $req) {
	$dados = $req->session()->get('cadastro');
	$campos = $req->input('resultados');

	// Valida dados
	if (empty($dados) || empty($campos))
		return redirect ('/');

	$nome = $dados['nome'];
	$email = $dados['email'];

	if (empty($nome) || empty($email))
		return redirect ('/');

	// Calcular pontuação
	$campos = array_flatten(json_decode($campos));
	$score = [];

	foreach ($campos as $area) {
		if (!isset($score[$area]))
			$score[$area] = 0;

		$score[$area]++;
	}

	$score = collect($score)->sort()->reverse();

	// Descobrir o Curso
	$area = DB::table('cursos_categorias')->where('id', $score->keys()->first())->first();

	// Salvar no BD
	$aluno = Aluno::where('email', $email)->first();
	if (!is_null($aluno))
		$aluno = $aluno->id;

	DB::table('top_resultados')->insert([
		'email' => $email,
		'aluno_id' => $aluno,
		'resultados' => json_encode($score),
		'raw' => json_encode([
			'campos' => $campos,
			'score' => $score,
			'area' => $area->id
		])
	]);

	// Listar cursos
	$cursos = Curso::where('categoria_id', $area->id)->get();

    /////////////////////////////////////////////////////////////////////////////
    // Atualizar RD Station

	$event = Event::register('top-finished', 'Teste de Orientação Profissional');
	$event->meta('email', $email);
	$event->meta('area', $area->id);

    $dados_rd = [];

    $dados_rd['top_area'] = $area->nome;
    $dados_rd['top_area_id'] = $area->id;

    $rd = new RDStation_Univestibular ();
    
    if (!env('APP_DEBUG'))
        $rd->converter_manual($email, 'Teste de Orientação Profissional', $dados_rd); // Converte o Lead

	// Gera HTML
	return view ('TOP::resultados', [
		'area' => $area,
		'cursos' => $cursos
	]);
});