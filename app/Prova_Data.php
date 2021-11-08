<?php

namespace App;

use Carbon\Carbon;

class Prova_Data extends Model {

	protected $table = 'provas_datas';
	protected $fillable = ['id', 'maximo'];

	public function getLotadoAttribute() { return !$this->tem_vagas; }
	public function getVagasOcupadasAttribute() { return $this->provas()->count(); }
	public function getVagasAttribute() { return max(0, $this->maximo - $this->vagas_ocupadas); }
	public function getTemVagasAttribute() { return ($this->vagas > 0); }
	public function getExibirAttribute() {
		if (Carbon::tomorrow() >= $this->hora()) return false;
		if (!$this->disponivel) return false;
		if (!$this->tem_vagas) return false;
		return true;
	}
	public function getStatusAttribute() {
        if ($this->hora_final()) {
            if (Carbon::now() > $this->hora_final()) return 'Realizada';
        } else {
            if (Carbon::now() > $this->hora()) return 'Realizada';
        }
		if (!$this->disponivel) return 'Indisponível';
		if (!$this->tem_vagas) return 'Lotado';
		return 'Disponível';
	}
	public function getAproveitamentoAttribute () { return number_format(100 * (($this->maximo == 0) ? 1 : ($this->vagas_ocupadas / $this->maximo)), 2); }
	public function local() { return $this->belongsTo(Prova_Local::class); } 
	public function provas() { return $this->hasMany(Prova::class, 'data_id'); }
	public function hora() { return Carbon::createFromTimestamp(strtotime($this->hora)); }
	public function hora_final() {
        if ($this->hora_final) {
            return Carbon::createFromTimestamp(strtotime($this->hora_final));
        } else {
            return false;
        }
    }
}