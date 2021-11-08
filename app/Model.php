<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Query\Expression;
use Watson\Rememberable\Rememberable;

use App\System\Serializer;

use App\Relationships\HasManyCustom;

class Model extends \Illuminate\Database\Eloquent\Model {
    use Rememberable;

    public function renderToTemplate ($template) {
        return Helpers::render_template($template, $this->toArray());
    }

    public function rawIndex($index_raw, $fn) {
        $table = $this->getTable();

        $this->setTable(\DB::raw($table . ' ' . $index_raw));

        $ret = $fn($this, $table);

        $this->setTable(\DB::raw($table));

        return $ret;
    }
    public function useIndex($index, $fn) {
    	return $this->rawIndex('USE INDEX (' . $index . ')', $fn);
    }
    public function forceIndex($index, $fn) {
    	return $this->rawIndex('FORCE INDEX (' . $index . ')', $fn);
    }

    protected $orderBy;
    protected $orderDirection = 'ASC';

    public function scopeOrdered ($query) {
        if ($this->orderBy) {
            return $query->orderBy($this->orderBy, $this->orderDirection);
        }

        return $query;
    }

    public function scopeGetOrdered($query) {
        return $this->scopeOrdered($query)->get();
    }

    public static function callStatic () {
        $x = func_get_args();
        $fn = $x[0];

        if (count($x) > 1)
            $args = array_slice($x, 1);
        else
            $args = [];

        return forward_static_call_array([static::class, $fn], $args);
    }

    public function getRowNumberAttribute() { if (isset($this->attributes['row'])) return $this->attributes['row']; return $this->getRowNumber(); }

    public function scopeWithRowNumber($query, $column = 'created_at', $order = 'asc') {
        DB::statement(DB::raw('set @row=0'));

        $sub = static::selectRaw('*, @row:=@row+1 as row')
            ->orderBy($column, $order)->toSql();

        $query->remember(1)->from(DB::raw("({$sub}) as sub"));
    }

    public function getRowNumber($column = 'created_at', $order = 'asc') {
        $order = ($order == 'asc') ? 'asc' : 'desc';

        $key = "userRow.{$this->id}.{$column}.{$order}";

        if (Cache::get($key)) return Cache::get($key);

        $row = $this->withRowNumber($column, $order)
            ->where($column, '<=',$this->$column)
            ->whereId($this->id)->pluck('row');

        Cache::put($key, $row);

        return $row;
    }

