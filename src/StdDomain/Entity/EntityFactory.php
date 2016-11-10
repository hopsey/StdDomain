<?php
/**
 * Created by PhpStorm.
 * User: tomaszchmielewski
 * Date: 09/11/16
 * Time: 12:54
 */

namespace StdDomain\Entity;

use StdDomain\Reflection\ReflectionManager;
use StdDomain\ValueObject\Factory\ValueObjectBuilderError;
use StdDomain\ValueObject\ValueObjectBuildException;

class EntityFactory
{
    private static function implementsVOInterface($className)
    {
        // for performance reasons nie skorzystamy z introspekcji,
        // tylko sprawdzamy czy namespace zawiera ValueObject
        return strpos($className, "\\ValueObject\\") === false ? false : true;
    }

    public static function build($entityClass, array $data, ValueObjectBuilderError $errors = null, $dontInvoke = false)
    {
        $properties = ReflectionManager::getReflectedConstructorParams($entityClass);
        $invokeArguments = [];
        $isError = false;
        foreach ($properties as $property) {
            $name = $property->getName();
            $type = $property->getType();
            if (!array_key_exists($name, $data) && $dontInvoke) {
                continue;
            }
            $value = @$data[$name];

            if (self::implementsVOInterface((string)$type)) {
                try {
                    $builtVo = null;
                    $invokeArguments[$name] = $property->isDefaultValueAvailable() && $property->getDefaultValue() === null && $value === null ? null :
                        ($builtVo = \StdDomain\ValueObject\Factory::build($type, $value, $errors, $name));
                    if ($builtVo === false) {
                        $isError = true;
                    }
                } catch (ValueObjectBuildException $e) {
                    $errors->registerError($name, 'buildError', 'You are required to supply full data in order to create single property');
                }
            } else {
                $invokeArguments[$name] = $value;
            }
        }

        if ($isError) {
            return false;
        }
        return $dontInvoke === true ? $invokeArguments :
            ReflectionManager::getReflectedClass($entityClass)->newInstanceArgs($invokeArguments);
    }

    public function prepareAndInvoke($entityClass, $invokeArguments)
    {
        $properties = ReflectionManager::getReflectedConstructorParams($entityClass);
        $supplyArgs = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            if (!array_key_exists($name, $invokeArguments)) {
                throw new \InvalidArgumentException(__FUNCTION__ . ": No value for property " . $name);
            }
            $supplyArgs[] = $invokeArguments[$name];
        }
        return ReflectionManager::getReflectedClass($entityClass)->newInstanceArgs($supplyArgs);
    }
}