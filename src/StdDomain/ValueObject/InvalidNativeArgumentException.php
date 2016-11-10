<?php

namespace StdDomain\ValueObject;


class InvalidNativeArgumentException extends \InvalidArgumentException
{
    /**
     * @var string
     */
    private $errorCode;

    /**
     * InvalidNativeArgumentException constructor.
     * @param string $message
     * @param string $errorCode
     */
    public function __construct($message, $errorCode)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}