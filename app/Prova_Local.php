<?php

namespace App;

class Prova_Local extends SimpleModel {
	protected $table = 'provas_locais';
	protected $fillable = ['local', 'unidade_id', 'endereco', 'telefone', 'email', 'corodenadas'];

	public function unidade () {
		return $this->belongsTo(Unidade::class);
	}

	public function datas_provas () { return $this->hasMany(Prova_Data::class, 'local_id'); }
	public function getDatasProvasDisponiveisAttribute () {
		$validas = [];
		$datas = $this->datas_provas;

		foreach ($datas as $data) {
			if ($data->exibir)
				$validas[] = $data;
		}

		return collect($validas)->sortBy('hora');
	}

	public function getLocalAttribute () {
		return (empty($this->attributes['local']) ? $this->unidade->nome : $this->attributes['local']);
	}

	public function getEnderecoAttribute () {
		return (empty($this->attributes['endereco']) ? $this->unidade->endereco_completo : $this->attributes['endereco']);
	}

	public function getTelefoneAttribute () {
		return (empty($this->attributes['telefone']) ? $this->unidade->telefone : $this->attributes['telefone']);
	}

	public function getEmailAttribute () {
		return (empty($this->attributes['email']) ? $this->unidade->email : $this->attributes['email']);
	}

	public function getCoordenadasAttribute () {
		$coords = (empty($this->attributes['coordenadas']) ? $this->unidade->coordenadas : $this->attributes['coordenadas']);

		if (is_object($coords))
			return $coords;
		
		return new Coordenada ($coords);
	}
}