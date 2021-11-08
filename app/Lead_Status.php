<?php

namespace App;

class Lead_Status extends SimpleModel
{
    //
    protected $table = 'lead_status';
    protected $primaryKey = 'codigo';
    
    public $incrementing = false;

    protected $fillable = ['codigo', 'nome'];


}
