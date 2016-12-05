<?php
/**
 * Created by PhpStorm.
 * User: tomaszchmielewski
 * Date: 10/11/16
 * Time: 10:47
 */

namespace StdDomain\ValueObject\Factory;


/**
 * Class ValueObjectBuilderError
 * @package KontomatikApi\ValueObject\Factory
 */
class ValueObjectBuilderError
{
    private $namespaces = [];

    public function registerError($namespace, $code, $message)
    {
        if (!array_key_exists($namespace, $this->namespaces)) {
            $this->namespaces[$namespace] = [];
        }
        $this->namespaces[$namespace][$code] = $message;
    }

    public function hasRegisteredErrors()
    {
        return count($this->namespaces) > 0;
    }

    public function getRegisteredErrors()
    {
        return $this->namespaces;
    }

    public function toArray()
    {
        if (count($this->namespaces) == 0) {
            return [];
        }
        $arr = [];

        foreach ($this->namespaces as $namespace => $exceptions) {
            $temp = &$arr;
            foreach (explode("__", $namespace) as $key) {
                $temp = &$temp[$key];
            }
            $temp = [];
            foreach ($exceptions as $exceptionCode => $exceptionMessage) {
                $temp[$exceptionCode] = $exceptionMessage;
            }
        }
        return $arr;
    }
}