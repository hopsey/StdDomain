<?php
/**
 * Created by PhpStorm.
 * User: tomaszchmielewski
 * Date: 10/11/16
 * Time: 10:38
 */

namespace StdDomain\ValueObject;

use MabeEnum\Enum as MEnum;
use StdDomain\Reflection\ReflectionManager;
use StdDomain\ValueObject\Factory\ValueObjectBuilderError;

class Factory
{
    public static function build($valueObjectClass, $data, ValueObjectBuilderError $error = null, $namespace = "")
    {
        $valueObjectClass = (string)$valueObjectClass;
        if (!class_exists($valueObjectClass)) {
            throw new \InvalidArgumentException("Value object class " . $valueObjectClass . " does not exist");
        }

        $params = ReflectionManager::getReflectedConstructorParams($valueObjectClass);

        $invokeParams = [];

        $paramsCount = is_array($data) ? count($data) : 1;

        if (ReflectionManager::getReflectedConstructor($valueObjectClass)->getNumberOfRequiredParameters() > $paramsCount) {
            throw new ValueObjectBuildException($valueObjectClass . " constructor expects " . count($params) . " " .
                "parameters, " . $paramsCount . " given.");
        }

        $argsRefined = ReflectionManager::getReflectedClass($valueObjectClass)->hasMethod('refineRequiredArgs') ?
            $valueObjectClass::refineRequiredArgs($data) : false;

        foreach ($params as $param) {
            $paramName = $param->getName();
            if (isset($argsRefined[$paramName]) && $argsRefined[$paramName] === false) {
                continue;
            }
            if (is_array($data)) {
                if (array_key_exists($paramName, $data)) {
                    $value = $data[$paramName];
                } else {
                    $value = null;
                }
            } else {
                $value = $data;
            }

            $paramType = ($param->getType() === null ? null : (string)$param->getType());

            $invokeParams[] = ($paramType === null ? $value : self::build($paramType, $value, $error, $namespace . "__" . $paramName));
        }

        try {
            // wyjatkowo inaczej dla enumow
            if (ReflectionManager::getReflectedClass($valueObjectClass)->isSubclassOf(MEnum::class)) {
                $result = $valueObjectClass::fromNative(current($invokeParams));
            } else {
                $result = ReflectionManager::getReflectedClass($valueObjectClass)->newInstanceArgs($invokeParams);
            }
        } catch (InvalidNativeArgumentException $e) {
            if ($error !== null) {
                $error->registerError($namespace, $e->getErrorCode(), $e->getMessage());
            }
            $result = false;
//        } catch (InvalidNativeArgumentsException $e) {
//            if ($error !== null) {
//                foreach ($e->getExceptions() as $field => $exception) {
//                    $error->registerError($namespace . "__" . $field, $exception->getErrorCode(), $exception->getMessage());
//                }
//            }
//            $result = null;
        } catch (\TypeError $e) {
            $result = false;
        }
        return $result;
    }
}