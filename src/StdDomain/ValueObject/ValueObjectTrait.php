<?php

namespace StdDomain\ValueObject;

/**
 * Prosta cecha skupiająca storage i obsługe prostych typow ValueObjects
 */
trait ValueObjectTrait
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @return static
     */
    public static function fromNative()
    {
        $value = \func_get_arg(0);
        return new static($value);
    }

    /**
     * @return mixed
     */
    public function toNative()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->toNative();
    }

    public function __construct($value)
    {
        $this->value = $value;
    }
}