<?php

namespace App\Modules\Admin;

use App\Aluno;
use App\Campanha;
use App\Lead;
use App\Lead_Status;
use App\Unidade;

use Carbon\Carbon;

use App\Helpers;
use App\Benchmark;
use Illuminate\Support\Facades\Cache;

use App\Modules\Admin\Grafico;
use App\Modules\Admin\GraficoTempo;
use App\Modules\Admin\GraficoRosca;
use App\Modules\Admin\GraficoGenero;
use App\Modules\Admin\GraficoGauge;
// use App\Modules\Admin\GraficosAsync;
// use App\Modules\Admin\GraficosWorker;

class Graficos {
	////////////////////////////
	// Funções da classe
	////////////////////////////

	protected static $min, $max, $campanha;

	public static function async ($graficos = []) {

		foreach ($graficos as $grafico) {
			$grafico->async();
		}

		return $graficos;
	}

	public static function make ($grafico, $args = [], $opts = []) {

		if (isset($args['campanha'])) {
			static::$campanha = Campanha::find($args['campanha']);
			static::$min = $args['data_min'];
			static::$max = $args['data_max'];
		}

        $args = array_merge($args, $opts);

		return static::{$grafico}($args);
	}
	public static function render ($grafico, $args = []) {
		return static::make($grafico, $args)->organizar()->render();
	}
	public static function json ($grafico, $args = []) {
		return static::make($grafico, $args)->organizar()->json();
	}

	protected static $tipos_lead = [];
	protected static function tipos_lead () {
		if (empty(static::$tipos_lead)) {
			$status = Lead_Status::all();

	    	foreach ($status as $tipo) {
	    		static::$tipos_lead[$tipo->codigo] = $tipo;
	    	}
	    }

    	return static::$tipos_lead;
	}

    ////////////////////////////
    // Gráfico de Dia da Prova
    ////////////////////////////

    protected static function dia_prova ($options = []) {

        $tipos_lead = static::tipos_lead();
        $campanha = static::$campanha;
        $min = static::$min;
        $max = static::$max;

        ////////////////////////////////////////////////////////////////////
        // Gráfico de Dia da Prova

        // $grafico = new Grafico($campanha->inscritos()->with('prova', 'prova.data', 'prova.local'), 'GraficoDataProva');

        /*$unidade = $campanha->unidade;
        $unidade = Unidade::find(1); // TODO: implementar unidades nas campanhas*/

        $locais = $campanha->locais_provas;

        $grafico = new Grafico($locais, 'GraficoDataProva');

        $grafico->titulo = (isset($options['title']) ? $options['title'] : 'Dia da Prova');
        $grafico->setHelperInfo('maior_valor' , 0);

        // Processar dados
        $grafico->preparar(function ($linha, &$chart) use ($campanha, $min, $max, $grafico) {

            $local = $linha;
            $datas = $linha->global_relation('datas_provas')->sortBy('hora')->filter(function ($data) {
                if ($data->hora < Carbon::today())
                    return false;
                return true;
            });

            foreach ($datas as $data) {
                $provas = $data->global_relation('provas')->filter(function($prova) use ($campanha) {
                    if ($prova->campanha_id == $campanha->id)
                        return true;
                    return false;
                });

                $data_id = $data->hora()->format('Y-m-d');
                $horario_id = $data->hora()->format('H:i');

                $helper_id = ['datas', $data_id, 'horarios', $horario_id, $local->id];

                // Helper - Data
                $grafico->setHelperInfo(['datas', $data_id, 'data'], $data);

                // Helper - Local
                $grafico->setHelperInfo(array_merge($helper_id, ['local']), $local);
                $grafico->setHelperInfo(array_merge($helper_id, ['data']), $data);
                $grafico->setHelperInfo('locais.' . $local->id, $local);

                // Helper - Quantidade
                $grafico->setHelperInfo(array_merge($helper_id, ['quantidade']), $provas->count());

                // Para calcular maior valor do gráfico (ajuda na hora de renderizar)

                $grafico->fnHelperInfo('maior_valor', function ($maior) use ($data) {
                    if ($data->maximo > $maior)
                        return $data->maximo;
                });

                // Pós-processar gráfico
                $grafico->sort_after(function ($a, $b) {
                    return strcmp($a, $b);
                }, true);

                $chart->set($data_id, $provas->count());
            }
        });

        return $grafico;
    }

    ////////////////////////////
    // Gráfico de Panorama de Cursos
    ////////////////////////////

