<?php

namespace Maduser\Minimal\Database\ORM;

use Maduser\Minimal\Collections\Collection;
use Maduser\Minimal\Collections\Contracts\CollectionInterface;
use Maduser\Minimal\Database\Exceptions\DatabaseException;
use Maduser\Minimal\Database\Connectors\PDO;
use Maduser\Minimal\Database\QueryBuilder;

class ORM
{
    /**
     * The class that builds and sends the database statements
     *
     * @var QueryBuilder
     */
    protected $builder;

    /**
     * Database table to use
     *
     * @var string
     */
    protected $table;

    /**
     * Prefix database table name
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Default order of the results
     *
     * @var string
     */
    protected $orderBy = '';

    /**
     * Default ID column
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Whether to use automatic timestamps
     *
     * @var bool
     */
    protected $timestamps = false;

    /**
     * Column name for 'created at" timestamp
     *
     * @var string
     */
    protected $timestampCreatedAt = 'created';

    /**
     * Column name for 'updated at" timestamp
     *
     * @var string
     */
    protected $timestampUpdatedAt = 'updated';

    /**
     * Represents the actual state of the row in database
     *
     * @var array
     */
    protected $state = [];

    /**
     * Holds the changed values, will be same as $state after each query
     *
     * @var array
     */
    protected $attributes = [];


    /**
     * Names of related objects to eager load
     *
     * @var array
     */
    protected $with = [];

    /**
     * Holds information about relationships
     *
     * @var array
     */
    protected $relations = [];

    /**
     * Holds the related objects
     *
     * @var array
     */
    protected $related = [];

    /**
     * @return QueryBuilder
     */
    public function getBuilder(): QueryBuilder
    {
        return $this->builder;
    }

    /**
     * @param QueryBuilder $builder
     *
     * @return ORM
     */
    public function setBuilder(QueryBuilder $builder): ORM
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * @param bool $withPrefix
     *
     * @return mixed
     */
    public function getTable($withPrefix = true)
    {
        return $withPrefix ? $this->getPrefix() . $this->table : $this->table;
    }

    /**
     * @param mixed $table
     *
     * @return ORM
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param mixed $prefix
     *
     * @return ORM
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    /**
     * @param string $orderBy
     *
     * @return ORM
     */
    public function setOrderBy(string $orderBy): ORM
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     *
     * @return ORM
     */
    public function setPrimaryKey(string $primaryKey): ORM
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * @return bool
     */
    public function useTimestamps(): bool
    {
        return $this->timestamps;
    }

