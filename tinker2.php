<?php

use App\Midia_Tipo;
use Illuminate\Support\Facades\Cache;

include('database/seeds/exported.php');

function tipo ($aluno) {
	$conheceu = (isset($aluno['comoconheceu']) ? $aluno['comoconheceu'] : null);
	
	if (!is_null($conheceu)) {
	    $midia = Cache::remember('midia-' . md5($conheceu), $minutes, function () use ($conheceu) {
	    	$midia = Midia_Tipo::where('nome', $conheceu)->first();

	    	if (is_null($midia))
	        	$midia = Midia_Tipo::find('Outros');

            return $midia;
        });

	    return $midia;
	}
}