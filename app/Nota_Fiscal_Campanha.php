<?php

namespace App;

use App\SimpleModel;

class Nota_Fiscal_Campanha extends SimpleModel
{
    //
    protected $table = 'notas_fiscais_campanhas';

    protected $fillable = ['nota_id', 'campanha_id', 'midia_id', 'porcentagem'];

    /*public function getIdAttribute() {
        return implode('-', [$this->nota_id, $this->campanha_id, $this->midia_id]);
    }

    public static function find($id) {
        $id = explode('-', $id);
        $nota = $id[0];
        $campanha = $id[1];
        $midia = $id[2];

        return static::where('nota_id', $nota)->where('campanha_id', $campanha)->where('midia_id', $midia)->first();
    }*/

    public function nota() { return $this->belongsTo(Nota_Fiscal::class, 'nota_id'); }

    public function campanha() { return $this->belongsTo(Campanha::class); }

    public function midia() { return $this->belongsTo(Midia::class); }

    public function getPorcentagemAttribute() { return $this->attributes['porcentagem']; }

    public function porcentagem_relativa ($total) {
        if ($total == 0 || is_null($this->nota))
            return 0;

        return $this->porcentagem * ($this->nota->valor / $total);
    }

    public function getFornecedorAttribute() { if (is_null($this->nota)) return null; return $this->nota->fornecedor; }

    public function setPorcentagemAttribute($value) { preg_match('/^\d+(\.\d+)*$/', $value, $value); if(!empty($value)) $this->attributes['porcentagem'] = $value[0]; }

    public function getValorAttribute() {
        if (is_null($this->nota) || !is_null($this->nota->deleted_at))
            return 0;

        return $this->nota->valor * ($this->porcentagem / 100);
    }
}
