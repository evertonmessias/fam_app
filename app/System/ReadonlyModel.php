<?php

namespace App\System;

use App\SimpleModel;

class ReadonlyModel extends SimpleModel {

	// Desabilita função save, delete, etc
 
	public function save (array $options = []) {
		return false;
	}
	public function update (array $attributes = [], array $options = []) {
		return false;
	}
	  
	static function firstOrCreate (array $arr) {
		return false;
	}
	  
	static function firstOrNew (array $arr) {
		return false;
	}
	  
	public function delete () {
		return false;
	}
	  
	static function destroy ($ids) {
		return false;
	}
	  
	public function restore () {
		return false;
	}
	  
	public function forceDelete () {
		return false;
	}
	  
	/* We need to disable date mutators, because they brake toArray function on this model */
	
	public function getDates () {
		return array();
	}
}

?>