<?php

use App\Midia_Tipo;
use Illuminate\Support\Facades\Cache;

include('database/seeds/exported.php');

function tipo ($aluno) {
	$conheceu = (isset($aluno['comoconheceu']) ? $aluno['comoconheceu'] : null);
	
	if (!is_null($conheceu)) {
	    $midia = Cache::remember('midia-' . md5($conheceu), 5, function () use ($conheceu) {
	    	var_dump($conheceu);
	    	$midia = Midia_Tipo::where('nome', $conheceu)->first();
	    	var_dump($midia);

	    	if (is_null($midia))
	        	$midia = Midia_Tipo::find('Outros');

            return $midia;
        });

	    return $midia;
	}
}