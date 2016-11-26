<?php

namespace StdDomain\Hydrator;

use StdDomain\Reflection\ReflectionManager;
use StdDomain\ValueObject\ValueObjectInterface;
use Zend\Hydrator\HydratorInterface;

class ValueObject implements HydratorInterface
{
    public function hydrate(array $data, $object)
    {
        throw new \BadMethodCallException("Hydrate not supported for " . __CLASS__);
    }

    public function extract($object)
    {
        $properties = ReflectionManager::getReflectedProperties($object);
        if(count($properties) == 0) {
            return [];
        }

        $rows = [];
        foreach ($properties as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);
            if (($value = $reflectionProperty->getValue($object)) instanceof ValueObjectInterface) {
                $rows[$reflectionProperty->getName()] = $value->toNative();
            }
        }
        return $rows;
    }
}