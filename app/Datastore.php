<?php

namespace App;

class Datastore extends Model
{
    //
    protected $fillable = ['identifier', 'data'];
    protected $table = 'datastore';

    // Main getter/setter
    public function getDataAttribute () {
        return json_decode($this->attributes['data'], true);
    }
    public function setDataAttribute ($value) {
        $this->attributes['data'] = json_encode($value);
    }

    // Append data
    public function appendData ($data) {
        $this->data = array_merge($this->data, $data);
        return $this->data;
    }
    
    // Static functions
    public static function store ($identifier, $data) {
        $datastore = new static();
        $datastore->identifier = $identifier;
        $datastore->data = $data;
        $datastore->save();

        return $datastore;
    }
    public static function retrieve ($identifier, $where = array()) {
        $result = static::identifiedBy($identifier)->get();

        foreach ($where as $k => $v) {
            $result = $result->whereStrict('data.' . $k, $v);
        }

        return $result;
    }
    public static function identifiedBy ($identifier) {
        return static::where('identifier', $identifier);
    }
}