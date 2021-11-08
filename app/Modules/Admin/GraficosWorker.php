<?php

namespace App\Modules\Admin;

class GraficosWorker extends \Worker {
	public $data = [];
    public function run() {
        echo 'Running '.$this->getStacked().' jobs'.PHP_EOL;
    }
    public function addData($grafico, $data) {
        $this->data = array_merge($this->data, [$grafico => $data]);
        echo 'qq';
    }
}