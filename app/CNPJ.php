<?php

namespace App;

class CNPJ extends FormattedNumberModel {
	protected static $format = '99.999.999/9999-99';
	protected static $err_invalid = 'CNPJ Inválido';
	protected static $len_max = 11;

	public static function validate ($cnpj = null) {
	    // Verifica se um número foi informado
	    if(empty($cnpj) || is_null($cnpj)) {
	        return false;
	    }
	 
	    // Elimina possivel mascara
	    $cnpj = static::input($cnpj);

		// Valida tamanho
		if (strlen($cnpj) != 14)
			return false;

		// Valida primeiro dígito verificador
		for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
		{
			$soma += $cnpj{$i} * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}
		$resto = $soma % 11;
		if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto))
			return false;

		// Valida segundo dígito verificador
		for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
		{
			$soma += $cnpj{$i} * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}
		$resto = $soma % 11;
		return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
	}
}