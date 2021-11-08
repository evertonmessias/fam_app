<?php

namespace App;

class FormattedNumberModel {

	private $data;
	protected static $err_invalid = 'Invalid data format.';
	protected static $err_length = 'Data too short.';

	protected static $format = '';
	protected static $format_input = '/[^0-9]/';

	protected static $len_min = 0;
	protected static $len_max = null;

	public function __construct ($data) {
		if (empty($data)) return;

		if (!static::validate($data))
			throw new \Exception(static::$err_invalid, 1);

	    $this->data = static::input($data);
	}

	public function formatted() {
		return static::format($this->data);
	}

	public function numeric() {
		return $this->data;
	}

	protected static function input ($data) {
		// Substituir
		if (!is_null(static::$format_input))
	    	$data = preg_replace(static::$format_input, '', $data);

	    // Testar comprimento dos dados
	    if (strlen($data) < static::$len_min)
	    	throw new \Exception(static::$err_length, 1);
	    	
	    // Cortar para comprimento máximo
	    if (!is_null(static::$len_max))
	    	$data = str_pad($data, static::$len_max, '0', STR_PAD_LEFT);

	    return $data;
	}

	public static function validate ($data = null) {
		if (is_null($data))
			return false;

		return true;
	}
	
	public static function format ($data, $formato = null) {
		if (is_null($formato))
			$formato = static::$format;

		$ret = '';
		$len = strlen($formato);
		$strpos = 0;
		for ($i = 0; $i < $len; $i++) {
			$chr = $formato[$i];

			$type = 'copy';

			if (is_numeric($chr)) $type = 'num';
			if (ctype_alpha($chr)) $type = 'txt';
			if ($chr == '*') $type = 'any';

			$skip = true;
			while ($skip && $strpos < strlen($data)) {
				$dch = $data[$strpos];
				switch ($type) {
					case 'num':
						if (is_numeric($dch)) {
							$ret .= $dch;
							$skip = false;
						}
						break;
					case 'txt':
						if (ctype_alnum($dch)) {
							$ret .= $dch;
							$skip = false;
						}
						break;
					case 'any':
						if (ctype_alnum($dch) || is_numeric($dch)) {
							$ret .= $dch;
							$skip = false;
						}
						break;
					default:
						$ret .= $chr;
						$skip = false;
						$strpos--;
						break;
				}
				$strpos++;
			}
		}

		// Retorna string correta
		return $ret;
	}
}