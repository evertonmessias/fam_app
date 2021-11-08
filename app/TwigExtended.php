<?php

namespace App;
 
use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleFilter;
use Twig_Loader_Array;
use Twig_Environment;

use \Illuminate\Support\Facades\View;

use App\Helpers;
 
class TwigExtended extends Twig_Extension {
 
	public function getName() {
		// 
	}
 
	/**
	 * Functions
	 * @return void
	 */
	public function getFunctions() {
		return [
			// Functions go here
		];
	}
 
	/**
	 * Filters
	 * @return void
	 */
	public function getFilters() {
		return [
			new Twig_SimpleFilter('md5', [$this, 'md5']),
			new Twig_SimpleFilter('array_order', [$this, 'array_order']),
			new Twig_SimpleFilter('array_reverse', [$this, 'array_reverse']),
			new Twig_SimpleFilter('array2js', [$this, 'array2js']),
			new Twig_SimpleFilter('array2json', [$this, 'array2json']),
			new Twig_SimpleFilter('array2keys', [$this, 'array2keys']),
			new Twig_SimpleFilter('array2values', [$this, 'array2values']),
			new Twig_SimpleFilter('getOptionsField', [$this, 'getOptionsField']),
			new Twig_SimpleFilter('dia_semana', [$this, 'dia_semana']),
			new Twig_SimpleFilter('floor', [$this, 'floor']),
			new Twig_SimpleFilter('filter_only', [$this, 'filter_only']),
			new Twig_SimpleFilter('filter_out', [$this, 'filter_out']),
		];
	}

	public function dia_semana ($dia) {
		return [
			'Domingo',
			'Segunda-feira',
			'Terça-feira',
			'Quarta-feira',
			'Quinta-feira',
			'Sexta-feira',
			'Sábado'
		][$dia];
	}
	public function filter_only ($col, $only) {
		return Helpers::filter_ony ($col, $only);
	}
	public function filter_out ($col, $except) {
		return Helpers::filter_out ($col, $except);
	}
	public function floor ($str) { return floor($str); }
	public function md5 ($str) { return md5($str); }
	public function array_rotate ($array) { return Helpers::array_rotate($array); }
	public function array2js ($array, $keys = false) { 
		if ($keys) {
			foreach ($array as $key => $value) {
				$array[$key] = json_encode([$key, $value]);
			}
		}
		return '[' . implode(',', $array) . ']';
	}
	public function array2json ($array, $keys = false) {
		if ($keys) {
			foreach ($array as $key => $value) {
				$array[$key] = json_encode([$key, $value]);
			}
		}
		return json_encode($array);
	}
	public function array2keys ($array) { return array_keys($array); }
	public function array2values ($array) { return array_values($array); }

	public function array_order ($array, $order) {
		$out = [];

		// Reordenar array
		foreach ($order as $k) {
			if (isset($array[$k])) {
				$out[$k] = $array[$k];
				unset($array[$k]);
			}
		}

		// Combinar itens restantes
		$out = array_merge($out, $array);

		return $out;
	}

	public function array_reverse ($array) { return array_reverse($array); }

	// Campo de Opção
	public function getOptionsField ($option, $modulo, $name = 'field', $template = null) {
		$view = $template;
		$args = [];
		$current = isset($modulo->options[$option['_id']]) ? $modulo->options[$option['_id']] : null;

		switch ($option['type']) {
			case 'model':
				$model = new \ReflectionMethod('App\\' . $option['model'], 'callStatic');
				
				// Caso exista algum valor já existente, pesquisar qual é no banco

				if (!is_null($current))
					$current = $model->invoke(null, 'where', $option['value'], $current);

				// Inicializa a engine de templates

				$templates = new Twig_Loader_Array(array(
					'display' => $option['display']
				));
				$twig = new Twig_Environment($templates);

				// Função helper que converte o Model em um formato específico para os templates

				$obj2opt = function ($object) use ($option, $twig) {
					if (is_null($object)) return null;

					$raw = $object->toArray();
					return [
						'value' => $object->{$option['value']},
						'text' => $twig->render('display', $raw),
						'raw' => $raw
					];
				};

				// Fazer loop pelo banco de dados e definir relação de valor-texto para o controle

				$displays = [];
				$currents = [];
				foreach ($model->invoke(null, 'cursor') as $object) {
					$displays[] = $obj2opt($object);
				}
				if (!is_null($current)) {
					foreach ($current->cursor() as $object) {
						$currents[] = $obj2opt($object);
					}
				}

				// Preparar controle para exibição

				if (is_null($template)) {
					if (isset($option['control']))
						$template = $option['control'];
					else
						$template = 'select';
				}

				$view = $template;
				$args = [
					'name_prefix' => $name,
					'name' => $option['_id'],
					'current' => $currents,
					'values' => $displays,
					'option' => $option
				];
				break;
			case 'radio':
				$view = 'radio';
				$args = [
					'name_prefix' => $name,
					'name' => $option['_id'],
					'current' => $current,
					'option' => $option
				];
				break;
			case 'text':
			default:
				$view = 'text';
				$args = [
					'name_prefix' => $name,
					'name' => $option['_id'],
					'current' => $current,
					'option' => $option
				];
				break;
		}
		return View::make('Admin::Control.' . $view, $args)->render();
	}
 
}