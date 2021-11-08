<?php

namespace App;

class Unidade extends SimpleModel
{
	protected $fillable = ['nome', 'endereco', 'numero', 'bairro', 'cidade_id', 'telefone', 'email', 'coordenadas'];

	public function cursos() { 
		return $this->belongsToMany(Curso::class);
	}

	public function locais_provas () {
		return $this->hasMany(Prova_Local::class);
	}

	public function getTelefoneAttribute () {
		return (new Telefone($this->attributes['telefone']))->formatted();
	}
	public function setTelefoneAttribute ($value) {
		$this->attributes['telefone'] = (new Telefone($value))->numeric();
	}

	public function setCoordenadasAttribute ($value) {
		if (is_object($value))
			$this->attributes['coordenadas'] = $value->json;
		else
			$this->attributes['coordenadas'] = (new Coordenada($value))->json;
	}
	public function getCoordenadasAttribute () {
		return new Coordenada($this->attributes['coordenadas']);
	}

	public function getEnderecoCompletoAttribute () {
		return Endereco::completo($this->attributes);
	}
}