<?php

namespace App\System;

// Classe usada apenas para listar migrations

class Migration extends ReadonlyModel {
	protected $table = 'migrations';
}