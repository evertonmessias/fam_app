<?php

namespace App\Modules\Admin;

class GraficoCombo extends Grafico {
	private $charts = [];

	public function add_chart ($chart) {
		$this->charts[] = $chart;
	}

	public function async () {
		foreach ($this->charts as $chart) {
			$chart->async();
		}
	}

	public function render ($tipo = 'GraficoCombo') {
		$html = '';

		foreach ($this->charts as $chart) {
			$html .= $chart->render();
		}

		return $html;
	}
}