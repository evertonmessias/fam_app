<?php

namespace App;

class RG extends FormattedNumberModel {
	protected static $format = '99.999.999-*';
	protected static $err_invalid = 'RG Inválido';
	protected static $format_input = null;
	protected static $len_max = 9;
}