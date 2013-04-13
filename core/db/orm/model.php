<?php namespace Neph\Core\DB\ORM;

use \Neph\Core\DB;
use \Neph\Core\Loader;
use \Neph\Core\Event;

class Model {
    static $key = 'id';
    static $table;
    static $columns = array();
    static $accessible;
    public static $connection;
    public static $hidden = array();

    static $factories = array();

    protected $attributes = array();
    protected $original = array();
    public $relationships = array();

    protected $exists = false;

    static function all() {
        return DB::table(static::table())->get();
    }

    static function table() {
        if (!isset(static::$table)) static::$table = strtolower(class_basename(new static()));
        return static::$table;
    }

    static function check() {
        $check = DB::check(static::table());
        throw new Exception('Unfinished yet!');
    }

    static function invoker($name) {
        return new ModelInvoker($name);
    }

    static function factory($name) {
        if (!isset(static::$factories[$name])) {
            static::$factories[$name] = new ModelFactory($name);
        }
        return static::$factories[$name];
    }

    static function columns() {
        if (empty(static::$columns)) {
            static::$columns = DB::columns(static::table());
        }
        return static::$columns;
    }

    public static function create($attributes) {
        $model = new static($attributes);

        $success = $model->save();

        return ($success) ? $model : false;
    }

    function get_key() {
        return $this->attributes[static::$key];
    }

    public function dirty()
    {
        return ! $this->exists or count($this->get_dirty()) > 0;
    }

    static public function query() {
        return DB::table(static::table());
    }

    public function save()
    {
        if ( ! $this->dirty()) return true;

        Event::emit('orm.saving', array( 'model' => $this ));

        // If the model exists, we only need to update it in the database, and the update
        // will be considered successful if there is one affected row returned from the
        // fluent query instance. We'll set the where condition automatically.
        if ($this->exists)
        {
            $query = static::query()->where(static::$key, '=', $this->get_key());

            $result = $query->update($this->get_dirty()) === 1;

            if ($result) Event::emit('orm.updated', array( 'model' => $this ));;
        }

        // If the model does not exist, we will insert the record and retrieve the last
        // insert ID that is associated with the model. If the ID returned is numeric
        // then we can consider the insert successful.
        else
        {
            $id = static::query()->insert_get_id($this->attributes, static::$key);

            $this->attributes[static::$key] = $id;

            $this->exists = $result = is_numeric($this->get_key());

            if ($result) Event::emit('orm.created', array( 'model' => $this ));
        }

        // After the model has been "saved", we will set the original attributes to
        // match the current attributes so the model will not be viewed as being
        // dirty and subsequent calls won't hit the database.
        $this->original = $this->attributes;

        if ($result) Event::emit('orm.saved', array( 'model' => $this ));

        return $result;
    }

    public function __construct($attributes = array(), $exists = false) {
        $this->exists = $exists;

        $this->fill($attributes);
    }

    public function fill(array $attributes, $raw = false)
    {
        foreach ($attributes as $key => $value)
        {
            // If the "raw" flag is set, it means that we'll just load every value from
            // the array directly into the attributes, without any accessibility or
            // mutators being accounted for. What you pass in is what you get.
            if ($raw)
            {
                $this->set_attribute($key, $value);

                continue;
            }

            // If the "accessible" property is an array, the developer is limiting the
            // attributes that may be mass assigned, and we need to verify that the
            // current attribute is included in that list of allowed attributes.
            if (is_array(static::$accessible))
            {
                if (in_array($key, static::$accessible))
                {
                    $this->$key = $value;
                }
            }

            // If the "accessible" property is not an array, no attributes have been
            // white-listed and we are free to set the value of the attribute to
            // the value that has been passed into the method without a check.
            else
            {
                $this->$key = $value;
            }
        }

        // If the original attribute values have not been set, we will set
        // them to the values passed to this method allowing us to easily
        // check if the model has changed since hydration.
        if (count($this->original) === 0)
        {
            $this->original = $this->attributes;
        }

        return $this;
    }

    function __set($key, $value) {
        $this->attributes[$key] = $value;
    }

    public function to_array()
    {
        $attributes = array();

        // First we need to gather all of the regular attributes. If the attribute
        // exists in the array of "hidden" attributes, it will not be added to
        // the array so we can easily exclude things like passwords, etc.
        foreach (array_keys($this->attributes) as $attribute)
        {

            if ( ! in_array($attribute, static::$hidden))
            {
                $attributes[$attribute] = $this->attributes[$attribute];
            }
        }

        foreach ($this->relationships as $name => $models)
        {
            // Relationships can be marked as "hidden", too.
            if (in_array($name, static::$hidden)) continue;

            // If the relationship is not a "to-many" relationship, we can just
            // to_array the related model and add it as an attribute to the
            // array of existing regular attributes we gathered.
            if ($models instanceof Model)
            {
                $attributes[$name] = $models->to_array();
            }

            // If the relationship is a "to-many" relationship we need to spin
            // through each of the related models and add each one with the
            // to_array method, keying them both by name and ID.
            elseif (is_array($models))
            {
                $attributes[$name] = array();

                foreach ($models as $id => $model)
                {
                    $attributes[$name][$id] = $model->to_array();
                }
            }
            elseif (is_null($models))
            {
                $attributes[$name] = $models;
            }
        }

        return $attributes;
    }

    public static function __callStatic($method, $parameters) {
        return call_user_func_array(array(static::query(), $method), $parameters);
    }

}
