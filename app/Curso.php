<?php

namespace App;

use App\Campanha;

class Curso extends Model
{
    //
    protected $table = 'cursos';

    protected $orderBy = 'nome';
    protected $orderDirection = 'ASC';

    protected $fillable = ['codigo', 'nome', 'duracao', 'valor', 'grade'];
    
    public function provas () { return $this->hasMany(Prova::class); }

    public function periodos () {
    	return $this->hasMany(Periodo::class);
    }

    public function grade () {
    	return $this->belongsTo(Grade::class);
    }

    public function campanhas () {
        return $this->belongsToMany(Campanha::class);
    }

    public function unidades () {
        return $this->belongsToMany(Unidade::class);
    }

    public function turmas () {
    	return $this->hasMany(Turma::class);
    }

    public function matriculados_total ($campanha) { return (is_object($campanha) ? $campanha : Campanha::find($campanha))->matriculados_total_unique()->where('curso_id', $this->id); }
    public function matriculados () {
    	return $this->hasMany(Lead::class)->where('status_id', 'MATR');
    }

    public function inscritos_total ($campanha) { return (is_object($campanha) ? $campanha : Campanha::find($campanha))->inscritos_total_unique()->where('curso_id', $this->id); }
    public function inscritos () {
        return $this->hasMany(Lead::class)->where('status_id', 'INSC');
    }

    public function leads_total ($campanha) { return (is_object($campanha) ? $campanha : Campanha::find($campanha))->leads_total()->where('curso_id', $this->id); }
    public function leads () {
        return $this->hasMany(Lead::class)->where('status_id', 'LEAD');
    }

    public function getValorMatriculaAttribute () {
        if (isset($this->attributes['valor_matricula']) && !empty($this->attributes['valor_matricula']))
            return $this->attributes['valor_matricula'];

        // Se não estiver definido, usar como padrão o valor da mensalidade
        $matricula = $this->valor;

        // Lógica para valor de matrícula, descontos, etc aqui

        return $matricula;
    }

    // Dados Adicionais

    public function getDadosAdicionaisAttribute () {
        $json = $this->attribute_get('dados_adicionais', '[]');
        return json_decode($json, true);
    }
    public function setDadosAdicionaisAttribute ($value) { $this->attribute_set('dados_adicionais', json_encode($value)); }
    public function dados_adicionais ($prop, $value = null) {
        $da = $this->dados_adicionais;

        // Caso vá sobrescrever a propriedade

        if (!is_null($value))
            $da[$prop] = $value;

        // Se não existir a propriedade

        if (!isset($da[$prop]))
            return null;

        // Salvar

        $this->dados_adicionais = $da;

        return $da[$prop];
    }
}