    protected static function panorama_cursos ($options = []) {

        $tipos_lead = static::tipos_lead();
        $campanha = static::$campanha;
        $min = static::$min;
        $max = static::$max;

        ////////////////////////////////////////////////////////////////////
        // Gráfico de Dia da Prova

        $grafico = new Grafico($campanha->cursos(), 'GraficoCursos');
        $grafico->titulo = (isset($options['title']) ? $options['title'] : 'Detalhamento dos Cursos');
        $grafico->setHelperInfo('maior_valor' , 0);

        // Processar dados
        $grafico->preparar(function ($linha, &$chart) use ($campanha, $min, $max, $grafico) {
            if (is_null($linha)) return;

            $total = $linha->global_relation('inscritos');

            // $inscritos = $total->filter(function ($lead, $k) use ($campanha) { return ($lead->campanha_id == $campanha->id && ($lead->status_id == 'INSC' || $lead->status_id == 'MATR')); });
            // $matriculados = $inscritos->filter(function ($lead, $k) use ($campanha) { return ($lead->status_id == 'MATR'); });

            $inscritos = $linha->inscritos_total($campanha)->get();
            $matriculados = $linha->matriculados_total($campanha)->get();

            $inscritos = $inscritos->count();
            $matriculados = $matriculados->count();
            $aproveitamento = ($linha->vagas == 0) ? 0 : 100 * $matriculados / $linha->vagas;
            $conversao = ($inscritos == 0) ? 0 : 100 * $matriculados / $inscritos;

            $id = $linha->id;

            $grafico->setHelperInfo(['cursos', $id], [
                'curso' => $linha,
                'inscritos' => $inscritos,
                'matriculados' => $matriculados,
                'aproveitamento' => $aproveitamento,
                'conversao' => $conversao
            ]);

            $chart->add($id);
        });

        return $grafico;
    }

    ////////////////////////////
    // Gráfico de Bases
    ////////////////////////////

    protected static function bases ($options = []) {

        $tipos_lead = static::tipos_lead();
        $campanha = static::$campanha;
        $min = static::$min;
        $max = static::$max;

        ////////////////////////////////////////////////////////////////////
        // Gráfico de Dia da Prova

        $grafico = new Grafico($campanha->leads_total(), 'GraficoBases');
        $grafico->titulo = (isset($options['title']) ? $options['title'] : 'Bases de Leads');

        // Processar dados
        $grafico->preparar(function ($linha, &$chart) use ($campanha, $min, $max, $grafico) {

            $id = $linha->status_id;

            $grafico->setHelperInfo(['bases', $id], [
                'tipo' => $linha->global_relation('status')
            ]);

            $chart->add($id);
        });

        return $grafico;
    }

	////////////////////////////
	// Tipo estático
	////////////////////////////

	protected static function gauge ($options = []) {

		////////////////////////////////////////////////////////////////////
    	// Gráfico de Indicação

    	$grafico_gauge = new GraficoGauge();
    	$grafico_gauge->titulo = (isset($options['title']) ? $options['title'] : '');
    	$grafico_gauge->dados = $options;

    	// Processar dados
    	/*$grafico_gauge->preparar(function ($linha, &$grafico) use ($tipos_lead, $min, $max) {
    		if (!isset($linha->midia))
    			return;

    		$tipo = $linha->midia->tipo;

    		$grafico->add($tipo->nome);
    	});*/

    	return $grafico_gauge;
	}

	////////////////////////////
	// Aqui é onde processamos
	////////////////////////////

    protected static function leads_inscritos_matriculas ($options = []) {

        $tipos_lead = static::tipos_lead();
        $campanha = static::$campanha;
        $min = static::$min;
        $max = static::$max;

        /////////////////////////////////////////////////////////////////
        // Gráfico de Leads x Inscritos x Matrículas

        $grafico_conversao = new GraficoTempo($campanha->leads_total()->with('historico'));
        $grafico_conversao->titulo = (isset($options['title']) ? $options['title'] : 'Eventos: Leads x Inscritos x Matriculas Diários');

        // Preparar gráfico
        foreach ($tipos_lead as $tipo) {
            $grafico_conversao->dados()->chart($tipo->codigo)->date_range($min, $max, 0);
        }

        // Processar dados
        $grafico_conversao->preparar(function ($linha, &$grafico) use ($tipos_lead, $min, $max) {

            // dd($linha);
            $historico = $linha->global_relation('historico');

            foreach ($historico as $evento) {
                $tipo = $tipos_lead[$evento->status_new];
                $data = date('Y-m-d', $evento->at->timestamp);

                $grafico->chart($tipo->codigo, $tipo->nome, 's')->add($data);
            }
        });

        return $grafico_conversao;
    }

