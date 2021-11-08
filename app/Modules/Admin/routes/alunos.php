<?php

use App\Aluno;
use App\Lead;
use App\Lead_Status;

use Illuminate\Http\Request;

use Carbon\Carbon;

$middle = function (Request $req, $next) {
	return $next($req);
};

// HELPER

if (!function_exists('array_to_xml')) {
	function array_to_xml( $data, &$xml_data ) {
	    foreach( $data as $key => $value ) {
	        if( is_numeric($key) ){
	            $key = 'item'.$key; //dealing with <0/>..<n/> issues
	        }
	        if( is_array($value) ) {
	            $subnode = $xml_data->addChild($key);
	            array_to_xml($value, $subnode);
	        } else {
	            $xml_data->addChild("$key",htmlspecialchars("$value"));
	        }
	     }
	}
}

Route::group(['middleware' => $middle], function () {
	Route::get('/', function () {
		$alunos = Aluno::orderBy('id', 'ASC');		

		return view ('Admin::Alunos.index', [
			'alunos' => $alunos
		]);		
		/*
		$dados = [
			'c_alunos' => $alunos
		];
		return view('Admin::Alunos.index', $dados);*/
	});
	Route::get('/{id}/', function ($id) {
		$aluno = Aluno::with('leads', 'leads.curso', 'leads.historico')->find($id);

		$leads_filtered = [];

		foreach ($aluno->leads as $lead) {
			foreach ($lead->historico as $entry) {
				$entry->curso = $lead->curso;
				array_push($leads_filtered, $entry);
			}
		}

		$dados = [
			'aluno' => $aluno,
			'leads' => collect($leads_filtered)->sortByDesc('at')
		];

		return view('Admin::Alunos.view', $dados);
	});

	// Editar Aluno
	Route::get('/{id}/edit', function ($id) {
		$aluno = Aluno::find($id);

		$dados = [
			'aluno' => $aluno
		];

		return view('Admin::Alunos.edit', $dados);
	});
	Route::post('/{id}/edit', function (Request $req, $id) {
		$aluno = Aluno::find($id);

		foreach ($req->all() as $k => $v) {
			if ($k == '_token') continue;

			if ($k == 'datanascimento') {
				$v = explode('-', $v);
				$v = array_reverse($v);
				$v = implode('/', $v);
			}

			$aluno->{$k} = $v;
		}

		$aluno->save();

		return redirect('/alunos/' . $id . '/');
	});

	// Editar Lead
	Route::get('/{id}/lead/{lead_id}', function ($id, $lead_id) {
		$lead = Lead::find($lead_id);

		// Validar se Lead é mesmo deste Aluno
		if (is_null($lead) || $lead->aluno_id != $id)
			return redirect('/alunos');

		$aluno = Aluno::find($id);

		$dados = [
			'aluno' => $aluno,
			'lead' => $lead,
			'lead_status' => Lead_Status::where('base_id', '>', 0)->get()->sortBy('base_id')
		];

		return view('Admin::Alunos.edit-lead', $dados);
	});
	Route::post('/{id}/lead/{lead_id}', function (Request $req, $id, $lead_id) {
		$lead = Lead::find($lead_id);

		// Validar se Lead é mesmo deste Aluno
		if (is_null($lead) || $lead->aluno_id != $id)
			return redirect('/alunos');

		// Buscar aluno da request
		$aluno = Aluno::find($id);

		// Obter dados de opções de curso
		$opcao_curso_principal = $req->input('opcao_curso');
		$opcoes_curso = $req->input('opcoes_curso');

		// Por padrão, a opção de curso principal é sempre a primeira
		$lead->curso_id = $opcoes_curso[0];

		// Setar valores de opções de curso
		foreach ($opcoes_curso as $k => $opcao_id) {
			// Limitar a 3
			if ($k > 2)
				break;

			// ID da Opção ou NULL
			if (empty($opcao_id))
				$opcao_id = null;

			// Setar opção no Lead
			$lead->{'opcao_curso_' . ($k + 1)} = $opcao_id;

			// Caso tenha marcado outra opção de curso principal, mudar aqui
			if ($k == ($opcao_curso_principal - 1) && !is_null($opcao_id))
				$lead->curso_id = $opcao_id;
		}

		// Extensão da mensagem de status (histórico)
		$msg_ext = ' [' . Auth::user()->name . ']';
		$converter = false;

		// Data da Prova
		if (!is_null($req->input('data_prova'))) {
			// Trigger de conversão para inscrito/reagendamento
			if ($lead->prova->data_id != $req->input('data_prova'))
				$converter = true;

			$lead->prova->data()->associate($req->input('data_prova'));
			$lead->prova->touch();
			$lead->prova->save();

			// Extender texto
			if ($converter)
				$msg_ext .= ' [DATA: ' . $lead->prova->data->hora()->format('d/m/Y H:i') . ']';
		}

		// Esperar um segundo para evitar bugs onde o histórico fica fora de ordem
		sleep(1);

		// Validar se precisamos converter o lead para INSCRITO
		switch ($lead->status->base_id) {
			case 2: // Ausente
			case 5: // Reprovado
			if ($converter) {
				$lead->converter('INSC', 'Reagendamento do Lead via BI ' . $msg_ext);
				break;
			}
			default: // Em outros casos, apenas salvar no histórico
				$lead->criar_historico('Dados do Lead Alterados via BI' . $msg_ext);
				break;
		}

		// Pausar para não bagunçar histórico
		sleep(1);

		// Salvar Lead
		$lead->touch();
		$lead->save();

		// Conversão de bases
		$nova_base = $req->input('converter_lead');
		if (!empty($nova_base) && $nova_base != '0') {
			$nova_base = Lead_Status::find($nova_base);

			// Checar se podemos resetar para base 0
			$baseZeroAllowed = Auth::user()->can('dev*') || $lead->campanha->id == 6;

			// Aqui validamos se a base realmente existe e bloqueamos migração para base zero (LEAD)
			if (!is_null($nova_base) && ($baseZeroAllowed || $nova_base->base_id != 0)) {
				$lead->converter($nova_base, 'Conversão de Base via BI' . $msg_ext);
			}
		}

		return redirect('/alunos/' . $id . '/');
	});


	Route::get('/export/xml', function () {
		$alunos = Aluno::with(
			'leads',
			'leads.prova',
			'leads.historico'
			)->get();

		$xml = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
		array_to_xml ($alunos->toArray(), $xml);

		return $xml->asXML();
	});
});