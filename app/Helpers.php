<?php

namespace App;

use Twig_Loader_Array;
use Twig_Environment;

class Helpers {
	public static function array_rotate ($array) {
		$ret = [];
		$k = array_keys($array[array_keys($array)[0]]);

		foreach ($k as $key) {
			# code...
		}

		foreach ($array as $ak => $v) {
			foreach ($k as $key) {
				if (!isset($ret[$key]))
					$ret[$key] = array();

				$ret[$key][$ak] = $v[$key];
			}
		}

		return $ret;
	}

	public static function data_internacional ($data) {
		if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $data))
			return $data;

		$data = explode('/', $data);
		$data = array_reverse($data);
		$data = implode('-', $data);

		return $data;
	}

	public static function render_template ($template, $vars = []) {
		$templates = new Twig_Loader_Array(array(
			'template' => $template
		));
		$twig = new Twig_Environment($templates);
		return $twig->render('template', $vars);
	}

	public static function filter_only ($col, $filter) {
		if (is_array($col))
			$col = collect($col);

		if (is_array($filter))
			$filter = collect($filter);

		return $col->filter(function ($v, $k) use ($filter) {
			if ($filter->contains($v))
				return true;
		});
	}
	public static function filter_out ($col, $filter) {
		if (is_array($col))
			$col = collect($col);

		if (is_array($filter))
			$filter = collect($filter);

		return $col->reject(function ($v, $k) use ($filter) {
			if ($filter->contains($v))
				return true;
		});
	}

	public static function array_multilevel (&$array, $name, $fn = null, $separator = '.') {
		$name_tree = explode($separator, $name);

		if (count($name_tree) > 1) {
			$name_tree = array_reverse($name_tree);
			$name = array_pop($name_tree);
			$name_tree = array_reverse($name_tree);
			$name_tree = implode($separator, $name_tree);

			if (!isset($array[$name]))
				$array[$name] = [];

			return static::array_multilevel ($array[$name], $name_tree, $fn, $separator);
		} else {
			$value = (isset($array[$name]) ? $array[$name] : null);

			// Functional getter

			if (!is_null($fn) && is_callable($fn)) {
				$result = $fn($value);

				// Setter

				if (!is_null($result))
					$array[$name] = $result;
			}

			return $array[$name];
		}
	}

	public static function json_query ($query, $fn_json = null, $fn_nonjson = null) {
		$json_query = json_decode($query);

		if (!is_null($json_query)) {
			if (is_null($fn_json))
				return $json_query;
			else
				return $fn_json($json_query);
		} else {
			if (is_null($fn_nonjson))
				return $query;
			else
				return $fn_nonjson($query);
		}
	}
}