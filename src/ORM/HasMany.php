<?php

namespace Maduser\Minimal\Database\ORM;

use Maduser\Minimal\Collections\Collection;
use Maduser\Minimal\Collections\Contracts\CollectionInterface;

class HasMany extends AbstractRelation
{
    public function __construct($class, $foreignKey, $localKey)
    {
        $this->setClass($class);
        $this->setForeignKey($foreignKey);
        $this->setLocalKey($localKey);
    }

    public function resolve(
        CollectionInterface $collection,
        string $with,
        ORM $queryingClass = null
    ) {
        $localKeys = $collection->extract($this->getLocalKey());
        $relatedCollection = $this->getWhereIn($localKeys);

        if ($relatedCollection) {
            /** @var ORM $item */
            foreach ($collection->getArray() as &$item) {
                $newCollection = new Collection();
                foreach ($relatedCollection as $related) {
                    if ($item->{$this->getLocalKey()} ==
                        $related->{$this->getForeignKey()}
                    ) {
                        $newCollection->add($related);
                    }
                }
                $item->addRelated($with, $newCollection);
            }
        }

    }

    public function resolveInline(ORM $queryingClass)
    {
        $class = $this->getClass();

        /** @noinspection PhpUndefinedMethodInspection */
        return $class::instance()->where([
                $this->getForeignKey(),
                $queryingClass->{$this->getLocalKey()}
            ]
        )->getAll();
    }


    /**
     * @param      $array
     *
     * @return mixed
     */
    public function getWhereIn($array)
    {
        $class = $this->getClass();

        /** @noinspection PhpUndefinedMethodInspection */
        return $class::instance()->where([
            $this->foreignKey,
            'IN',
            implode(',', $array)
        ])->getAll();
    }
}