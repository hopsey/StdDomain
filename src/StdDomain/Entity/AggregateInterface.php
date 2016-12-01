<?php
/**
 * Created by PhpStorm.
 * User: tomaszchmielewski
 * Date: 01/12/16
 * Time: 12:47
 */

namespace StdDomain\Entity;

/**
 * Interfejs agregata.
 * Interface AggregateInterface
 * @package StdDomain\Entity
 */
interface AggregateInterface extends \IteratorAggregate, \Countable
{
    /**
     * Zwraca klasę pojedynczego elementu
     * @return string
     */
    public function getAggregateElementClass();

    /**
     * Dodaje element do agregatu
     * @param EntityInterface $item
     * @return bool
     */
    public function addItem(EntityInterface $item);
}