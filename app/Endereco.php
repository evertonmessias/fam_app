<?php

namespace App;

class Endereco {
	public static function completo ($attrs) {
		$attrs = array_merge(['endereco' => '', 'numero' => 'S/N', 'complemento' => '', 'bairro' => '', 'cidade_id' => 1], $attrs);

		if (!isset($attrs['cidade_id'])) return;

		$attrs['cidade'] = Cidade::find($attrs['cidade_id']);

		return Helpers::render_template ('{{ endereco }}, {{ numero }}{% if complemento %} - {{ complemento }}{% endif %}{% if bairro %} - {{ bairro }}{% endif %} - {{ cidade.nome }}/{{ cidade.estado.uf }}', $attrs);
	}
}