<?php

namespace App\System;

class Serializer {
	public static function get_object_vars ($object) {
		$r = new \ReflectionObject($object);
		$p = [];

		$props = $r->getProperties();

		foreach ($props as $prop) {
			$prop->setAccessible(true);
			$p[$prop->getName()] = $prop->getValue($object);
		}

		return $p;
	}

	public static function toArray ($object) {
		$a = static::get_object_vars($object);

		foreach ($a as $k => $v) {
			if (is_object($v))
				$a[$k] = static::toArray($v);
		}

		return $a;
	}
}