<?php

use App\Campanha;
use App\Modules\Admin\Graficos;

Route::get('/', function () {
	return view ('Admin::BI.performance_index', [
		'campanhas' => Campanha::all()
	]);
});
Route::get('/{c1}/{c2}', function ($c1, $c2) {

	// Inicializar Dados
	$dados = [
		'campanha1' => Campanha::find($c1),
		'campanha2' => Campanha::find($c2),
		'dados_leads' => collect(),
	];

	// Data mínima
	/*$dados['data_min'] = date('Y-m-d', min(strtotime($dados['campanha1']->inicio), strtotime($dados['campanha2']->inicio)));
	$dados['data_max'] = date('Y-m-d', max(strtotime($dados['campanha1']->fim), strtotime($dados['campanha2']->fim)));*/

	$d1 = $dados['campanha1']->duracaoEfetiva;
	$d2 = $dados['campanha2']->duracaoEfetiva;
	$dMax = max($d1, $d2);

	$dados['data_min'] = $dados['campanha1']->inicioEfetivo;
	// $dados['data_max'] = $dados['campanha1']->fimEfetivo;
	$dados['data_max'] = $dados['campanha1']->inicioEfetivo->addDays($dMax);

	// Criar Gráficos
	$graficos = [];

	// Gráfico Performance Candidatos
	$dados['titulo'] = 'Candidatos';
	$dados['targetStatus'] = 'LEAD';
	$dados['dados_leads'] = 
		collect()
			->merge($dados['campanha1']->candidatos()->get())
			->merge($dados['campanha2']->candidatos()->get());
	$graficos[] = Graficos::make('campanha1_vs_campanha2', $dados);

	// Gráfico Performance Inscritos
	$dados['titulo'] = 'Inscritos';
	$dados['targetStatus'] = 'INSC';
	$dados['dados_leads'] = 
		collect()
			->merge($dados['campanha1']->inscritos_total_unique()->get())
			->merge($dados['campanha2']->inscritos_total_unique()->get());
	$graficos[] = Graficos::make('campanha1_vs_campanha2', $dados);

	// Gráfico Performance Matriculas
	$dados['titulo'] = 'Matrículas';
	$dados['targetStatus'] = 'MATR';
	$dados['dados_leads'] = 
		collect()
			->merge($dados['campanha1']->matriculados_total_unique()->get())
			->merge($dados['campanha2']->matriculados_total_unique()->get());
	$graficos[] = Graficos::make('campanha1_vs_campanha2', $dados);

	// Processar Gráficos
	Graficos::async($graficos);

	return view ('Admin::BI.performance', ['graficos' => $graficos, 'dados' => $dados]);
});