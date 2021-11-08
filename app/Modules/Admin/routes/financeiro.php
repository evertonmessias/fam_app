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

	/* Fornecedores */
	Route::group(['prefix' => 'fornecedores'], function () {
		Route::get('/', function () {
			$fornecedores = Fornecedor::all();

			return view ('Admin::Financeiro.Fornecedores.index', [
				'fornecedores' => $fornecedores
			]);
		});
		Route::get('/{id}/', function ($id) {
			$fornecedor = Fornecedor::find($id);

			return view ('Admin::Financeiro.Fornecedores.view', [
				'fornecedor' => $fornecedor
			]);
		});
		Route::get('/{id}/edit', function ($id) {
			$fornecedor = Fornecedor::find($id);

			return view ('Admin::Financeiro.Fornecedores.edit', [
				'fornecedor' => $fornecedor,
				'midias' => Midia_Tipo::all()
			]);
		});

		Route::post('/{id}/edit', function (Request $req, $id) {
			$fornecedor = Fornecedor::find($id);

			// Mídias - Atualizar
			$midias = $fornecedor->midias;
			$midias_manter = [];
			$midias_novas = $req->midias;
			foreach($midias_novas as $k => $tipo) {
				$tipo = Midia_Tipo::find($tipo);
				$midia = $fornecedor->midia($tipo);

				if (is_null($midia)) {
					// Criar tipo
					$midia = new Midia();
					$midia->fornecedor()->associate($fornecedor);
					$midia->tipo()->associate($tipo);
					$midia->nome = $tipo->nome;
				} else {
					$midias_manter[] = $midia;
					if (isset($req->midia_nome[$midia->id]))
						$midia->nome = $req->midia_nome[$midia->id];
				}
				$midia->save();

				$midias_novas[$k] = $midia;
			}

			// Remover mídias desmarcadas
			$midias_remover = Helpers::filter_out($midias, $midias_manter);
			foreach($midias_remover as $midia) { $midia->delete(); }

			$fornecedor->razao_social = $req->razao_social;
			$fornecedor->nome_fantasia = $req->nome_fantasia;
			$fornecedor->c_nome = $req->c_nome;
			$fornecedor->c_cargo = $req->c_cargo;
			$fornecedor->c_gerente = $req->c_gerente;
			$fornecedor->fone = $req->fone;
			$fornecedor->fone_alt = $req->fone_alt;
			$fornecedor->email = $req->email;
			$fornecedor->email_alt = $req->email_alt;

			$fornecedor->save();

			return redirect('/financeiro/fornecedores/' . $fornecedor->id . '/edit');
		});

		Route::post('/new', function (Request $req) {
			$fornecedor = new Fornecedor();

			$fornecedor->cnpj = $req->cnpj;
			$fornecedor->razao_social = $req->razao_social;
			$fornecedor->nome_fantasia = $req->nome_fantasia;
			$fornecedor->c_nome = $req->c_nome;
			$fornecedor->c_cargo = $req->c_cargo;
			$fornecedor->c_gerente = $req->c_gerente;
			$fornecedor->fone = $req->fone;
			$fornecedor->fone_alt = $req->fone_alt;
			$fornecedor->email = $req->email;
			$fornecedor->email_alt = $req->email_alt;

			$fornecedor->save();

			return redirect('/financeiro/fornecedores/' . $fornecedor->id . '/');
		});
	});

	/* Mídias */
	Route::group(['prefix' => 'midias'], function () {
		Route::get('/', function () {
			$midias = Midia_Tipo::orderBy('categoria_id', 'asc')->get();

			return view ('Admin::Financeiro.Midias.index', [
				'midias' => $midias
			]);
		});
		Route::get('/{id}/', function ($id) {
			$midia = Midia_Tipo ::find($id);

			return view ('Admin::Financeiro.Midias.view', [
				'midia' => $midia
			]);
		});
		Route::get('/{id}/edit', function ($id) {
			$midia = Midia_Tipo::find($id);

			return view ('Admin::Financeiro.Midias.edit', [
				'midia' => $midia,
				'midias' => Midia_Tipo::all()
			]);
		});

		Route::post('/{id}/edit', function (Request $req, $id) {
			$midia = Midia_Tipo::find($id);
			$categoria = Midia_Tipo::find($req->categoria);

			$midia->nome = $req->nome;
			$midia->codigo = $req->codigo;
			$midia->categoria()->dissociate();

			if (!is_null($categoria))
				$midia->categoria()->associate($categoria);

			$midia->save();

			return redirect('/financeiro/midias/' . $midia->id . '/');
		});

		Route::post('/new', function (Request $req) {
			$tipo = new Midia_Tipo();
			$categoria = Midia_Tipo::find($req->categoria);

			$tipo->nome = $req->nome;
			$tipo->codigo = $req->codigo;

			if (!is_null($categoria))
				$tipo->categoria()->associate($categoria);

			$tipo->save();

			return redirect('/financeiro/midias/' . $tipo->id . '/');
		});
	});

	/* Notas Fiscais */
	Route::group(['prefix' => 'notas'], function () {
		Route::get('/', function () {
			$notas = Nota_Fiscal::all();
			$mesAnterior = Carbon::createFromFormat('Y-m-d', date('Y-m-') . '01')->subMonth(1);
			$mesAtual = Carbon::createFromFormat('Y-m-d', date('Y-m-') . '01');
			$mesNext = Carbon::createFromFormat('Y-m-d', date('Y-m-') . '01')->addMonth(1);
			
			$notasMes = $notas->filter(function ($nota) use ($mesAtual, $mesNext) {
				$vencimento = Carbon::createFromFormat('Y-m-d', $nota->vencimento);

				if ($mesAtual->timestamp <= $vencimento->timestamp && $vencimento->timestamp < $mesNext->timestamp)
					return true;
				return false;
			});

			$notasMesAnterior = $notas->filter(function ($nota) use ($mesAtual, $mesAnterior) {
				$vencimento = Carbon::createFromFormat('Y-m-d', $nota->vencimento);

				if ($mesAnterior->timestamp <= $vencimento->timestamp && $vencimento->timestamp < $mesAtual->timestamp)
					return true;
				return false;
			});

			return view ('Admin::Financeiro.Notas.index', [
				'notas' => $notas,
				'notasMes' => $notasMes,
				'notasMesAnterior' => $notasMesAnterior,
				'fornecedores' => Fornecedor::all()
			]);
		});
		Route::get('/{id}/', function ($id) {
			$nota = Nota_Fiscal::find($id);

			return view ('Admin::Financeiro.Notas.view', [
				'nota' => $nota
			]);
		});
		Route::get('/{id}/edit', function ($id) {
			$nota = Nota_Fiscal::find($id);
			$notas = Nota_Fiscal::all();
			$notas = Helpers::filter_out($notas, [$nota]); // Tirar nota atual da lista de notas (evitar loop infinito)
			$notas = Helpers::filter_out($notas, $nota->notas_relacionadas); // Filtrar as que já estão relacionadas

			return view ('Admin::Financeiro.Notas.edit', [
				'nota' => $nota,
				'notas' => $notas,
				'campanhas' => Campanha::all(),
				'midias' => $nota->fornecedor->midias
			]);
		});
		Route::get('/{id}/remove', function ($id) {
			$nota = Nota_Fiscal::find($id);
			$nota->delete();
			
			return redirect('/financeiro/notas/');
		});

		Route::post('/{id}/edit', function (Request $req, $id) {
			$nota = Nota_Fiscal::find($id);

			// Descrição
			$nota->descricao = $req->descricao;

			// Somar relacionadas
			$nota->somar_relacionadas = isset($req->somar_relacionadas);

			// Relacionadas a manter
			$relacionadas = isset($req->notas_relacionadas) ? $req->notas_relacionadas : [];

			// Relacionadas novas
			if (isset($req->relacionadas_new) && !empty($req->relacionadas_new))
				$relacionadas = array_merge($relacionadas, [$req->relacionadas_new]);

			// Sincroniza notas relacionadas
			$nota->notas_relacionadas()->sync($relacionadas);

			// Campanhas a manter
			$campanhas = isset($req->campanhas) ? $req->campanhas : [];
			foreach ($campanhas as $k => $id) {
				$campanhas[$k] = Nota_Fiscal_Campanha::find($id);
			}

			// Separar campanhas a remover individualmente
			$campanhas_limpar = Helpers::filter_out($nota->campanhas, $campanhas);

			// Remover campanhas
			foreach ($campanhas_limpar as $campanha) {
				$campanha->delete();
			}

			try {
				if (isset($req->campanha_new) && !empty($req->campanha_new['campanha'])) {
					// Criar link de campanha
					$campanha_new = new Nota_Fiscal_Campanha();
					$campanha_new->nota()->associate($nota);
					$campanha_new->campanha()->associate(Campanha::find($req->campanha_new['campanha']));
					$campanha_new->midia()->associate(Midia::find($req->campanha_new['midia']));
					$campanha_new->porcentagem = $req->campanha_new['porcentagem'];
					$campanha_new->save();
				}
			} catch (Exception $e) { }

			$nota->save();

			return redirect('/financeiro/notas/' . $nota->id . '/edit');
		});

		Route::post('/new', function (Request $req) {
			$nota = new Nota_Fiscal();

			$nota->numero = $req->numero;
			$nota->fornecedor()->associate(Fornecedor::find($req->fornecedor));
			$nota->descricao = '';
			$nota->data = Helpers::data_internacional($req->data);
			$nota->vencimento = Helpers::data_internacional($req->vencimento);
			$nota->valor = $req->valor;

			$nota->save();

			return redirect('/financeiro/notas/' . $nota->id . '/edit');
		});
	});

	/* Cálculo de ROI */
	Route::group(['prefix' => 'roi'], function () {
		include ('financeiro-roi.php');
	});

	// Dashboard Financeira
	Route::group(['prefix' => 'dashboard'], function () {
		include ('financeiro-dashboard.php');
	});

	Route::get('/', function () {
		return redirect('/provas/datas/');
	});
});