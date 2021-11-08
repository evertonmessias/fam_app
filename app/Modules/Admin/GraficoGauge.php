<?php

namespace App\Modules\Admin;

class GraficoGauge extends Grafico {
	public $dados = ['min' => 0, 'max' => 100, 'value' => 0];

	public function valores ($value = 0, $max = 100, $min = 0) {
		$this->dados['min'] = $min;
		$this->dados['max'] = $max;
		$this->dados['value'] = $value;

		return $this;
	}

	public function render ($tipo = 'GraficoGauge') {
		$this->dados['id'] = $this->get_id();
		return $this->render_view ($tipo, $this->dados);
	}
}