<?php

namespace Maduser\Minimal\Database\ORM;

use Maduser\Minimal\Collections\Contracts\CollectionInterface;

/**
 * Class AbstractRelation
 *
 * @package Maduser\Minimal\Database\ORM
 */
abstract class AbstractRelation
{
    private $caller;

    /**
     * The name of the related class
     *
     * @var string
     */
    protected $class;

    /**
     * The key name that represents the related class
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The key name that represents the querying class
     *
     * @var string
     */
    protected $localKey;

    /**
     * The name of the many-to-many table
     *
     * @var string
     */
    protected $pivotTable;

    /**
     * @return mixed
     */
    public function getCaller()
    {
        return $this->caller;
    }

    /**
     * @param mixed $caller
     */
    public function setCaller($caller)
    {
        $this->caller = $caller;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param $class
     *
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @param $foreignKey
     *
     * @return $this
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocalKey()
    {
        return $this->localKey;
    }

    /**
     * @param $localKey
     *
     * @return $this
     */
    public function setLocalKey($localKey)
    {
        $this->localKey = $localKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getPivotTable()
    {
        return $this->pivotTable;
    }

    /**
     * @param $pivotTable
     *
     * @return $this
     */
    public function setPivotTable($pivotTable)
    {
        $this->pivotTable = $pivotTable;

        return $this;
    }

    public function resolve(
        CollectionInterface $collection,
        string $with,
        ORM $queryingClass = null
    ) {
        return $queryingClass;
    }

    public function resolveInline(ORM $orm)
    {
        return $orm;
    }
}