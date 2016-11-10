<?php
/**
 * Created by PhpStorm.
 * User: tomaszchmielewski
 * Date: 10/11/16
 * Time: 10:04
 */

namespace StdDomain\Reflection;

/**
 * TODO zaimplementowac storage na cache - na teraz niepotrzebne
 * Class ReflectionManager
 * @package KontomatikApi\Reflection
 */
class ReflectionManager
{
    private static $reflectedEntities = [];

    /**
     * @param $className
     * @return array
     */
    private static function loadCache($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Entity class " . $className . " does not exist");
        }

        if (!array_key_exists($className, self::$reflectedEntities)) {
            $class = self::$reflectedEntities[$className] = new \ReflectionClass($className);
            $constructor = $class->getConstructor();
            $parameters = $constructor->getParameters();
            $properties = $class->getProperties();

            self::$reflectedEntities[$className] = [
                'constructor' => $constructor,
                'class' => $class,
                'params' => $parameters,
                'properties' => $properties
            ];
        }
        return self::$reflectedEntities[$className];
    }

    /**
     * @param $className
     * @return \ReflectionClass
     */
    public static function getReflectedClass($className)
    {
        return self::loadCache($className)['class'];
    }

    /**
     * @param $className
     * @return \ReflectionMethod
     */
    public static function getReflectedConstructor($className)
    {
        return self::loadCache($className)['constructor'];
    }

    /**
     * @param $className
     * @return \ReflectionParameter[]
     */
    public static function getReflectedConstructorParams($className)
    {
        return self::loadCache($className)['params'];
    }

    /**
     * @param $className
     * @return \ReflectionProperty[]
     */
    public static function getReflectedProperties($className)
    {
        return self::loadCache($className)['properties'];
    }
}