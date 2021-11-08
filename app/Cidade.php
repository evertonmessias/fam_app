<?php

namespace App;

class Cidade extends Model
{
    //
    protected $fillable = ['nome', 'estado'];
    protected $hidden = ['coordenadas'];
    public $timestamps = false;

    public function estado () {
    	return $this->belongsTo('App\Estado');
    }

    public function alunos () {
    	return $this->hasMany('App\Aluno');
    }

	public function setCoordenadasAttribute ($value) {
		if (is_object($value)) 
			$this->attributes['coordenadas'] = $value->json;
		else
			$this->attributes['coordenadas'] = (new Coordenada($value))->json;
	}
    public function getCoordenadasAttribute () {
    	if (empty($this->attributes['coordenadas'])) {
			// Atualizar objeto
    		$search = $this->nome . ' ' . $this->estado->nome;
			
			// $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($search) . '&format=json';
			
			// Usaremos agdora a API de Geocoding do Google Maps
			$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($search) . '&key=' . env('GOOGLE_MAPS_API_KEY');
			
			// create a new cURL resource
			$ch = curl_init();
			
			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
			// grab URL and pass it to the browser
			if (!$data = curl_exec($ch))
			trigger_error(curl_error($ch));
			
			// close cURL resource, and free up system resources
			curl_close($ch);
			
    		// $data = json_decode(file_get_contents($url))[0];
    		$data = json_decode($data);
    		sleep(1);

    		if (empty($data))
    			return null;
				
			$result = $data->results[0];
			$coords = $result->geometry->location;

			$this->coordenadas = $coords->lat .',' . $coords->lng;
    		$this->save();
    	}

    	return new Coordenada($this->attributes['coordenadas']);
    }
    public function getCoordenadasJsonAttribute () {
		$jsonCoords = $this->coordenadas->json;
    	return json_decode($jsonCoords, true);
    }
}
