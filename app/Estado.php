<?php

namespace App;

class Estado extends Model
{
    //
    protected $fillable = ['nome', 'uf'];
    public $timestamps = false;

    public function cidades () {
    	return $this->hasMany('App\Cidade');
    }
}
