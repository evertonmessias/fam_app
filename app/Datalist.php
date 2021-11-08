<?php

namespace App;

class Datalist {
	protected $builder;
	protected $list;

	public function __construct ($list) {
		$this->builder = DatalistModel::where('list', $list);
		$this->list = $list;
	}

	public static function on ($list) {
		return new static($list);
	}

	public function get () {
		return $this->builder->get();
	}

	public function find ($term) {
		return $this->builder->where('key', $term);
	}

	public function insert ($value, $key = null) {
		$i = new DatalistModel();
		$i->list = $this->list;
		$i->key = $key;
		$i->value = $value;
		$i->save();

		if (is_null($key)) {
			$i->key = $i->id;
			$i->save();
		}

		return $i;
	}

	public function set ($key, $value) {
		$o = $this->find($key)->first();

		if (is_null($o))
			$o = new DatalistModel();

		$o->list = $this->list;
		$o->key = $key;
		$o->value = $value;
		$o->save();

		return $o;
	}

	public function cursor () { return $this->builder->cursor(); }
}