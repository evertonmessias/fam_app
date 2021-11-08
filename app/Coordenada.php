<?php

namespace App;

class Coordenada extends SimpleModel {
	protected $coordenada = ['nome' => '', 'lat' => 0.0, 'lon' => 0.0, 'zoom' => 17];

	public function __construct ($coords = null, $nome = '') {
		if (!is_null($coords))
			$this->parse($coords, $nome);
	}

	public function getCoordenadasAttribute() { return $this->coordenada; }
	public function getLatAttribute() { return $this->coordenada['lat']; }
	public function getLatitudeAttribute() { return $this->coordenada['lat']; }
	public function getLonAttribute() { return $this->coordenada['lon']; }
	public function getLongitudeAttribute() { return $this->coordenada['lon']; }
	public function getZoomAttribute() { return $this->coordenada['zoom']; }

	public function getJsonAttribute () { return json_encode($this->coordenada); }

	protected static function getLatLon($coords) {
		$result = ['lat' => 0.0, 'lon' => 0.0];

		$matches = [];

		if(preg_match_all('/(\-?\d+.\d+)|(\d+)z/', $coords, $matches)) {
			$result['lat'] = $matches[1][0];
			$result['lon'] = $matches[1][1];

			if (isset($matches[0][2]))
				$result['zoom'] = $matches[2][2];
		}

		return $result;
	}

	public function parse ($coords, $nome = '') {
		if (starts_with($coords, 'http')) {
			$matches = [];

			// Parse URL do Google Maps
			preg_match_all('/\/place\/(.+)\/@(.+)\//', $coords, $matches);

			// Seta o nome baseada na URL
			$this->coordenada['nome'] = urldecode($matches[1][0]);

			// Seta as coordenadas baseada na URL
			$coords = static::getLatLon($matches[2][0]);
			$this->coordenada = array_merge($this->coordenada, $coords);

			// Nome personalizado
			if (!empty($nome))
				$result->coordenada['nome'] = $nome;
		} elseif (starts_with($coords, '{')) {
			$this->coordenada = json_decode($coords, true);
		} else {
			// URL por string normal

			$this->coordenada = static::getLatLon($coords);
		}

		return $this;
	}
}