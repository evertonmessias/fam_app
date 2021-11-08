<?php

use App\Aluno;
use App\Campanha;
use App\Lead;
use App\Crm\Atendimento;

use Carbon\Carbon;

use Illuminate\Http\Request;

$atendimentos = function () {
	return Atendimento::with('aluno', 'historico', 'lead', 'lead.curso', 'lead.status', 'lead.prova', 'lead.prova.data', 'user');
};

$status = [
	'nao-tem-interesse' => [
		'label' => 'Não Tem Interesse'
	],
];

Route::get('/locais_provas/{campanha}', function ($campanha) use ($atendimentos) {
	$campanha = Campanha::find($campanha);
	$locais = $campanha->locais_provas;
	foreach ($locais as $k => $local) {
		$local->datas = $local->datas_provas_disponiveis;
		$locais[$k] = $local;
	}
	return $locais;
});

Route::get('/atendimentos', function () use ($atendimentos) {
	$atendimentos = $atendimentos()->get();
	return $atendimentos;
});

Route::get('/atendimento_start/{id}', function ($id) use ($atendimentos) {
	$atendimento = $atendimentos()->find($id);

	// Validar se já está sendo atendido
	if (!is_null($atendimento->user) && $atendimento->user->id != Auth::user()->id)
		return Response::json([
		    'error' => 'Outro usuário já está atendendo este candidato.'
		], 401);

	// Setar usuário atual como atendente
	$atendimento->user()->associate(Auth::user());
	$atendimento->save();

	return $atendimento;
});
Route::post('/atendimento_salvar', function (Request $req) use ($atendimentos) {
	$atendimento = $req->input('atendimento');
	$aluno = $req->input('atendimento.aluno');
	$lead = $req->input('atendimento.lead');

	// dd($aluno, $lead, $atendimento);

	// Aluno
	$_aluno = Aluno::find($aluno['id']);
	$attrs = array_keys($_aluno->getAttributes());
	foreach ($aluno as $k => $v) {
		if (empty($v) || is_null($v)) continue;
		if (in_array($k, $attrs))
			$_aluno->{$k} = $v;
	}
	$_aluno->save();

	// Lead
	$_lead = Lead::find($lead['id']);
	$attrs = array_keys($_lead->getAttributes());
	foreach ($lead as $k => $v) {
		if (empty($v) || is_null($v)) continue;
		if (in_array($k, $attrs))
			$_lead->{$k} = $v;
	}
	$_lead->save();

	// Prova
	$_prova = $_lead->prova;
	if (!is_null($_prova)) {
		$_prova->data()->associate($req->input('atendimento.lead.prova.data.id'));
		$_prova->save();
	}

	// Atendimento
	$_atendimento = $atendimentos()->find($atendimento['id']);

	try { $_atendimento->agendamento = Carbon::createFromFormat('Y-m-d?H:i', $req->input('atendimento.agendamento')); } catch (\Exception $e) { }

	$_atendimento->save();
	
	return $_atendimento;
});
Route::post('/atendimento_finalizar', function (Request $req) use ($atendimentos) {
	$atendimento = $req->input('atendimento');

	// Atendimento
	$_atendimento = $atendimentos()->find($atendimento['id']);
	$_atendimento->user()->associate(null);
	$_atendimento->save();

	return $_atendimento;
});