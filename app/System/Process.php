<?php

namespace App\System;

use App\Model;

use Illuminate\Support\Facades\Auth;

class Process extends Model {
	protected $table = 'sys_processes';

	public function events () { return $this->hasMany(Event::class); }

	public static function init ($type) {
		$p = new static();
		$p->type = $type;
		
		// Logar usuário
		$user = Auth::user();
		if (!is_null($user))
			$p->user_id = $user->id;

		return $p;
	}

	public function text ($message) {
		$this->message = $message;
		$this->save();
		return $this;
	}

	public function run ($fn) {
		$this->save();

		try {
			global $procID;
			$procID = $this->id;

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
		if (empty($this->attributes['meta']))
			$this->attributes['meta'] = '{}';

		$meta = json_decode($this->attributes['meta']);

		if (!is_null($value))
			$meta->{$name} = $value;

		$this->attributes['meta'] = json_encode($meta);

		return $meta->{$name};
	}
}