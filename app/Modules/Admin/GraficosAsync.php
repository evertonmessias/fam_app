<?php

namespace App\Modules\Admin;

use App\Modules\Admin\Grafico;
use App\Modules\Admin\Graficos;

class GraficosAsync extends \Thread {
	public $grafico;

	public function __construct() {
        // $this->grafico = $grafico;
    }

    public function run() {
    	// echo microtime(true).PHP_EOL;

    	var_dump($this->grafico);

    	// $this->grafico->async();

		// $this->worker->addData($this->grafico);
    }
}