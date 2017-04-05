<?php
/**
 * Created by PhpStorm.
 * User: tomaszchmielewski
 * Date: 01/12/16
 * Time: 14:24
 */

namespace StdDomain\Entity;


abstract class AbstractAggregate implements AggregateInterface
{
    protected $aggregateItems = [];

    abstract public function getAggregateElementClass(): string;

    public function addItem(EntityInterface $item)
    {
        $class = $this->getAggregateElementClass();
        if (!$item instanceof $class) {
            throw new \InvalidArgumentException("Error while creating aggregate: 
            Item must be an instance of " . $class);
        }
        $this->aggregateItems[] = $item;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->aggregateItems);
    }

    /**
     * @return mixed
     */
    public function getElement($key)
    {
        if (!array_key_exists($key, $this->aggregateItems)) {
            throw new \DomainException("Element not found on position " . $key);
        }

        return $this->aggregateItems[$key];
    }

    /**
     * @return mixed
     */
    public function getLast()
    {
        return end($this->aggregateItems);
    }

    /**
     * @return mixed
     */
    public function getFirst()
    {
        return reset($this->aggregateItems);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->aggregateItems);
    }

    public function purge()
    {
        $this->aggregateItems = [];
    }
}