    /**
     * @param bool $timestamps
     *
     * @return ORM
     */
    public function setTimestamps(bool $timestamps): ORM
    {
        $this->timestamps = $timestamps;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimestampCreatedAt(): string
    {
        return $this->timestampCreatedAt;
    }

    /**
     * @param string $timestampCreatedAt
     *
     * @return ORM
     */
    public function setTimestampCreatedAt(string $timestampCreatedAt): ORM
    {
        $this->timestampCreatedAt = $timestampCreatedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimestampUpdatedAt(): string
    {
        return $this->timestampUpdatedAt;
    }

    /**
     * @param string $timestampUpdatedAt
     *
     * @return ORM
     */
    public function setTimestampUpdatedAt(string $timestampUpdatedAt): ORM
    {
        $this->timestampUpdatedAt = $timestampUpdatedAt;

        return $this;
    }

    /**
     * @return array
     */
    public function getState(): array
    {
        return $this->state;
    }

    /**
     * @param array $state
     *
     * @return ORM
     */
    public function setState(array $state): ORM
    {
        if (isset($state[$this->getPrimaryKey()])) {
            $this->state = $state;
        }

        $this->attributes = $state;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     *
     * @return ORM
     */
    public function setAttributes(array $attributes): ORM
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return array
     */
    public function getWith(): array
    {
        return $this->with;
    }

    /**
     * @param array|string $with
     *
     * @return ORM
     */
    public function setWith($with): ORM
    {
        $this->with = $with;

        return $this;
    }

    /**
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param array $relations
     *
     * @return ORM
     */
    public function setRelations(array $relations): ORM
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * @param $key
     * @param AbstractRelation $value
     *
     * @return $this
     */
    public function addRelation($key, AbstractRelation $value)
    {
        $this->relations[$key][$value->getClass()] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getRelated(): array
    {
        return $this->related;
    }

    /**
     * @param array $related
     *
     * @return ORM
     */
    public function setRelated(array $related): ORM
    {
        $this->related = $related;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function addRelated($key, $value)
    {
        $this->related[$key] = $value;

        return $this;
    }

    /**
     * ORM constructor.
     *
     * @param null $connection
     */
    public function __construct($connection = null)
    {
        ! is_null($connection) || $connection = PDO::connection();

        $builder = $this->newQueryBuilder();
        $builder->setDb($connection);

        $builder->setPrimaryKey($this->getPrimaryKey());
        $builder->setPrefix($this->getPrefix());
        $builder->setTable($this->getTable());
        $builder->setOrderBy($this->getOrderBy());

        $builder->setTimestamps($this->useTimestamps());
        $builder->setTimestampCreatedAt($this->getTimestampCreatedAt());
        $builder->setTimestampUpdatedAt($this->getTimestampUpdatedAt());

        $this->builder = $builder;
    }

    /**
     * @return QueryBuilder
     */
    public function newQueryBuilder()
    {
        return new QueryBuilder();
    }

    /**
     * @param array $data
     *
     * @return ORM
     */
    public static function instance(array $data = []): ORM
    {
        /** @var ORM $obj */
        $class = get_called_class();
        $obj = new $class();
        $obj->setState($data);

        return $obj;
    }

    /**
     * @param array $data
     *
     * @return ORM|Collection
     */
    public static function create(array $data = [])
    {
        if (isset($data[0]) && is_array($data[0])) {

            $collection = new Collection();

            foreach ($data as $row) {
                /** @var ORM $obj */
                $class = get_called_class();
                $obj = new $class();
                $obj->setState($row);
                $obj->save();

                $collection->add($obj);
            }

            return $collection;
        }

        /** @var ORM $obj */
        $class = get_called_class();
        $obj = new $class();
        $obj->setState($data);
        $obj->save();

        return $obj;
    }

    /**
     * @return CollectionInterface
     */
    public static function all()
    {
        /** @var ORM $obj */
        $class = get_called_class();
        $obj = new $class();

        return $obj->getAll();
    }

    public function save()
    {
        $attr = $this->attributes;
        $key = $this->getPrimaryKey();

        $id = isset($attr[$key]) ? $attr[$key] : null;
        if ($id) {
            $affectedRows = $this->builder->update($id, $attr);
            $id = $affectedRows > 0 ? $id : null;
        } else {
            $id = $this->builder->insert($attr);
        }

        if ($id) {
            $new = $this->getById($id);
            $this->attributes = $new->getAttributes();
            $this->state = $new->getState();
        }

        return $this;
    }

    public function delete()
    {
        return $this->builder->delete($this->attributes[$this->getPrimaryKey()]);
    }

    /**
     * @param int $id
     *
     * @return ORM
     * @throws DatabaseException
     */
    public static function find(int $id): ORM
    {
        if (! ($instance = self::instance()->getById($id))) {
            throw new DatabaseException('Could not find ' . __CLASS__ .
                ' where primary key is ' . $id);
        }
        return $instance;
    }

    /**
     * @param int $id
     *
     * @return ORM|null
     */
    public function getById(int $id)
    {
        $result = $this->builder->getById($id);

        if (isset($result[0])) {
            return self::instance($result[0]);
        }

        return null;
    }

    /**
     * @param null $sql
     *
     * @return ORM|null
     */
    public function getFirst($sql = null)
    {
        $result = $this->builder->getFirst($sql);

        if (isset($result[0])) {
            return self::instance($result[0]);
        }

        return null;
    }

    /**
     * @param null $sql
     *
     * @return ORM|null
     */
    public function first($sql = null)
    {
        $obj = $this->getFirst($sql);

        if ($obj && $this->getWith()) {
            $collection = new Collection();
            $collection->add($obj);

            $this->resolveRelations($collection);

            return $collection->first();
        }

        return $obj;
    }

    /**
     * Return all the rows of this table
     *
     * @param null $sql Optional query string
     *
     * @return Collection|null
     * @throws DatabaseException
     */
    public function getAll($sql = null)
    {
        $results = $this->builder->query($sql)->collect();

        if ($results) {

            $collection = new Collection();

            foreach ($results as $key => $row) {
                $collection->add(self::instance($row));
            }

            $this->resolveRelations($collection);

            return $collection;
        }

        return null;
    }

    public function associate($item)
    {
        $item = func_get_args();
        $relation = array_shift($item);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->{$relation->getForeignKey()} = $item[0]->{$item[0]->getPrimaryKey()};
        $this->save();
        return $this;
    }

    public function dissociate($relation)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->{$relation->getForeignKey()} = null;
        $this->save();

        return $this;
    }

    public function attach($args)
    {
        return call_user_func_array([$this, 'updateRelation'],
            ['attach', func_get_args()]);
    }

    public function detach($args)
    {
        return call_user_func_array([$this, 'updateRelation'],
            ['detach', func_get_args()]);
    }

    public function updateRelation($method, $args)
    {
        $relation = array_shift($args);

        /** @var CollectionInterface $toDetach */
        foreach ($args as $toUpdate) {

            if ($toUpdate instanceof CollectionInterface) {
                $item = $toUpdate->first();
                $items = $toUpdate->extract($item->getPrimaryKey());
            }

            if ($toUpdate instanceof ORM) {
                $items = $toUpdate->{$toUpdate->getPrimaryKey()};
            }

            if (!isset($items)) {
                throw new DatabaseException("Cannot handle item to " . $method);
            }

            $this->builder->{$method}(
                $items,
                $this->{$this->getPrimaryKey()},
                $relation->getPivotTable(),
                $relation->getForeignKey(),
                $relation->getLocalKey()
            );
        }

        return $this;
    }

    public function resolveRelations(&$collection)
    {
        foreach ($this->with as $with) {

            /** @var AbstractRelation $relation */
            $relation = $this->{explode('.', $with)[0]}();
            $relation->resolve($collection, explode('.', $with)[0], $this);
        }
    }

    public function addWith($with)
    {
        $this->with[] = $with;

        return $this;
    }

    /**
     * @param $arg
     *
     * @return $this
     * @throws DatabaseException
     */
    public function with($arg)
    {
        $args = func_get_args();

        if (is_array($args[0])) {
            $this->setWith($args[0]);

            return $this;
        }

        foreach ($args as $arg) {
            if (is_string($arg)) {
                $this->addWith($arg);
            }

            return $this;
        }

        throw new DatabaseException('The arguments for with() don\'t fit');
    }

    public function hasOne($class, $foreignKey, $localKey)
    {
        $relation = new HasOne($class, $foreignKey, $localKey);
        $this->addRelation('HasOne', $relation);

        return $relation;
    }

    public function hasMany($class, $foreignKey, $localKey)
    {
        $relation = new HasMany($class, $foreignKey, $localKey);
        $this->addRelation('HasMany', $relation);

        return $relation;
    }

    public function belongsTo($class, $foreignKey, $localKey)
    {
        $relation = new BelongsTo($class, $foreignKey, $localKey);
        $this->addRelation('BelongsTo', $relation);

        return $relation;
    }

    public function belongsToMany($class, $table, $localKey, $foreignKey)
    {
        $relation = new BelongsToMany($class, $table, $localKey, $foreignKey);
        $this->addRelation('BelongsToMany', $relation);

        return $relation;
    }

    public function drop($name)
    {
        unset($this->attributes[$name]);
        unset($this->state[$name]);
    }

    public function toArray()
    {
        $items = [];
        foreach ($this->related as $key => $related) {
            if ($related instanceof CollectionInterface) {
                $items[$key] = $related->getArray();
            } else {
                $items[$key] = $related->toArray();
            }
        }

        return array_merge($this->attributes, $items);
    }

    public function __toString()
    {
        foreach ($this->related as &$related) {
            if ($related instanceof CollectionInterface) {
                $related = $related->toArray();
            }
        }

        return json_encode(array_merge($this->attributes, $this->related));
    }

    public function __set($name, $arg)
    {
       $this->attributes[$name] = $arg;
    }

    public function __get($name)
    {
        if (method_exists($this, $name)) {
            /** @var AbstractRelation $result */
            $result = $this->{$name}();
            if ($result instanceof AbstractRelation) {

                $calledRelationMethod = debug_backtrace()[0]['args'][0];

                if (isset($this->related[$calledRelationMethod])) {
                    return $this->related[$calledRelationMethod];
                }

                return $result->resolveInline($this);
            }

            return $result;
        }

        return $this->{'get' . ucfirst($name)}();
    }

    public function __call($name, $arguments)
    {
        $_name = $name;

        $prefix = 'get';

        if (substr($name, 0, strlen($prefix)) == $prefix) {

            $name = lcfirst(substr($name, strlen($prefix)));
            if (array_key_exists($name, $this->state)) {
                return $this->attributes[$name];
            }
            throw new DatabaseException('Key ' . $_name . ' does not exist.');
        }

        $prefix = 'set';

        if (substr($name, 0, strlen($prefix)) == $prefix) {

            $name = lcfirst(substr($name, strlen($prefix)));
            if (array_key_exists($name, $this->state)) {
                $this->attributes[$name] = $arguments;
                return $this;
            }
            throw new DatabaseException('Key ' . $_name . ' does not exist.');

        }

        $result = call_user_func_array([$this->builder, $name], $arguments);

        if (in_array($name, ['lastQuery'])) {
            return $result;
        }

        return $this;
    }


}