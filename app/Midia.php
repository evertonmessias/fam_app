<?php

namespace App;

class Midia extends SimpleModel
{
    protected $fillable = ['nome', 'fornecedor', 'midia'];

    public function fornecedor () {
    	return $this->belongsTo('App\Fornecedor');
    }
    public function tipo () {
    	return $this->belongsTo('App\Midia_Tipo');
    }

    public function getNomeAttribute () {
    	if (empty($this->attributes['nome']))
    		return $this->tipo->nome;
    	return $this->attributes['nome'];
    }
}
