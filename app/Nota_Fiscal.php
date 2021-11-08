<?php

namespace App;

use App\SimpleModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nota_Fiscal extends SimpleModel
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
    //
    protected $table = 'notas_fiscais';

    protected $fillable = ['numero', 'data', 'vencimento', 'descricao', 'valor', 'fornecedor_id', 'somar_filhas'];

    public function fornecedor() { return $this->belongsTo(Fornecedor::class); }

    public function campanhas() { return $this->hasMany(Nota_Fiscal_Campanha::class, 'nota_id'); }

    public function getTodasCampanhasAttribute() {
        $campanhas = [];

        if (!is_null($this->_nota_parent))
            $this->_nivel = $this->_nota_parent->_nivel + 1;
        else 
            $this->_nivel = 0;

        foreach ($this->campanhas()->cursor() as $campanha) {
            $campanha->_nota = $this;
            $campanha->_nivel = $this->_nivel;
            $campanhas[] = $campanha;
        }

        foreach ($this->notas_relacionadas()->cursor() as $nota) {
            $nota->_nota_parent = $this;
            $campanhas = array_merge($campanhas, $nota->todas_campanhas);
        }

        return $campanhas;
    }

    // Retorna valor total das campanhas
    public function getValorCampanhasAttribute() {
        $valor = 0.0;
        $ac = $this->todas_campanhas;
        foreach ($ac as $campanha) {
            $valor += $campanha->valor;
        }
        return $valor;
    }
    public function getValorRestanteAttribute() { return max(0, $this->valor_somado - $this->valor_campanhas); }
    public function getPorcentagemCampanhasAttribute() { return (100 * $this->valor_campanhas / $this->valor_somado); }
    public function getPorcentagemRestanteAttribute() { return max(0, 100 - $this->porcentagem_campanhas); }

    // Relacionada por...
    public function nota_relacionada() { return $this->belongsToMany(Nota_Fiscal::class, 'notas_fiscais_relacionadas', 'filha_id', 'nota_id'); }

    // Relacionadas a esta...
    public function notas_relacionadas() { return $this->belongsToMany(Nota_Fiscal::class, 'notas_fiscais_relacionadas', 'nota_id', 'filha_id'); }

    public function setValorAttribute($value) { $this->attributes['valor'] = str_replace(',', '.', $value); }

    // Retorna o valor das notas filhas (relacionadas)
    public function getValorRelacionadasAttribute() {
        $valor = 0.0;
        foreach ($this->notas_relacionadas()->cursor() as $nota) {
            $valor += $nota->valor;
        }
        return $valor;
    }

    public function getSomarRelacionadasAttribute() { return $this->attributes['somar_filhas']; }
    public function setSomarRelacionadasAttribute($value) {$this->attributes['somar_filhas'] = $value; }

    // Retorna valor descontado quando 'somar_filhas' for 'true'
    public function getValorDescontadoAttribute () {
        $descontar = 0.0;
        if ($this->somar_filhas)
            $descontar += $this->valor_relacionadas;
        return $this->valor - $descontar;
    }

    // Retorna valor somado quando 'somar_filhas' for 'true'
    public function getValorSomadoAttribute () {
        $somar = 0.0;
        if ($this->somar_filhas)
            $somar += $this->valor_relacionadas;
        return $this->valor + $somar;
    }
}
