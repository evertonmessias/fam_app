<?php

namespace App;

use Carbon\Carbon;

class Campanha extends Model
{
    //
    protected $fillable = ['nome', 'inicio', 'fim', 'status', 'budget', 'campos_personalizados'];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }

    public function on_create () {
        $this->midias()->sync([
            3, 5, 7, 8, 17, 19, 24
        ]);
        $this->save();
    }

    public function tags () { return $this->hasMany(Campanha_Tag::class); }

    public function before_create () {
        if (!$this->attribute_exists('campos_personalizados'))
            $this->campos_personalizados = [];
        if (!$this->attribute_exists('textos'))
            $this->textos = [];
    }

    // Campanha Parent/Principal
    public function parent () { return $this->belongsTo(Campanha::class); }

    // Campanhas filhas
    public function childs () { return $this->hasMany(Campanha::class, 'parent_id'); }

    // Scope

    // public function porCandidatoScope ($query) {
        // return $query->distinct('aluno_id');
    // }

    // Totais

    public function with_childs ($query) {
        $campanhas = [$this->id];
        $className = get_class($query->getRelated());
        $tableName = $query->getRelated()->getTable();

        $builder = $className::whereNotNull($tableName . '.campanha_id');
        $query->setQuery($builder);

        foreach ($this->childs as $campanha) {
            $campanhas[] = $campanha->id;
        }

        return $query->whereIn($tableName . '.campanha_id', $campanhas);
    }

    public function leads_total () {
        return $this->with_childs($this->hasManyCustom(Lead::class));
    }
    public function leads_total_unique () {
        return $this->leads_total()->distinct('aluno_id')->groupBy('aluno_id')->get();
    }

    public function inscritos_total () {
        return $this->leads_total()
            ->join('lead_status', 'leads.status_id', 'lead_status.codigo')
            ->where('lead_status.base_id', '>', '0')
            ->where('lead_status.base_id', '<', '9');
    }
    public function inscritos_total_unique () {
        return $this->inscritos_total()->distinct('aluno_id')->groupBy('aluno_id');
    }

    public function matriculados_total () {
        return $this->matriculados()/*->orWhere('status_id', 'PREMATR')*/;
    }
    public function matriculados_total_unique () {
        return $this->matriculados_total()->distinct('aluno_id')->groupBy('aluno_id');
    }

    // Tipos Base

    public function provas_next () {
        return $this->inscritos()->join('provas', 'leads.prova_id', '=', 'provas.id')->join('provas_datas', 'provas.data_id', '=', 'provas_datas.id')->where('provas_datas.hora', '>', Carbon::today()->toDateTimeString())->count();
    }

    public function candidatos () {
        // return $this->hasMany(Lead::class)->distinct('aluno_id');
        return $this->with_childs($this->hasManyCustom(Lead::class))->distinct('aluno_id')->groupBy('aluno_id');
    }

    public function leads () {
    	return $this->with_childs($this->hasManyCustom(Lead::class))->where('status_id', 'LEAD');
    }

    public function inscritos () {
        return $this->with_childs($this->hasManyCustom(Lead::class))->where('status_id', 'INSC');
    }

    public function matriculados () {
        return $this->with_childs($this->hasManyCustom(Lead::class))->where('status_id', 'MATR');
    }

    public function ausentes () {
        return $this->with_childs($this->hasManyCustom(Lead::class))->where('status_id', 'AUSENTE');
    }

    public function aprovados () {
        return $this->with_childs($this->hasManyCustom(Lead::class))->where('status_id', 'APROVADO');
    }

    public function cursos () {
    	return $this->belongsToMany('App\Curso')->ordered();
    }

    // Resto do código

    public function midias () {
        return $this->belongsToMany('App\Midia_Tipo', 'campanha_midia', 'campanha_id', 'midia_id')->withPivot('ordenar')->orderBy('ordenar', 'DESC');
    }

    public function unidades () {
        return $this->belongsToMany('App\Unidade', 'campanha_unidades', 'campanha_id', 'unidade_id');
    }
    public function getLocaisProvasAttribute () {
        $locais = [];

        foreach ($this->unidades()->cursor() as $unidade) {
            $_locais = $unidade->locais_provas;
            foreach ($_locais as $local) {
                array_push($locais, $local);
            }
        }

        return collect($locais)->unique(function ($local) { return $local->id; });
    }

    public function provas () { return $this->with_childs($this->hasManyCustom(Prova::class)); }

    public function notas_fiscais () { return $this->with_childs($this->hasManyCustom(Nota_Fiscal_Campanha::class)); }

    public function getBudgetConsumidoAttribute () {
        $consumido = 0.0;
        foreach ($this->notas_fiscais()->cursor() as $nota) {
            if (!is_null($nota->deleted_at))
                continue;
            
            $consumido += $nota->valor;
        }
        return $consumido;
    }

    public function getIsProgramadaAttribute () {
        $a = time();
        $i = strtotime($this->inicio);
        return ($a < $i);
    }
    public function getIsAtivaAttribute () {
        $a = time();
        $i = strtotime($this->inicio);
        $f = strtotime($this->fim);
        return ($i < $a && $a < $f);
    }
    public function getIsEncerradaAttribute () {
        $a = time();
        $f = strtotime($this->fim);
        return ($f < $a);
    }

    public function getInicioEfetivoAttribute () {
        $c_inicioDate = Carbon::createFromFormat('Y-m-d', $this->inicio);
        $c_firstEvent = $this->leads()->orderBy('created_at', 'ASC')->first();
        $c_firstEventDate = $c_firstEvent->created_at;

        // Caso a data de início especificada for maior que a do primeiro lead registrado, a data efetiva será a do primeiro lead registrado.
        if ($c_inicioDate->gt($c_firstEventDate))
            $date = $c_firstEventDate;
        else
            $date = $c_inicioDate;

        // Retorna data efetiva
        return $date;
    }

    public function getFimEfetivoAttribute () {
        $c_fimDate = Carbon::createFromFormat('Y-m-d', $this->inicio);
        $c_lastEvent = $this->leads()->orderBy('updated_at', 'DESC')->first();
        $c_lastEventDate = $c_lastEvent->created_at;

        // Caso a data de término especificada for menor que a do último lead registrado, a data efetiva será a do último lead registrado.
        if ($c_fimDate->lt($c_lastEventDate))
            $date = $c_lastEventDate;
        else
            $date = $c_fimDate;

        // Retorna data efetiva
        return $date;
    }

    public function getDuracaoAttribute () {
        return Carbon::createFromFormat('Y-m-d', $this->inicio)->diffInDays(Carbon::createFromFormat('Y-m-d', $this->fim));
    }
    public function getDuracaoEfetivaAttribute () {
        return $this->inicioEfetivo->diffInDays($this->fimEfetivo);
    }

    public function setInicioAttribute ($value) {
        $this->attributes['inicio'] = Carbon::createFromFormat('d/m/Y', $value);
    }

    public function setFimAttribute ($value) {
        $this->attributes['fim'] = Carbon::createFromFormat('d/m/Y', $value);
    }

    public function getCamposPersonalizadosAttribute() {
        $json = $this->attribute_get('campos_personalizados', '[]');
        return json_decode($json, true);
    }

    public function setCamposPersonalizadosAttribute ($value) {
        $this->attribute_set('campos_personalizados', json_encode($value));
    }

    public function getTextosAttribute() {
        $json = $this->attribute_get('textos', '{}');
        return json_decode($json, true);
    }

    public function setTextosAttribute ($value) {
        $this->attribute_set('textos', json_encode($value));
    }

    public function getStatusAttribute () {
        $a = time();
    	$i = strtotime($this->inicio);
    	$f = strtotime($this->fim);

        $status = 'Desconhecido';

        if     ($this->is_programada) $status = 'Programada';
        elseif ($this->is_ativa) $status = 'Ativa';
        elseif ($this->is_encerrada) $status = 'Encerrada';

    	return $status;
    }

    public $_cursos = null;
    public function tem_curso ($curso_id) {
        if (is_null($this->_cursos)) {
            $this->_cursos = [];
            foreach ($this->cursos()->cursor() as $curso) {
                $this->_cursos[] = $curso->id;
            }
        }

        return in_array($curso_id, $this->_cursos);
    }

    public $_unidades = null;
    public function tem_unidade ($unidade_id) {
        if (is_null($this->_unidades)) {
            $this->_unidades = [];
            foreach ($this->unidades()->cursor() as $unidade) {
                $this->_unidades[] = $unidade->id;
            }
        }

        return in_array($unidade_id, $this->_unidades);
    }

    public function getAlunosAttribute () {
        $alunos = [];
        $ja_foram = [];

        foreach ($this->leads()->cursor() as $lead) {
            $aluno = $lead->global_relation('aluno');

            if (!in_array($aluno->cpf, $ja_foram))
                $alunos[] = $aluno;
        }

        return $alunos;
    }
}
