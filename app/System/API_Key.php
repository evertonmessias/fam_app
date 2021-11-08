<?php

namespace App\System;

use App\SimpleModel;

class API_Key extends SimpleModel {
	protected $table = 'api_keys';

	protected $_authenticated = false;

	public function authenticated () { return $this->_authenticated; }
	public function getAuthenticatedAttribute () { return $this->authenticated(); }

	private static function generate_private ($key, $hash = true) {
		$private = md5(env('app_key')) . $key->id . $key->key;
	}

	public static function generate ($description = '') {
		$key = new API_Key();
		$key->description = $description;
		$key->key = uniqid();
		$key->save();

		$key->private_key = password_hash(static::generate_private($key), PASSWORD_BCRYPT);
		$key->save();

		return $key;
	}

	public static function auth ($key, $private_key = null) {
		if (!is_object($key))
			$key = static::where('key', $key)->first();

		if (is_null($key))
			return false;

		if (is_null($private_key))
			return $key;

		if (password_verify (static::generate_private($key), $private_key)) {
			$key->_authenticated = true;
			return $key;
		}

		return false;
	}

	public static function validate ($key, $private_key) {
		if (is_object(static::auth($key, $private_key)))
			return true;

		return false;
	}

	public function matches ($private_key) {
		static::validate($this, $private_key);
	}
}