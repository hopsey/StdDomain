<?php

namespace StdDomain\ValueObject;

interface ValueObjectInterface
{
    /**
     * @return ValueObjectInterface
     */
    public static function fromNative();

    /**
     * @return mixed
     */
    public function toNative();

    /**
     * @return string
     */
    public function __toString();
}