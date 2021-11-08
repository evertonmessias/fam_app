<?php

namespace App;

class Periodo extends SimpleModel
{
    //
    protected $fillable = ['periodo'];

    public function cursos () {
    	return $this->hasMany('App\Curso');
    }
}