        /**
     * This determines the foreign key relations automatically to prevent the need to figure out the columns.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $relation_name
     * @param string $operator
     * @param string $type
     * @param bool   $where
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeModelJoin($query, $relation_name, $operator = '=', $type = 'left', $where = false) {
        $relation = $this->$relation_name();
        $table = $relation->getRelated()->getTable();
        $one = $relation->getQualifiedParentKeyName();
        $two = $relation->getForeignKey();

        if (empty($query->columns)) {
            $query->select($this->getTable().".*");
        }
        foreach (\Schema::getColumnListing($table) as $related_column) {
            $query->addSelect(new Expression("`$table`.`$related_column` AS `$table.$related_column`"));
        }
        return $query->join($table, $one, $operator, $two, $type, $where); //->with($relation_name);

    }

    public function create_with_linked_attributes ($attr = []) {
        $n = new static();
        $n->link_attributes ((array) $attr);
        return $n;
    }

    static $_prop_store = [];
    public function global_relation($property, $fallback = false) {
        $relation = $this->$property();

        // Caching, baby :3

        $relation = Cache::remember('GlobalRelation.' . static::class . '->' . $property, 0, function () use ($relation) {
            $related = $relation->getRelated();
            $table = $related->getTable();

            $hasMany = false;

            if (method_exists($relation, 'getOtherKey')) {
                // BelongsTo
                // $key = $relation->getOwnerKey();
                $key = $relation->getOtherKey();
                $attr = $relation->getForeignKey();
            } else if (method_exists($relation, 'getOwnerKey')) {
                // BelongsTo
                $key = $relation->getOwnerKey();
                $attr = $relation->getForeignKey();
            } else {
                // HasMany
                $serialized = Serializer::get_object_vars($relation);
                $key = $serialized['foreignKey'];
                // $key = $relation->getForeignKey();
                $attr = $this->primaryKey;
                $hasMany = true;

                // if (isset(static::$_prop_store[$property]))
                    // dd(static::$_prop_store);
            }

            // Parse Local Key

            $attr = explode('.', $attr);
            $attr = array_pop($attr);

            // Parse Foreign Key

            $key = explode('.', $key);
            $key = array_pop($key);

            return [
                'relation' => $relation,
                'related' => $related,
                'table' => $table,
                'localKey' => $attr,
                'foreignKey' => $key,
                'hasMany' => $hasMany
            ];
        });

        // Parseia o cache

        $related = $relation['related'];
        $table = $relation['table'];

        $attr = $relation['localKey'];
        $key = $relation['foreignKey'];
        $hasMany = $relation['hasMany'];

        $relation = $relation['relation'];

        // Agora sim

        if (!isset($this->attributes[$attr]))
            return null;

        if (isset(static::$_prop_store[$property])) {

            // If not found, try fallback or return empty

            if (!isset(static::$_prop_store[$property][$this->attributes[$attr]])) {
                if ($fallback) return $this->{$property};
                if ($hasMany) return collect([]);
                return null;
            }

            // Found, convert to array

            $data = (array) (static::$_prop_store[$property][$this->attributes[$attr]]);

            // If is a HasMany, parse all elements back into objects

            if ($hasMany) {
                foreach ($data as $k => $d) {
                    $data[$k] = $related->create_with_linked_attributes($d);
                }
                return collect($data);
            }

            return $related->create_with_linked_attributes($data);
        } else {
            if ($hasMany) {
                $filtered = [];
                $all = DB::table($table)->get();

                foreach ($all as $v) {
                    $k = $v->{$key};

                    if (!isset($filtered[$k]))
                        $filtered[$k] = [];

                    $filtered[$k][] = $v;
                }

                static::$_prop_store[$property] = $filtered;
                return $this->global_relation ($property, $attr);
            }

            static::$_prop_store[$property] = DB::table($table)->get()->keyBy($key);
            return $this->global_relation ($property, $key);
        }
    }
    public function link_attributes ($attr = []) {
        try {
            $this->attributes = array_merge($this->attributes, $attr);
        } catch (Exception $e) { unset($e); }
    }

    protected function attribute_retrieve ($attr, $empty = null) { return $this->attribute_get($attr, $empty); }
    protected function attribute_get ($attr, $empty = null) {
        if ($this->attribute_exists($attr))
            return $this->attributes[$attr];
        
        return $empty;
    }
    protected function attribute_set ($attr, $value) { $this->attributes[$attr] = $value; }
    public function attribute_exists ($attr) {
        return isset($this->attributes[$attr]);
    }
    public function attribute_empty ($attr) {
        return ((!$this->attribute_exists($attr)) || empty($this->attributes[$attr]));
    }

    // Batch saving

    static $batch_enabled = false;
    static $batch_save = []; 

    // Enable batch
    public static function batch_enable () {
        static::$batch_enabled = true;
    }

    // Finish batch and save all
    public static function batch_finish () {
        static::$batch_enabled = false;
        static::insert(static::$batch_save);
    }

    // Override do 'save()'
    public function on_save ($new = false) {}
    public function before_save ($new = false) {}
    public function on_create () {}
    public function before_create () {}
    public function save(array $options = []) {

        // Detectar quando estiver criando o model
        $isCreate = false;
        if ($this->attribute_empty($this->primaryKey))
            $isCreate = true;

        // Eventos pré
        if ($isCreate) $this->before_create();
        $this->before_save($isCreate);

        // Salvar
        if (static::$batch_enabled) {
            static::$batch_save[] = $this;
            return;
        } else
            parent::save();

        // Eventos pós
        if ($isCreate) $this->on_create();
        $this->on_save($isCreate);
    }

    // Custom-query Relationships


    public function hasManyCustom($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $instance = new $related();
        $localKey = $localKey ?: $this->getKeyName();

        return new HasManyCustom($instance->newQuery(), $this, $foreignKey, $localKey);
    }
}

?>