<?php

namespace App;

use Carbon\Carbon;

class Prova extends Model {

	public function raw ($attr) { return isset($this->attributes[$attr]) ? $this->attributes[$attr] : null; }
	public function aluno() { return $this->belongsTo(Aluno::class); }
	public function campanha() { return $this->belongsTo(Campanha::class); }
	public function curso() { return $this->belongsTo(Curso::class); }
	public function lead() { return $this->hasOne(Lead::class); }
	public function leads() { return $this->hasMany(Lead::class); }
	public function data() { return $this->belongsTo(Prova_Data::class); }
	public function getLocalAttribute() { return $this->data->local; }
	public function isResultsAvailable() {
		// Validar se está na base 2, 3 ou 5 (Ausente, Aprovado, Reprovado)
		// Validar se o campo aprovado está preenchido
		// Validar se o campo nota está preenchido
		if ($this->lead->status->base_id == 2 || $this->lead->status->base_id == 3 || $this->lead->status->base_id == 5) return true;
		if ($this->attributes['aprovado'] == 1) return true;
		if ($this->attributes['participou'] == 1) return true;
		if (!is_null($this->attributes['nota'])) return true;
		return false;
	}
	public function getParticipouAttribute () {
		// Lógica para validar se o candidato participou. Verificar se está na base 2, que indica ausente.
		// Uma base menor (1) seria inscrito, que utiliza o atributo participou, uma base maior entra no caso de matriculado, boleto vencido, etc, ou seja, participou.
		if ($this->lead->status_id == 'AUSENTE') return 0;
		if ($this->lead->status->base_id > 2) return 1;
		return $this->attributes['participou'];
	}
	public function getStatusAttribute() {
		if (Carbon::now() < $this->data->hora) return 'Ainda não realizada';
		if (!$this->dados_disponiveis) return 'Resultados indisponíveis';
		if (!$this->participou) return 'Faltou à prova';
		if (!$this->aprovado) return 'Reprovado';
		return 'Aprovado';
	}
	public function getAprovadoAttribute() {
		if ($this->lead->status->base_id == 5) return 0;
		if ($this->lead->status->base_id > 3) return 1;
		return $this->attributes['aprovado'];
	}
	public function getDadosForamAtualizadosAttribute() {
		return ($this->updated_at > $this->created_at);
	}
	public function getDadosDisponiveisAttribute() {
		return $this->isResultsAvailable();
		// return ($this->lead->status->base_id > 1) || (!is_null($this->nota) && $this->dados_foram_atualizados);
		// return !is_null($this->nota);
	}
	public function getStatusIdAttribute() {
		if (Carbon::now() < $this->data->hora) return 0;
		if (!$this->dados_disponiveis) return 1;
		if (!$this->participou) return 2;
		if (!$this->aprovado) return 3;
		return 4;
	}
	public function getClassificacaoGeralAttribute() {
		$result = $this->campanha->provas()->where('id', $this->id)->first();
		return $result->getRowNumber('nota', 'desc')->first();
	}
	public function getClassificacaoCursoAttribute() {
		$result = $this->curso->provas()->where('id', $this->id)->first();
		return $result->getRowNumber('nota', 'desc')->first();
	}
	public function save (array $options = array()) {

		if (empty($this->local_id))
			$this->local_id = $this->local->id;

        parent::save($options); // Calls Default Save
    }
}