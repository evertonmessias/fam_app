<?php

use App\Aluno;
use App\Campanha;
use App\Curso;
use App\Lead;
use App\Lead_History;
use App\Lead_Status;

use App\Helpers;
use App\Benchmark;

use App\Modules\Admin\Graficos;
use App\Modules\Admin\Grafico;
use App\Modules\Admin\GraficoTempo;
use App\Modules\Admin\GraficoRosca;
use App\Modules\Admin\GraficoGenero;
use App\Modules\Admin\GraficoGauge;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

set_time_limit(150);

/*
$total_campanhas = 0;
//$campanhas = [];
foreach (Campanha::cursor() as $campanha) {
    // Valida se campanha está ativa           
    if ($campanha->relatorios) {        
        $total_campanhas++;        
        //array_push($campanhas,$campanha);
    }
}
echo $total_campanhas;
*/

Route::get('/graph/{chart}', function (Request $req, $chart) {
	$dados = $req->session()->get('obj', []);

	return Graficos::json($chart, $dados);
});
Route::get('/dashboard', function (Request $req) {
    // $dados = Cache::remember('chart-data', 5, function () use ($req) {
    	$dados = $req->session()->get('obj', []);
    	$campanha = Campanha::find($dados['campanha']);

        Benchmark::hook_db();

        Benchmark::run('totais-stat');

    	// Totais
        $t_leads = $campanha->leads_total()->count();
        $t_inscritos = $campanha->inscritos_total_unique()->get()->count();
        // $t_inscritos = $campanha->inscritos_total()->distinct('aluno_id')->count();
        $t_matriculados = $campanha->matriculados_total_unique()->get()->count();
        // $t_matriculados = $campanha->matriculados_total()->distinct('aluno_id')->count();
        // $candidatos = $campanha->leads_total()->distinct('aluno_id')->count('aluno_id');
        $candidatos = $campanha->candidatos()->get()->count();
        $provas = $campanha->inscritos()->join('provas', 'leads.prova_id', '=', 'provas.id')->join('provas_datas', 'provas.data_id', '=', 'provas_datas.id')->where('provas_datas.hora', '>', Carbon::today()->toDateTimeString())->count();
        $ausentes = $campanha->ausentes()->distinct('aluno_id')->count();
        $aprovados = $campanha->aprovados()->distinct('aluno_id')->count();

        Benchmark::run('corrigir');

    	// Totais corrigidos
        // $matriculados = $t_matriculados;
    	// $inscritos = $t_inscritos + $t_matriculados;
    	// $leads = $t_leads + $inscritos;

        $matriculados = $t_matriculados;
        $inscritos = $t_inscritos;
        $leads = $t_leads;

        Benchmark::run('tx_conv');

    	if ($leads == 0 || $inscritos == 0)
    		$t_conversao = 'N/D';
    	else
            $t_conversao = round(($matriculados / ($inscritos)) * 100);
    		// $t_conversao = round(($matriculados / $inscritos) * 100);

        Benchmark::run('recentes');

    	// Recentes

    	if ($campanha->is_ativa) {
        	$gr_date = date('Y-m-d', strtotime('15 days ago'));
        	$recentes = Lead_History::join('leads', 'lead_history.lead_id', '=', 'leads.id')->where('lead_history.at', '>', $gr_date)->where('leads.campanha_id', '=', $campanha->id)->with('lead')->with('lead.aluno')->get();
        } else {
        	$date_end = new Carbon($campanha->fim);
        	$lt_date = date('Y-m-d', $date_end->timestamp);
        	$gr_date = date('Y-m-d', $date_end->subDays(15)->timestamp);

        	$recentes = Lead_History::join('leads', 'lead_history.lead_id', '=', 'leads.id')->where('lead_history.at', '>', $gr_date)->where('lead_history.at', '<', $lt_date)->where('leads.campanha_id', '=', $campanha->id)->with('lead')->with('lead.aluno')->get();
        }

    	$tipos_lead = [];
    	foreach (Lead_Status::all() as $tipo) {
            Benchmark::run('tipo_lead');
    		$tipos_lead[$tipo->codigo] = $tipo;
    	}

        Benchmark::run('preset');

    	$min = $dados['data_min'];
    	$max = $dados['data_max'];

        // Semestre
        $multiplicador = 6;

        $soma_cursos = 0;
        $soma_leads = 0;
        $soma_inscritos = 0;
        $soma_matriculas = 0;
        $soma_matriculas_cursos = 0;

        /*

        // Somar matriculas

        foreach ($campanha->matriculados()->with('curso')->cursor() as $matriculado) {
            $v = $matriculado->curso->valor * $multiplicador;

            $soma_inscritos += $v;
            $soma_matriculas += $v;
            $soma_matriculas_cursos += $v * $matriculado->curso->duracao;
        }

        // Somar inscritos

        foreach ($campanha->inscritos()->with('curso')->cursor() as $aluno) {
            $soma_inscritos += $aluno->curso->valor * $multiplicador;
        }

        // Somar leads totais

        foreach ($campanha->leads_total()->with('curso')->cursor() as $aluno) {
            $soma_leads += $aluno->curso->valor * $multiplicador;
        }

        */

        Benchmark::run('count-leads');

        $leads_all = $campanha->leads_total()->with('curso');

        foreach ($leads_all->cursor() as $lead) {

            if (is_null($lead->global_relation('curso')))
                $v = 0;
            else
                $v = $lead->global_relation('curso')->valor * $multiplicador;

            Benchmark::run('proj');

            switch ($lead->status_id) {
                case 'MATR':
                    $soma_matriculas += $v;
                    $soma_matriculas_cursos += $v * $lead->global_relation('curso')->duracao;
                case 'INSC':
                    $soma_inscritos += $v;
                case 'LEAD':
                default:
                    $soma_leads += $v;
                    break;
            }
        }

        Benchmark::run('proj-cursos');

        // Somar cursos

        foreach (Curso::cursor() as $curso) {
            $soma_cursos += $curso->valor * $curso->duracao * $curso->vagas * $multiplicador;
        }

        Benchmark::run('c-init');

    	// Gerar gráficos

        $grafico_bases = Graficos::make('bases', $dados);
    	$grafico_conversao = Graficos::make('leads_inscritos_matriculas', $dados);
    	$grafico_cidades = Graficos::make('cidades', $dados);
    	$grafico_idades = Graficos::make('idades', $dados);
    	$grafico_indicacao = Graficos::make('indicacao', $dados);
        $grafico_genero = Graficos::make('genero', $dados);
        $grafico_cursos = Graficos::make('panorama_cursos', $dados);

        $grafico_budget = Graficos::make('gauge', ['title' => 'Budget', 'prefix' => 'R$', 'min' => 0, 'max' => $campanha->budget, 'value' => $campanha->budget_consumido, 'color' => '#B51212']);

        $grafico_budget_inscritos = Graficos::make('gauge', ['title' => 'Matriculados x Inscritos', 'prefix' => 'R$', 'min' => 0, 'max' => $soma_inscritos, 'value' => $soma_matriculas, 'color' => '#0b2f7f']);
        $grafico_budget_leads = Graficos::make('gauge', ['title' => 'Inscritos x Leads', 'prefix' => 'R$', 'min' => 0, 'max' => $soma_leads, 'value' => $soma_inscritos, 'color' => '#26B99A']);

        $grafico_budget_cursos = Graficos::make('gauge', ['title' => 'Leads x Vagas', 'prefix' => 'R$', 'min' => 0, 'max' => $soma_cursos, 'value' => $soma_leads, 'color' => '#9b59b6']);

        $grafico_dia_prova = Graficos::make('dia_prova', $dados);

        $grafico_custom = Graficos::make('custom', $dados);

    	// Processar gráficos

    	$test = Graficos::async([$grafico_conversao, $grafico_cursos, $grafico_bases, $grafico_cidades, $grafico_idades, $grafico_indicacao, $grafico_genero, $grafico_dia_prova, $grafico_custom]);

        Benchmark::run('dias');

    	$dias = [];
    	foreach ($recentes as $lead) {
    		$date = date('Y-m-d', $lead->at->timestamp);

    		if (!isset($dias[$date]))
    			$dias[$date] = ['leads' => 0, 'inscritos' => 0, 'matriculados' => 0, 'inscritos_cpf' => [], 'leads_cpf' => [], 'candidatos' => []];

            $cpf = $lead->lead->aluno->cpf;
            $dias[$date]['candidatos'][$cpf] = true;

    		switch (strtoupper($lead->status_new)) {
                case 'PREMATR':
                case 'MATR':
                    $dias[$date]['matriculados']++;
                    break;
    			case 'INSC':
    				$dias[$date]['inscritos']++;
                    $dias[$date]['inscritos_cpf'][$cpf] = true;
    				break;
    			case 'LEAD':
    				$dias[$date]['leads']++;
                    $dias[$date]['leads_cpf'][$cpf] = true;
    				break;
    		}
    	}

        Benchmark::run('dias-proc');

        if(empty($dias))
            $dias = [date('Y-m-d') => ['leads' => 0, 'inscritos' => 0, 'matriculados' => 0, 'inscritos_cpf' => [], 'leads_cpf' => [], 'candidatos' => []]];

        $lConv = 0;
        $sConv = 0;
        $mConv = 0;
    	foreach ($dias as $data => $dados) {
            $dados['leads'] = count($dados['candidatos']);
            $dados['inscritos'] = count($dados['inscritos_cpf']);

            $sConv += count($dados['candidatos']);
            $mConv += $dados['matriculados'];
            /*if ($dados['inscritos'] > 0 && $dados['leads'] > 0)
              $lConv = $dados['conversao'] = round(($dados['inscritos'] / $dados['leads']) * 100);
    		  // $lConv = $dados['conversao'] = round(($dados['matriculados'] / $dados['inscritos']) * 100);
            else
              $dados['conversao'] = $lConv;*/

            if ($sConv == 0 || $mConv == 0)
                $dados['conversao'] = $lConv;
            else
                $dados['conversao'] = $lConv = round(100 * $mConv / $sConv);
          
    		$dias[$data] = $dados;
    	}

        $graficos = [
            $grafico_conversao,
            $grafico_dia_prova,
            $grafico_cursos,
            $grafico_bases,
            $grafico_cidades,
            $grafico_idades,
            $grafico_genero,
            $grafico_indicacao,
            $grafico_custom,
            // $grafico_budget,
            // $grafico_budget_inscritos,
            // $grafico_budget_leads,
            // $grafico_budget_cursos,
        ];
        Benchmark::finish();

        /*

        ob_start();
        Benchmark::results(true);
        $c = ob_get_contents();
        ob_end_clean();

        dd($c);

        */

        $dados = [
            'total' => ['leads' => $leads, 'inscritos' => $inscritos, 'conversao' => $t_conversao, 'matriculados' => $t_matriculados, 'candidatos' => $candidatos, 'provas' => $provas, 'ausentes' => $ausentes, 'aprovados' => $aprovados],
            'recentes' => Helpers::array_rotate($dias),
            'graficos' => $graficos
        ];

        // return $dados;

    // });

    return view ('Admin::dashboard', $dados);
});