    // Comparativo de Campanhas
    protected static function campanha1_vs_campanha2 ($options = []) {

        // dd($options);

        $tipos_lead = static::tipos_lead();
        $campanha1 = $options['campanha1'];
        $campanha2 = $options['campanha2'];
        $min = $options['data_min'];
        $max = $options['data_max'];

        $targetStatus = isset($options['targetStatus']) ? $options['targetStatus'] : 'LEAD';

        $leads = $options['dados_leads']->sortBy('created_at');

        $min1 = $campanha1->inicioEfetivo;
        $min2 = $campanha2->inicioEfetivo;

        $getData = function ($lead, $campanha, $status = 'LEAD') use ($campanha1, $campanha2, $min1, $min2) {
            if (is_null($lead->global_relation('historico')->where('status_new', $status)->first()))
                return null;

            if ($campanha->id == $campanha1->id)
                return $lead->global_relation('historico')->where('status_new', $status)->first()->at;
            else {
                $dias = $lead->global_relation('historico')->where('status_new', $status)->first()->at->diffInDays($min2);
                return $min1->copy()->addDays($dias);
            }
        };

        /////////////////////////////////////////////////////////////////
        // Gráfico de Candidatos x Inscritos

        $grafico_conversao = new GraficoTempo($leads, 'GraficoTempoComparativo');
        $grafico_conversao->titulo = (isset($options['title']) ? $options['title'] : $options['titulo'] . ': ' . $campanha1->nome . ' x ' . $campanha2->nome);

        // Preparar gráfico
        foreach ([$campanha1->id, $campanha2->id] as $cid) {
            // $grafico_conversao->dados()->chart($cid)->date_range($min, $max, 0);
        }


        $l = 0;
        $buffer = [];
        $buffer[$campanha1->id] = 0;
        $buffer[$campanha2->id] = 0;

        $candidatos = [];
        $candidatos[$campanha1->id] = [];
        $candidatos[$campanha2->id] = [];

        // Processar dados
        $grafico_conversao->preparar(function ($linha, &$grafico) use ($campanha1, $campanha2, $getData, $targetStatus, &$buffer, &$candidatos, &$l) {

            $campanha = $linha->global_relation('campanha');
            $campanha_main = ($campanha->id == $campanha1->id);

            // Evitar candidatos repetidos ou que trocaram de campanhas
            if (!isset($candidatos[$campanha->id]) || in_array($linha->aluno_id, $candidatos[$campanha->id]))
                return;

            // Evitar linhas que tenham pulado etapas (LEAD -> MATR, etc)
            $data = $getData($linha, $campanha, $targetStatus);
            if (is_null($data))
                return;

            // Filtrar caso o ID da campanha não bata
            if (!array_key_exists($campanha->id, $buffer))
                return;

            // Gerar data no formato YYYY-mm-dd
            $data = date('Y-m-d', $data->timestamp);

            // Evitar candidatos repetidos
            $candidatos[$campanha->id][] = $linha->aluno_id;

            // if ($grafico->chart($campanha->id)->get($data) == 0)
                // $grafico->chart($campanha->id)->add($data);
                // $grafico->chart($campanha->id)->set($data, $buffer[$campanha->id]);

            $buffer[$campanha->id]++;
            $grafico->chart($campanha->id, $campanha->nome, 's')->add($data);
        });

        // Pós-processar dados
        $grafico_conversao->post_exec(function ($chart) {
            // Aqui nós iremos pegar os dados pontuais e criar uma rampa cumulativa
            foreach ($chart->graficos as $i => $grafico) {
                $dados = $grafico->dados;

                // Organizar por chaves primeiro
                ksort($dados);

                $buffer = 0;
                foreach ($dados as $k => $value) {
                    $buffer += $value;
                    $dados[$k] = $buffer;
                }

                $grafico->dados = $dados;
                $chart->graficos[$i] = $grafico;
            }
            return $chart;
        });

        return $grafico_conversao;
    }

