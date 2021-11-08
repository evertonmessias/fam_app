<?php

namespace App;

class Midia_Tipo extends SimpleModel
{
    //
    protected $table = 'midias_tipos';

    protected $fillable = ['nome', 'codigo'];

    public function midias () {
    	return $this->hasMany('App\Midia', 'tipo_id');
    }

    public function subtipos () {
        return $this->hasMany('App\Midia_Tipo', 'categoria_id');
    }

    public function getSubtiposAllAttribute () {
        $subtipos = [];
        foreach ($this->subtipos()->cursor() as $subtipo) {
            $subtipos[] = $subtipo;
            $subtipos = array_merge($subtipos, $subtipo->subtipos_all);
        }
        return $subtipos;
    }

    public function categoria () {
    	return $this->belongsTo('App\Midia_Tipo');
    }

    public function categoria_arvore($include_this = false, $separador = ' <i class="fa fa-angle-right"></i> ', $formato = '{{ nome }}') {
        return implode($separador, $this->categoria_custom($formato, $include_this));
    }

    public function categoria_custom($template, $include_this = false) {
        $categorias = $this->categoria_completa_object;

        if ($include_this)
            $categorias[] = $this;

        foreach ($categorias as $k => $cat) {
            $categorias[$k] = $cat->renderToTemplate($template);
        }

        return $categorias;
    }

    public function getCategoriaCompletaAttribute() {
        $categorias = $this->categoria_completa_object;

        foreach ($categorias as $k => $cat) {
            $categorias[$k] = $cat->nome;
        }

        return $categorias;
    }

    public function getCategoriaCompletaIdsAttribute() {
        $categorias = $this->categoria_completa_object;

        foreach ($categorias as $k => $cat) {
            $categorias[$k] = $cat->id;
        }

        return $categorias;
    }

    public function getCategoriaCompletaObjectAttribute() {
        $categorias = [];

        $parent = $this->categoria;
        while ($parent != null) {
            $categorias[] = $parent;
            $parent = $parent->categoria;
        }

        return array_reverse($categorias);
    }
}
