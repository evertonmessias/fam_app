<?php

namespace App;

class Module extends Model
{
    //

    protected $fillable = ['domain', 'root', 'www', 'namespace'];

    public function getOptionsAttribute($value) {
    	if (empty($value)) $value = '{}';
    	return json_decode($value, true);
    }

    public function setOptionsAttribute($value) {
    	$this->attributes['options'] = json_encode($value);
    }

    public function getInfoAttribute () {
    	return Module::info($this->namespace);
    }
    public function getFullNameAttribute () {
    	return $this->info['name'] . ' (' . $this->domain . ($this->root == '/' ? '' : $this->root) . ')';
    }

    public function getUrlAttribute () {
        return $this->domain . $this->root;
    }

    public static function info ($module_id) {
		$dir = Module::directory($module_id);

		$settings = [
			'id' => $module_id,
			'name' => $module_id,
			'namespace' => $module_id,
			'options' => []
		];

		if (file_exists($dir . 'module.json')) {
			$settings = array_merge($settings, json_decode(file_get_contents($dir . 'module.json'), true));
		}

		foreach ($settings['options'] as $key => $value) {
			$settings['options'][$key]['_id'] = $key;
		}

		return $settings;
    }

    public static function directory ($module = null) {
    	$base = __DIR__ . '/Modules/';

    	$fix_dir = function ($path) { return str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path); };

    	$path = $base;

    	if (!is_null($module)) {
    		if (!isset($module->namespace) && is_integer($module)) {
    			$module = Module::find($module);
				$path = $base . $module->namespace;
    		} elseif (is_string($module)) {
    			$path = $base . $module;
    		} else {
				$path = $base . $module->namespace;
    		}
    	}

    	return realpath($fix_dir($path . '/')) . DIRECTORY_SEPARATOR;
    }

    public static function available () {
    	$dh = opendir(Module::directory());

    	$modules = [];

    	while ($dir = readdir($dh)) {
    		if ($dir == '.' || $dir == '..') continue;

    		$mod_id = $dir;
    		$modules [$mod_id] = Module::info($mod_id);
    	}

    	closedir($dh);

    	return $modules;
    }
}
