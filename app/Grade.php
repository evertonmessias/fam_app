<?php

namespace App;

class Grade extends SimpleModel
{
    //
    protected $fillable = ['nome'];
    
    protected $table = 'cursos_grades';
}
