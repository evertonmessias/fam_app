<?php

namespace App;

class Campanha_Tag extends SimpleModel {
	protected $table = 'campanha_tags';

	public function campanha () { return $this->belongsTo(Campanha::class); }
}