<?php
/**
 * Created by PhpStorm.
 * User: tomaszchmielewski
 * Date: 09/11/16
 * Time: 12:54
 */

namespace StdDomain\Entity;

use StdDomain\Reflection\ReflectionManager;
use StdDomain\ValueObject\Factory;
use StdDomain\ValueObject\Factory\ValueObjectBuilderError;
use StdDomain\ValueObject\ValueObjectInterface;

class EntityFactory
{
    private static function implementsVOInterface($className)
    {
        return ReflectionManager::isSubclassOf($className, ValueObjectInterface::class);
    }

    private static function implementsAggregateInterface($className)
    {
        return ReflectionManager::isSubclassOf($className, AggregateInterface::class);
    }

    public static function buildParams($entityClass, array $data, ValueObjectBuilderError $errors = null, $ignoreNotExisting = false)
    {
        $properties = ReflectionManager::getReflectedConstructorParams($entityClass);
        $invokeArguments = [];
        $isError = false;
        foreach ($properties as $property) {
            $name = $property->getName();

            if ($ignoreNotExisting === true && !array_key_exists($name, $data)) {
                continue;
            }

            $type = $property->getType();
            $value = @$data[$name];

            if ((string)$type == "" || (!self::implementsVOInterface((string)$type) && !(self::implementsAggregateInterface((string)$type)))
                || (is_object($value) && get_class($value) == (string)$type)
            ) {
                $invokeArguments[$name] = $value;
            } elseif(self::implementsAggregateInterface((string)$type)) {
                /** @var AggregateInterface $aggregate */
                $typeStr = (string)$type;
                $aggregate = new $typeStr;
                if (is_array($value) && count($value) > 0) {
                    foreach ($value as $rowKey => $row) {
                        if (!is_array($row)) {
                            continue;
                        }
                        $subErrors = new ValueObjectBuilderError();
                        $built = self::buildParams($aggregate->getAggregateElementClass(), $row, $subErrors);

                        if ($subErrors->hasRegisteredErrors()) {
                            foreach ($subErrors->getRegisteredErrors() as $namespace => $errorsObj) {
                                foreach ($errorsObj as $errorCode => $errorDesc) {
                                    $errors->registerError($name . "__" . $rowKey . "__" . $namespace, $errorCode, $errorDesc);
                                }
                            }
                        }

                        if ($built !== false) {
                            try {
                                $builtInstance = self::buildFromParams($aggregate->getAggregateElementClass(), $built);
                            } catch (\TypeError $e) {
                                continue;
                            }
                            if ($builtInstance instanceof EntityInterface) {
                                $aggregate->addItem($builtInstance);
                            }
                        }
                    }
                }
                $invokeArguments[$name] = $aggregate;
            } else {
                try {
                    $builtVo = null;
                    $invokeArguments[$name] = $property->isDefaultValueAvailable() && $property->getDefaultValue() === null && $value === null ? null :
                        ($builtVo = Factory::build($type, $value, $errors, $name));
                    if ($builtVo === false) {
                        $isError = true;
                    }
                } catch (Factory\ValueObjectBuildException $e) {
                    $errors->registerError($name, 'buildError', 'You are required to supply full data in order to create single property');
                }
            }
        }

        if ($isError) {
            return false;
        }
        return $invokeArguments;
    }

    /**
     * @param $entityClass
     * @param array $data
     * @param ValueObjectBuilderError|null $errors
     * @return mixed
     */
    public static function build($entityClass, array $data, ValueObjectBuilderError $errors = null)
    {
        return self::buildFromParams($entityClass, self::buildParams($entityClass, $data, $errors));
    }

    public static function buildFromParams($entityClass, $params)
    {
        return ReflectionManager::getReflectedClass($entityClass)->newInstanceArgs($params);
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