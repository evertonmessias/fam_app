<?php

use App\Campanha;
use App\Campanha_Tag;
use App\Curso;
use App\Unidade;

use Illuminate\Http\Request;

use Carbon\Carbon;

$middle = function (Request $req, $next) {
	return $next($req);
};

Route::group(['middleware' => $middle], function () { 
	Route::get('/', function () {
		$campanhas = Campanha::orderBy('inicio', 'desc')->get();

		$ativas = [];
		$outras = [];

		foreach ($campanhas as $campanha) {

			$c_matriculados = $campanha->matriculados()->count();
			$c_inscritos = $campanha->inscritos()->count() + $c_matriculados;
			$c_leads = $campanha->leads_total()->count();

			// Calcular custos
			$custo = [
				'lead' => $c_leads > 0 ? ($campanha->budget_consumido / $c_leads) : 0,
				'inscrito' => $c_inscritos > 0 ? ($campanha->budget_consumido / $c_inscritos) : 0,
				'matricula' => $c_matriculados > 0 ? ($campanha->budget_consumido / $c_matriculados) : 0
			];

			// Criar projeções
			$projecoes = ['matriculados' => 0, 'inscritos' => 0, 'leads' => 0, 'cursos' => 0];
			$meses = 6;

			foreach($campanha->cursos()->cursor() as $curso) {
				$projecoes['matriculados'] += $campanha->matriculados_total()->porCurso($curso->id)->count() * $meses * $curso->valor;
				$projecoes['inscritos'] += $campanha->inscritos_total()->porCurso($curso->id)->count() * $meses * $curso->valor;
				$projecoes['leads'] += $campanha->leads_total()->porCurso($curso->id)->count() * $meses * $curso->valor;
				$projecoes['cursos'] += $curso->valor * $curso->vagas * $meses;
			}

			$campanha->projecoes = $projecoes;
			$campanha->custo = $custo;

			// Calcular se é ou não ativa
			if ($campanha->is_ativa)
				$ativas[] = $campanha;
			else
				$outras[] = $campanha;
		}

		$dados = [
			'ativas' => $ativas,
			'outras' => $outras,
			'campanhas' => $campanhas
		];

		return view('Admin::Campanhas.index', $dados);
	});

	Route::get('/{campanha}/', function ($campanha_id) {

		$campanha = Campanha::find($campanha_id);

		$inicio = new Carbon($campanha->inicio);
		$fim = new Carbon($campanha->fim);
		$agora = Carbon::now();

		$dias_total = $inicio->diffInSeconds($fim);
		$dias_corridos = $inicio->diffInSeconds(Carbon::now());

		$dados = [
			'campanha' => $campanha,
			'campanha_progresso' => max(0, min(100, round(100 * $dias_corridos / $dias_total)))
		];

		return view('Admin::Campanhas.view', $dados);
	});
	Route::post('/new', function (Request $req) {

		$campanha = new Campanha([
			'nome' => $req->nome,
			'budget' => $req->budget,
			'inicio' => $req->data_inicio,
			'fim' => $req->data_fim
		]);
		$campanha->campos_personalizados = [];
		$campanha->save();

		// Inserir todos os cursos
		$campanha->cursos()->attach(Curso::all());

		return redirect('campanhas/' . $campanha->id . '/edit');
	});
	Route::get('/{campanha}/edit', function ($campanha_id) {

		$dados = [
			'campanha' => Campanha::find($campanha_id),
			'cursos' => Curso::ordered()->get(),
			'unidades' => Unidade::all(),
			'campanhas' => Campanha::all()
		];

		return view('Admin::Campanhas.edit', $dados);
	});
	Route::post('/{campanha}/edit', function (Request $req, $campanha_id) {
		$campanha = Campanha::find($campanha_id);

		// Campos Personalizados

		$campos_personalizados = $campanha->campos_personalizados;
		if (isset($req->campos_personalizados)) {
			$campos = $req->campos_personalizados;

			if(isset($campos['__new__'])) {
				$novo = $campos['__new__'];
				$novo['valores'] = [];

				// Criar novo campo
				$campos[$novo['nome']] = $novo;

				unset($campos['__new__']);
			}

			foreach ($campos as $nome => $campo) {
				if (empty($campo['label'])) {
					unset($campos[$nome]);
					continue;
				}

				if (isset($campo['nome']))
					$nome = $campo['nome'];

				if (empty($nome)) continue;

				$campo['valores'] = json_decode($campo['valores'], true);

				$campos[$nome] = array_merge($campos[$nome], $campo);
			}

			$campanha->campos_personalizados = $campos;
		}

		// Textos

		if (isset($req->textos)) {
			$campanha->textos = $req->textos;
		}

		// Midias

		$_midias = array_reverse($req->midias); // Por algum motivo estavam vindo ao contrário, isso parece corrigir o bug (?)

		$campanha->midias()->sync($_midias);
		$midias = $campanha->midias()->withPivot('ordenar')->get();
		$rel = array_flip($_midias);

		foreach ($midias as $midia) {
			$pivot = $midia->pivot;
			$pivot->ordenar = $rel[$midia->id];
			$pivot->save();

			// dd($pivot, $midia, $rel);
		}

		// Tags

		$tags = isset($req->tags) ? $req->tags : [];
		foreach ($tags as $id => $tag_data) {
			// Ignorar se nova tag estiver vazia
			if ($id == 'new' && empty($tag_data['codigo'])) continue;

			// ID vs Nova
			if ($id == 'new')
				$tag = new Campanha_Tag();
			else
				$tag = Campanha_Tag::find($id);

			// Setar dados
			foreach ($tag_data as $k => $v) {
				$tag->{$k} = $v;
			}

			// Setar campanha e salvar
			$tag->campanha()->associate($campanha);
			$tag->save();

			// Deletar se tag estiver vazia
			if (empty($tag->codigo))
				$tag->delete();
		}

		// Campanha Parent

		$parent = isset($req->parent) ? $req->parent : null;
		$parent_obj = Campanha::find($parent);

		if (!is_null($parent_obj))
			$campanha->parent()->associate($parent_obj);

		// dd($req, $campanha);

		$cursos = [];
		if (isset($req->cursos))
			$cursos = $req->cursos;

		$unidades = [];
		if (isset($req->unidades))
			$unidades = $req->unidades;

		$campanha->inicio = $req->data_inicio;
		$campanha->fim = $req->data_fim;
		$campanha->budget = $req->budget;
		$campanha->relatorios = $req->relatorios;

		$campanha->nome = $req->nome;
		$campanha->cursos()->sync($cursos);
		$campanha->unidades()->sync($unidades);
		$campanha->save();

		return back();
	});

	// Exportações em CSV para Excel, etc
	Route::get('/{campanha}/leads/csv', function ($campanha) {

		$campanha = Campanha::find($campanha);

		$leads = $campanha->leads_total_unique();
		$leads = leads2listizer($leads);

		$data = [];

		// Columns
		$columns = array_keys($leads[0]);

		$output = fopen("php://output",'w') or die("Can't open php://output");

		// UTF-8
		fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

		// Nome do Arquivo
		$filename = str_replace(' ', '_', 'Leads ' . $campanha->nome . ' ' . date('Y-m-d H-i-s'));

		header('Content-Encoding: UTF-8');
    	header('Content-Type: text/csv; charset=UTF-8' );
		header("Content-Disposition:attachment;filename=" . $filename . ".csv"); 
		
		fputs( $output, "\xEF\xBB\xBF" );

		$separator = ',';

		// Separator
		
		fputcsv($output, $columns, $separator);
		foreach($leads as $data) {
			if (!is_null($data))
		    	fputcsv($output, $data, $separator);
		}

		fclose($output) or die("Can't close php://output");

		return;
	});

	// Exportações zipzop em CSV para Excel, etc
	Route::get('/{campanha}/leads/zipzop', function ($campanha) {

		$campanha = Campanha::find($campanha);

		$leads = $campanha->leads_total_unique();

		$zap = [];
		if (is_array($leads) || is_a($leads, 'Illuminate\Database\Eloquent\Collection')) {
			// Array
			foreach ($leads as $lead) {
				if (is_null($lead)) continue;
				$zap [] = $lead->export_zipzop();
			}
		} else {
			// Cursor
			foreach ($leads->cursor() as $lead) {
				if (is_null($lead)) continue;
				$zap [] = $lead->export_zipzop();
			}
		}

		$data = [];

		// Columns
		$columns = array_keys($zap[0]);

		$output = fopen("php://output",'w') or die("Can't open php://output");

		// UTF-8
		fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

		// Nome do Arquivo
		$filename = str_replace(' ', '_', 'Contatos WhatsApp ' . $campanha->nome . ' ' . date('Y-m-d H-i-s'));

		header("Content-Type:application/csv"); 
		header("Content-Disposition:attachment;filename=" . $filename . ".csv"); 

		$separator = ',';

		// Separator
		fprintf($output, "sep=$separator\r\n");

		fputcsv($output, $columns, $separator);
		foreach($zap as $data) {
			if (!is_null($data))
		    	fputcsv($output, $data, $separator);
		}

		fclose($output) or die("Can't close php://output");

		return;
	});
});