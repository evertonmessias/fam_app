<?php

namespace App\Integracoes\Legacy;

use App\Aluno;
use App\CPF;
use App\Curso;
use App\Lead;
use App\Lead_Status;

use Carbon\Carbon;

// DUMMY
if (!function_exists('_')) {
	function _ ($data) { return $data; }
}

class Migracao_Base extends \App\SimpleModel {
	protected $table = 'candidatos_base';

    protected $orderBy = 'dataalteracao';
    protected $orderDirection = 'DESC';

    public static function rodar_migracoes () {
		_ ('Rodando conversoes CRM -> APP...');

		set_time_limit(300);
		set_time_limit(600);

		_ ('Importando migracoes de base...');

		$base_leads = Migracao_Base::ordered()->get();

		_ ('Carregando dados do app...');

		$alunos = Aluno::all()->keyBy('cpf');
		$cursos = Curso::all()->keyBy('codigo');
		$base_status = Lead_Status::all()->keyBy('base_id');

		_ ('Convertendo do CRM...');

		// Loop principal

		foreach ($base_leads as $base_lead) {

			// CPF do Lead

			if (!CPF::validate($base_lead->cpf))
				continue;

			$cpf = new CPF($base_lead->cpf);

			// Pegamos o lead da base
			$lead = Lead::with('aluno', 'status', 'historico')->find($base_lead->lead_id);

			// Aqui pré-formatamos tudo

			if (empty($base_lead->lead_id) || is_null($lead) || ((new CPF($lead->aluno->cpf))->numeric() != $cpf->numeric())) {

				// Precisaremos de um aluno cadastrado, senão ignore
				if (!isset($alunos[$cpf->formatted()]))
					continue;

				$aluno = $alunos[$cpf->formatted()];

				// Caso não inclua o ID, iremos pegar o último da pessoa daquele curso, mas causará menos precisão ¯\_(ツ)_/¯
				$lead = $aluno->leads()->with('aluno', 'status', 'historico', 'curso')->get()->filter(function ($lead) use ($base_lead) {
					if ($lead->curso->codigo == $base_lead->curso)
						return true;
					return false;
				})->first();
			} else {
				// Isso só irá acontecer se o lead for válido
				$aluno = $lead->aluno;
			}

			// Validar se o lead e o aluno realmente existem
			if (is_null($lead) || is_null($aluno))
				continue;

			// Validar CPF pela base de lead e do lead
			if ($cpf->formatted() != $aluno->cpf)
				continue;

			// Identificar o status de lead para qual vamos converter
			$base = $base_status[$base_lead->base];

			// Data da alteração
			$data_alterou = Carbon::createFromTimestamp(strtotime($base_lead->dataalteracao));

			// Testa se a base de leads do CRM está desatualizada, em casos raros onde não há último update, considerar a base do CRM como mais atualizada
			$desatualizado = is_null($lead->historico->last()) ? false : ($lead->historico->last()->at >= $data_alterou);

			// Pular leads que já estão na base indicada ou que estão desatualizados
			if ($desatualizado || $base->codigo == $lead->status->codigo) {
				_ ('Lead [' . $lead->id . '] ignorado ou desatualizado ' . $base->base_id . ' [' . $base->codigo . ']');
				continue;
			}

			// Realizar a conversão
			$lead->converter($base, 'Conversão via CRM', 'Lead convertido via CRM', null, $data_alterou);

			// Log
			_ ('Lead [' . $lead->id . '] convertido para base ' . $base->base_id . ' [' . $base->codigo . ']');
    	}
    }
}
