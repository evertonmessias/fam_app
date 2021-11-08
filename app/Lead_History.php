<?php

namespace App;

use Carbon\Carbon;

class Lead_History extends SimpleModel {
    //
    protected $table = 'lead_history';
    protected $fillable = ['campanha_id', 'lead_id', 'at', 'status_was', 'status_new', 'title', 'description'];
    
    public $incrementing = false;

    public function campanha () { return $this->belongsTo(Campanha::class); }
    public function lead () { return $this->belongsTo(Lead::class); }

    public function getAtAttribute () { return Carbon::createFromTimestamp(strtotime($this->attributes['at'])); }

    public function before_create() {
    	// Caso não tenha setado campanha, usar a do lead
    	if (is_null($this->campanha))
    		$this->campanha()->associate($this->lead->id);

    	// Caso não tenha setado momento da alteração, usar agora
    	if (is_null($this->at))
    		$this->at = Carbon::now();

    	// Caso não tenha setado qual era o status, usar o do lead
    	if (is_null($this->status_was))
    		$this->status_was()->associate($this->lead->status);

    	// Caso não tenha setado o novo status, usar o atual
    	if (is_null($this->status_new))
    		$this->status_new()->associate($this->status_was);

    	// Caso não tenha setado descrição, deixar em branco (campo TEXT)
    	if (!$this->attribute_exists('description'))
    		$this->description = '';
    }

    public function at($diff) {
        return $this->at->addHours($diff);
    }
}
