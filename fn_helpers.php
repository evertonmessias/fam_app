<?php

/**
 * Retro-compatibilidade com o sistema antigo (lliure) para integrar com o CRM atual
 * 
 * Não faço a menor idéia de como o lliure funciona, isso é apenas uma conversão.
 * Altere por sua conta e risco.
 */ 

if (!function_exists('lead2listizer')) {
	function lead2listizer ($lead) {
		if (is_null($lead))
			return null;

		return $lead->export_listizer();
	}
}

// Converte array de leads para o listizer

if (!function_exists('leads2listizer')) {
function leads2listizer ($leads) {
	$listizer = [];

	if (is_array($leads) || is_a($leads, 'Illuminate\Database\Eloquent\Collection')) {
		// Array
		foreach ($leads as $lead) {
			$listizer [] = lead2listizer($lead);
		}
	} else {
		// Cursor
		foreach ($leads->cursor() as $lead) {
			$listizer [] = lead2listizer($lead);
		}
	}

	return $listizer;
}
}