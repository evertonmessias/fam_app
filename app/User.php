<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable
{
    use Notifiable;
    use EntrustUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    // Nome da função que é exibida próxima ao nome do usuário
    public function getDisplayRoleAttribute() {
        if (isset($this->attributes['display_role']) && !empty($this->attributes['display_role']))
            return $this->attributes['display_role'];
        else {
            if ($this->roles->count())
                return $this->roles[0]->display_name;
            else
                return false;
        }
    }
}
