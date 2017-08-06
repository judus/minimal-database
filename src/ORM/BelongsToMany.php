<?php

namespace Maduser\Minimal\Database\ORM;

use Maduser\Minimal\Collections\Collection;
use Maduser\Minimal\Collections\Contracts\CollectionInterface;
use Maduser\Minimal\Database\Exceptions\DatabaseException;

class BelongsToMany extends AbstractRelation
{
    public function __construct(
        $class,
        $pivotTable,
        $foreignKey,
        $localKey
    ) {
        $this->setClass($class);
        $this->setPivotTable($pivotTable);
        $this->setForeignKey($foreignKey);
        $this->setLocalKey($localKey);
        $this->setCaller(debug_backtrace()[1]['object']);
    }

    public function resolve(
        CollectionInterface $collection,
        string $with,
        ORM $queryingClass = null
    ) {
        $localKeys = $collection->extract($queryingClass->getPrimaryKey());
        $relatedCollection = $this->getWhereIn($localKeys);

        if ($relatedCollection) {
            /** @var ORM $item */
            foreach ($collection->getArray() as &$item) {
                $newCollection = new Collection();
                foreach ($relatedCollection as $related) {

                    if (isset($related->attributes['pivot_' . $this->getLocalKey()]) &&
                        $item->{$queryingClass->getPrimaryKey()} ==
                        $related->attributes['pivot_' . $this->getLocalKey()]) {
                        $related->drop('pivot_' . $this->getLocalKey());
                        $related->drop('pivot_' . $this->getForeignKey());
                        $newCollection->add($related);
                    }
                }
                $item->addRelated($with, $newCollection);
            }
        }
    }

    public function resolveInline(ORM $queryingClass)
    {
        $localKey = $queryingClass->{$queryingClass->getPrimaryKey()};

        return $this->getWhereIn([$localKey]);
    }

    public function getWhereIn($localKeys)
    {
        /** @var ORM $class */
        $class = $this->getClass();
        /** @var ORM $obj */
        $obj = $class::instance();

        $table = $obj->getTable();
        $pivot = $this->getPivotTable();
        $primary = $obj->getPrimaryKey();
        $localKey = $this->getLocalKey();
        $foreignKey = $this->getForeignKey();

        $sql = "SELECT `" . $table . "`.*, " .
            "`" . $pivot . "`.`" . $localKey . "` AS `pivot_" . $localKey . "`, " .
            "`" . $pivot . "`.`" . $foreignKey . "` AS `pivot_" . $foreignKey . "` " .
            "FROM `" . $table . "` " .
            "INNER JOIN `" . $pivot . "` " .
            "ON `" . $table . "`.`" . $primary . "` = `" . $pivot . "`.`" . $foreignKey . "` " .
            "WHERE `" . $pivot . "`.`" . $localKey . "` IN (" . implode(', ',
                $localKeys) . ")";

        return $obj->getAll($sql);
    }

    public function __call($name, $args)
    {
        array_unshift($args, $this);

        if (! in_array($name, ['attach', 'detach'])) {
            throw new DatabaseException('Call to undefined method ' . __CLASS__ . '::' . $name . '()');
        }

        return call_user_func_array([$this->getCaller(), $name], $args);
    }

}