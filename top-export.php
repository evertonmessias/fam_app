<?php

use Illuminate\Support\Facades\DB;

$top = DB::table('top_resultados')->get();

$top->transform(function ($resultado) {
	$area = '';
	$area_score = 0;

	$resultados = json_decode($resultado->resultados);

	foreach ($resultados as $k => $v) {
		if ($v > $area_score) {
			$area_score = $v;
			$area = $k;
		}
	}

	unset($resultado->raw);
	unset($resultado->created_at);
	unset($resultado->updated_at);

	$resultado->area = $area;
	return $resultado;
});

file_put_contents('top.json', json_encode($top));