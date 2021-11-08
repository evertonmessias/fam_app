<?php

namespace App;

class Cached_Chart extends Model
{
    //

	public $table = 'cached_charts';
    protected $primaryKey = 'chart_id';
    protected $fillable = 'chart_id';
}
