<?php

namespace App;

class Settings extends SimpleModel
{
	protected $table = 'settings';
	protected $primaryKey = 'opcao';
	public $incrementing = false;
	
    public static function get_all() {
    	$settings = [];

    	foreach (Settings::cursor() as $opt) {
    		$settings[$opt->opcao] = $opt->valor;
    	}

    	return $settings;
    }
    public static function get($name = null, $default = null) {
    	if (is_null($name))
    		return static::get_all();

    	$s = static::find($name);

    	if (is_null($s)) return $default;
    	return $s->valor;
    }
    public static function set($name, $value) {
    	$s = static::find($name);

    	if (is_null($s))
    		$s = new static();

    	$s->opcao = $name;
    	$s->valor = $value;
    	$s->save();

    	return $s->valor;
    }
}
