<?php

namespace App\Modules\Admin;

class ChartData {
	public $dados = [];
	public $graficos = [];

	public $title = '';

	public function chart ($grafico = '__root__', $title = null) {
		if (!isset($this->graficos[$grafico]))
			$this->graficos[$grafico] = new ChartData();

		if ($title != null)
			$this->graficos[$grafico]->title = $title;

		return $this->graficos[$grafico];
	}

	public function date_range ($start, $finish, $data, $format = 'Y-m-d', $override = false) {
		$t_c = strtotime($start);
		$t_e = strtotime($finish);

		while ($t_c < $t_e) {
			$date = date($format, $t_c);

			if (is_callable($data))
				$data = $data($this, $date);

			if ($override || !isset($this->dados[$date]))
				$this->dados[$date] = $data;

			$t_c += 86400;
		}

		return $this;
	}

	public function sort ($keys = false, $fn = false) {

		if (is_callable($fn)) {
			if ($keys)
				uksort($this->dados, $fn);
			else
				uasort($this->dados, $fn);
		} else {
			if ($keys)
				ksort($this->dados);
			else
				sort($this->dados);
		}

		return $this;
	}

	public function data () {
		$r = [];

		$r['title'] = $this->title;
		$r['data'] = $this->dados;

		if (!empty($this->graficos)) {
			$r['charts'] = [];
			foreach (array_keys($this->graficos) as $grafico) {
				$r['charts'][$grafico] = $this->chart($grafico)->data();
			}
		}

		return $r;
	}

	public function date_format ($format = 'Y-m-d') {
		$new = [];

		foreach ($this->dados as $k => $v) {
			$new[date($format, strtotime($k))] = $v;
		}

		return $new;
	}

	public function add ($column, $amount = 1) {
		if (!isset($this->dados[$column]))
			$this->dados[$column] = $amount;
		else
			$this->dados[$column] += $amount;

		return $this;
	}

	public function subtract ($column, $amount = 1) {
		if (!isset($this->dados[$column]))
			$this->dados[$column] = $amount;
		else
			$this->dados[$column] -= $amount;

		return $this;
	}

	public function set ($column, $amount) {
		$this->dados[$column] = $amount;

		return $this;
	}

	public function get ($column) {
		if (isset ($this->dados[$column]))
			return $this->dados[$column];
		return 0;
	}
}