<?php

namespace App\Modules\Admin;

use App\Benchmark;
use App\Helpers;

class Grafico {
	protected $id;
	protected $dados;
	protected $chart;
	protected $tipo;

	protected $helper_info = [];
	protected $org_fn = null;
	protected $post_fn = null;
	protected $_pointer = null;
	protected $_sort = null;
	protected $_sort_after = null;

	public $titulo;
	public $color;
	public $colors;

	protected static $unique_id = 0;
	protected static function new_id () {
		self::$unique_id += 1;
		return self::$unique_id;
	}

	public function fnHelperInfo ($name, $fn = null, $separator = '.') {
		if (is_array($name))
			$name = implode($separator, $name);

		return Helpers::array_multilevel($this->helper_info, $name, $fn, $separator);
	}

	public function setHelperInfo ($name, $value = null) {
		return $this->fnHelperInfo($name, function ($v) use ($value) {
			return $value;
		});
	}

	public function getHelperInfo ($name) {
		return $this->fnHelperInfo($name);
	}

	public function __construct ($dados = null, $tipo = null) {

		if ($dados == null)
			return;

		if (is_array($dados))
			$this->dados = $dados;
		else
			$this->pointer($dados);

		$this->chart = new ChartData();
		$this->id = self::new_id();

		if (is_null($tipo)) {
			$tipo = static::class;
			$tipo = explode('\\', $tipo);
			$tipo = array_pop($tipo);
		}

		$this->tipo = $tipo;
	}

	public function pointer ($ptr = null) { if (!is_null($ptr)) $this->_pointer = $ptr; return $this->_pointer; }
	public function get_id () { if(!isset($this->id)) $this->id = self::new_id(); return $this->id; }

	public function filtrar ($parametros) {
		foreach ($this->dados as $k => $v) {
			if (!self::validar($v, $cond))
				unset ($this->dados[$k]);
		}

		return $this;
	}
	public function processar () {
		$this->chart->title = $this->titulo;

		$ret = $this->chart->data();
		$ret['id'] = $this->id;
		$ret['helpers'] = $this->helper_info;

		return $ret;
	}
	public function render () {
		return $this->render_view ($this->tipo, $this->processar());
	}
	protected function render_view ($view, $data) { return \Illuminate\Support\Facades\View::make('Admin::Graficos.' . $view, $data)->render(); }
	public function json () { return json_encode($this->processar()); }

	public function preparar ($fn) {
		$this->org_fn = $fn;
		return $this;
	}

	public function post_exec ($fn) {
		$this->post_fn = $fn;
		return $this;
	}

	public function async () {

		$bench_id = 'c' . $this->id . '.' . static::class;

		// Caso tenha escolhido um sort, já colocar aqui

		if (!is_null($this->_sort)) {
			$fn = $this->_sort['fn'];
			$keys = $this->_sort['keys'];
			$this->chart->sort($keys, $fn);
		}

		// Rodar funções

		$fn = $this->org_fn;
			
		// if (method_exists($this->pointer(), 'cursor')) {
		try {
			foreach ($this->pointer()->cursor() as $linha) {
	    		Benchmark::run($bench_id);
				$fn($linha, $this->chart);
			}
		// } else {
		} catch (\Exception $e) {
			foreach ($this->pointer() as $linha) {
	    		Benchmark::run($bench_id);
				$fn($linha, $this->chart);
			}
		}

		// Pós-processamento (importante)

		if (!is_null($this->_sort_after)) {
			$fn = $this->_sort_after['fn'];
			$keys = $this->_sort_after['keys'];
			$this->chart->sort($keys, $fn);
		}

		// Função de pós-processamento
		if(!is_null($this->post_fn)) {
			$fn = $this->post_fn;
			$this->chart = $fn($this->chart);
		}

		return $this;
	}

	public function organizar ($fn = null) {
		if (is_null($fn))
			$fn = $this->org_fn;

		// Caso tenha escolhido um sort, já colocar aqui

		if (!is_null($this->_sort)) {
			$fn = $this->_sort['fn'];
			$keys = $this->_sort['keys'];
			$this->chart->sort($keys, $fn);
		}

		foreach ($this->dados as $linha) {
			$fn($linha, $this->chart);
		}

		// Pós-processamento (importante)

		if (!is_null($this->_sort_after)) {
			$fn = $this->_sort_after['fn'];
			$keys = $this->_sort_after['keys'];
			$this->chart->sort($keys, $fn);
		}

		// Função de pós-processamento
		if(!is_null($this->post_fn))
			$this->post_fn($this->chart);


		return $this;
	}

	public function sort ($fn = null, $keys = false) {
		if (is_null($fn)) $this->_sort = null;

		$this->_sort = ['keys' => $keys, 'fn' => $fn];
		return $this;
	}

	public function sort_after ($fn = null, $keys = false) {
		if (is_null($fn)) $this->_sort_after = null;

		$this->_sort_after = ['keys' => $keys, 'fn' => $fn];
		return $this;
	}

	public function dados () { return $this->chart; }

	// Funções helper

	static function validar ($obj, $parametros) {

		// Caso tenha mais de um parâmetro
		if (is_array($parametros[0])) {
			foreach ($parametros as $cond) {
				if (!self::validar($linha, $parametros))
					return ['ok' => false, 'cond' => $cond];
			}

			return ['ok' => true, 'cond' => $parametros];
		}

		// Parâmetro único
		$tem_op = (count($parametros) == 3);

		$col = $parametros[0];
		$op = $tem_op ? $parametros[1] : '=';
		$match = $tem_op ? $parametros[2] : $parametros[1];

		if (is_array($obj))
			$dados = $obj[$col];
		else
			$dados = $obj->{$col};

		$result = true;

		switch ($op) {
			case '>': $result = ($dados > $match); break;
			case '>=': $result = ($dados >= $match); break;
			case '<=': $result = ($dados <= $match); break;
			case '<': $result = ($dados < $match); break;
			case '===': $result = ($dados === $match); break;
			case '==':
			case '=':
			default: $result = ($dados == $match); break;
		}

		return ['ok' => $result, 'cond' => $parametros];
	}
}

// \Threaded::extend(Grafico::class);

?>