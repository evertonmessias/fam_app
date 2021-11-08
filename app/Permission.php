<?php

namespace App;

use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission {
	public static function make ($name, $display, $desc) {
		$perm = new Permission();
        $perm->name = $name;
        $perm->display_name = $display;
        $perm->description = $desc;
        $perm->save();
        return $perm;
	}
}