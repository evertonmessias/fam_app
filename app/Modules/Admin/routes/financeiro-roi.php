<?php

use App\Campanha;
use App\Fornecedor;
use App\Nota_Fiscal;
use App\Nota_Fiscal_Campanha;
use App\Midia;
use App\Midia_Tipo;
use App\Helpers;

use Illuminate\Http\Request;

use Carbon\Carbon;

$middle = function (Request $req, $next) {
	return $next($req);
};

Route::group(['middleware' => $middle], function () {
	Route::get('/', function () {
		return view ('Admin::Financeiro.ROI.index');
	});
	Route::get('/print', function () {
		return view ('Admin::Financeiro.ROI.index', ['print' => true]);
	});

	/* API */
	Route::group(['prefix' => 'api'], function () {
		Route::get('/campanhas', function () {
			$campanhas = [];

			foreach (Campanha::with('notas_fiscais')->cursor() as $campanha) {
				// Puxar Notas Fiscais
				$campanha->notas_fiscais = $campanha->global_relation('notas_fiscais');
				$campanha->budget_consumido = $campanha->budget_consumido;

				// Inicializar
				$leads = $campanha->leads_total();
				$inscritos = $campanha->inscritos_total();
				$matriculados = $campanha->matriculados_total();
				$candidatos = $campanha->candidatos();

				// Quantidade de LxIxMxC
				$campanha->qtd_leads = $leads->count();
				$campanha->qtd_inscritos = $inscritos->count();
				$campanha->qtd_matriculados = $matriculados->count();
				$campanha->qtd_candidatos = $candidatos->get()->count();
				// $campanha->qtd_candidatos = $candidatos->count('aluno_id');

				// Processar fornecedores
				$fornecedores = [];
				foreach ($campanha->notas_fiscais as $nota) {
					if (is_null($nota->nota)) continue;

					$fornecedor = $nota->fornecedor;

					$fornecedor->leads = 0;
					$fornecedor->inscritos = 0;
					$fornecedor->matriculados = 0;
					$fornecedor->candidatos = 0;
					$fornecedor->gastos = 0;

					$fornecedores[$fornecedor->id] = $fornecedor;
				}

				// Processar mídias
				$midias = $campanha->midias->keyBy('id')->transform(function ($midia) use ($campanha) {
					$midia->leads = 0;
					$midia->inscritos = 0;
					$midia->matriculados = 0;
					$midia->candidatos = 0;
					$midia->gastos = 0;

					return $midia;
				});

				// Mídia/Fornecedor -> gastos
				foreach ($campanha->notas_fiscais as $nota) {
					if (is_null($nota->nota)) continue;

					$midia = $nota->global_relation('midia')->global_relation('tipo');

					// Caso a mídia não esteja na campanha, iremos adicionar pelo propósito de RELATÓRIOS!
					if (!isset($midias[$midia->id])) {
						$midia->leads = 0;
						$midia->inscritos = 0;
						$midia->matriculados = 0;
						$midia->candidatos = 0;
						$midia->gastos = 0;
						$midias[$midia->id] = $midia;
					}

					// Validações

					try { $fornecedores[$nota->fornecedor->id]->gastos += $nota->valor; }
					catch (\Exception $e) { }

					try { $midias[$midia->id]->gastos += $nota->valor; }
					catch (\Exception $e) { }
				}

				// Mídia -> leads
				foreach ($leads->cursor() as $lead) {
					try { $midias[$lead->midia_id]->leads++; }
					catch (\Exception $e) { }
				}

				// Mídia -> inscritos
				foreach ($inscritos->cursor() as $lead) {
					try { $midias[$lead->midia_id]->inscritos++; }
					catch (\Exception $e) { }
				}

				// Mídia -> matriculados
				foreach ($matriculados->cursor() as $lead) {
					try { $midias[$lead->midia_id]->matriculados++; }
					catch (\Exception $e) { }
				}

				// Mídia -> candidatos
				foreach ($candidatos->cursor() as $lead) {
					try { $midias[$lead->midia_id]->candidatos++; }
					catch (\Exception $e) { }
				}

				$campanha = $campanha->toArray();
				$campanha['fornecedores'] = $fornecedores;
				$campanha['midias'] = $midias;

				$campanhas[] = $campanha;
			}

			return $campanhas;
		});
	});
});