	protected static function cidades ($options = []) {

		$tipos_lead = static::tipos_lead();
		$campanha = static::$campanha;
		$min = static::$min;
		$max = static::$max;

    	////////////////////////////////////////////////////////////////////
    	// Gráfico de Cidades

    	// $grafico_cidades = new GraficoRosca($campanha->leads_total());
    	$grafico_cidades = new GraficoMapa($campanha->leads_total());
    	$grafico_cidades->titulo = (isset($options['title']) ? $options['title'] : 'Cidades');

    	// Processar dados
    	$grafico_cidades->preparar(function ($linha, &$grafico) use ($tipos_lead, $min, $max) {
    		$aluno = $linha->global_relation('aluno');

    		if (!isset($aluno) || !isset($aluno->cidade_id))
    			return;

    		$cidade = $aluno->cidade_id;

    		$grafico->add($cidade);
    	});

    	return $grafico_cidades;
	}

	protected static function idades ($options = []) {

		$tipos_lead = static::tipos_lead();
		$campanha = static::$campanha;
		$min = static::$min;
		$max = static::$max;

    	////////////////////////////////////////////////////////////////////
    	// Gráfico de Cidades

    	$grafico_idades = new Grafico($campanha->leads_total(), 'GraficoLinha');
    	$grafico_idades->titulo = (isset($options['title']) ? $options['title'] : 'Idades');

    	// Processar dados
    	$grafico_idades->preparar(function ($linha, &$grafico) use ($tipos_lead, $min, $max) {

            $aluno = $linha->global_relation('aluno');

    		// TODO: remover essa validação dos 60 anos

    		if (!isset($aluno) || $aluno->idade == false || $aluno->idade > 60)
    			return;

    		$grafico->chart('Idades')->add($aluno->idadeNaData($linha->created_at));
    	});

    	return $grafico_idades;
	}

    protected static function indicacao ($options = []) {

        $tipos_lead = static::tipos_lead();
        $campanha = static::$campanha;
        $min = static::$min;
        $max = static::$max;

        ////////////////////////////////////////////////////////////////////
        // Gráfico de Indicação

        $grafico_indicacao = new GraficoRosca($campanha->leads_total());
        $grafico_indicacao->titulo = (isset($options['title']) ? $options['title'] : 'Como nos Conheceu');

        // Processar dados
        $grafico_indicacao->preparar(function ($linha, &$grafico) use ($tipos_lead, $min, $max) {
            $tipo = $linha->global_relation('midia');

            if (!isset($tipo) || is_null($tipo))
                return;

            $grafico->add($tipo->nome);
        });

        return $grafico_indicacao;
    }

    protected static function custom ($options = []) {

        $tipos_lead = static::tipos_lead();
        $campanha = static::$campanha;
        $min = static::$min;
        $max = static::$max;

        ////////////////////////////////////////////////////////////////////
        // Gráficos Personalizados

        $grafico_combo = new GraficoCombo($campanha->leads_total());

        foreach ($campanha->campos_personalizados as $campo) {
            $grafico_indicacao = new GraficoRosca($campanha->leads_total());
            $grafico_indicacao->titulo = $campo['label'];

            // Processar dados
            $grafico_indicacao->preparar(function ($linha, &$grafico) use ($campo) {
                if (!isset($linha->dados_adicionais) || !isset($linha->dados_adicionais[$campo['nome']]))
                    return;

                $valor = $linha->dados_adicionais[$campo['nome']];
                $nome = Cache::remember('custom-' . $campo['nome'] . '-' . $valor, 5, function () use ($campo, $valor) {
                    foreach ($campo['valores'] as $v) {
                        if ($v['valor'] == $valor)
                            return $v['label'];
                    }
                    return $valor;
                });
                $grafico->add($nome);
            });

            $grafico_combo->add_chart($grafico_indicacao);
        }

        return $grafico_combo;
    }

	protected static function genero ($options = []) {

		$tipos_lead = static::tipos_lead();
		$campanha = static::$campanha;
		$min = static::$min;
		$max = static::$max;

    	////////////////////////////////////////////////////////////////////
    	// Gráfico Masc x Fem
       
    	$grafico_genero = new GraficoGenero($campanha->leads_total()->distinct('aluno_id'));
    	$grafico_genero->titulo = 'Masculino x Feminino';

    	// Processar dados
    	$grafico_genero->preparar(function ($linha, &$grafico) use ($tipos_lead, $min, $max) {
            $aluno = $linha->global_relation('aluno');

    		if (!isset($aluno) || !isset($aluno->sexo) || empty(trim($aluno->sexo)))
    			return;

    		$grafico->add($aluno->sexo);
    	});

    	return $grafico_genero;
	}
}