<?php

namespace App;

use App\System\Event;

// use RDStationAPI;

class RDStation_Univestibular {
	private $_api = null;

	private $bases_lead = array(
		0 => 'Leads',
		1 => 'Inscritos',
		2 => 'Ausentes',
		3 => 'Aprovados Não Pré-Matriculados',
		4 => 'Aprovados Pré-Matriculados',
		5 => 'Reprovados',
		6 => 'Matriculados',
		7 => 'Boletos Vencidos',
		8 => 'Vestibulandos Não-Aluno',
		9 => 'Alunos Evadidos',
		10 => 'Prospects'
	);

	public static function token ($token = null, $token_privado = null) {
		if (!is_null($token))
			Settings::set('rd_token', $token);

		if (!is_null($token_privado))
			Settings::set('rd_token_privado', $token_privado);

		return [
			'token' => Settings::get('rd_token', env('RD_TOKEN')),
			'token_privado' => Settings::get('rd_token_privado', env('RD_TOKEN_PRIVADO'))
		];
	}

	public function __construct () {
		$token = static::token();

		$this->TOKEN = $token['token'];
		$this->TOKEN_PRIVADO = $token['token_privado'];
		$this->_api = new RDStationAPI ($this->TOKEN_PRIVADO, $this->TOKEN);
	}

	public function gera_url ($v, $endpoint) {
		return 'https://www.rdstation.com.br/api/' . $v . '/' . $endpoint;
	}

	public function request ($url, $method = 'POST', $dados = array()) {
	    $newData['auth_token'] = $this->privateToken;
	}

	public function api () { return $this->_api; }

	public function converter ($email, $lead_status, $dados = array()) {

		$event = Event::register('rd-station', 'Conversão no RD Station');
		$event->meta('email', $email);
		
		return $event->run(function ($event) use ($email, $lead_status, $dados) {
			if (!is_object($lead_status))
				$lead_status = Lead_Status::find($lead_status);

			// Testa de status não é null

			if (is_null($lead_status))
				throw new \Exception ('Status do lead inválido.');

			// Identifica conversão e base de leads baseados no status do lead

			$dados['base'] = $lead_status->base_id;

			$event->meta('base', $lead_status->base_id);

			// Faz request

			return $this->converter_manual($email, $lead_status->conversao, $dados);
		});
	}

	public function converter_manual ($email, $identificador, $dados = array()) {

		// Identifica conversão e base de leads baseados no status do lead

		$dados['identificador'] = $identificador;

		// Faz request

		return $this->api()->sendNewLead($email, $dados);
	}
}