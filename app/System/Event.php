<?php

namespace App\System;

use App\SimpleModel;

use Illuminate\Support\Facades\Auth;

class Event extends SimpleModel {
	protected $table = 'sys_events';

	public function process () { return $this->belongsTo(Process::class); }

	public static function register ($type, $message, $meta = []) {
		global $procID;

		$e = new static();
		$e->type = $type;
		$e->message = $message;
		$e->meta = json_encode($meta);

		// ID do Processo em execução
		if (isset($procID))
			$e->process_id = $procID;

		$e->save();
		return $e;
	}

	public function run ($fn) {
		try {
			$ret = $fn($this);
			$this->finish();

			return $ret;
		} catch (\Exception $e) {
			$this->error($e)->finish();

			throw $e;
		}
	}

	public function error ($e) {
		$this->error = json_encode([
			'code' => $e->getCode(),
			'message' => $e->getMessage(),
			'trace' => $e->getTraceAsString()
		]);
		return $this;
	}

	public function finish () {
		$this->finished = true;
		$this->save();
		return $this;
	}

	public function meta ($name, $value = null) {
		$meta = json_decode($this->attributes['meta']);

		if (!is_object($meta))
			$meta = new \stdClass();

		if (!is_null($value))
			$meta->{$name} = $value;

		$this->attributes['meta'] = json_encode($meta);

		return $meta->{$name};
	}
}