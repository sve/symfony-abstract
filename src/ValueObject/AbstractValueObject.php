<?php

namespace ValueObject;

abstract class AbstractValueObject
{
    /**
     * @var mixed
     */
    public $value;

    /**
     * @param $value
     */
    public function set($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function has()
    {
        return isset($this->value);
    }

    /**
     * @return bool
     */
    public function isNotEmpty()
    {
        return !empty($this->value);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->value);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * @return bool
     */
    public function isBoolean()
    {
        return is_bool($this->value);
    }

    /**
     * @return bool
     */
    public function isBool()
    {
        return $this->isBoolean();
    }

    /**
     * @return bool
     */
    public function isInteger()
    {
        return is_int($this->value);
    }

    /**
     * @return bool
     */
    public function isInt()
    {
        return $this->isInteger();
    }

    /**
     * @return bool
     */
    public function isFloat()
    {
        return is_float($this->value);
    }

    /**
     * @return bool
     */
    public function isString()
    {
        return is_string($this->value);
    }

    /**
     * @return bool
     */
    public function isArray()
    {
        return is_array($this->value);
    }

    /**
     * @return bool
     */
    public function isObject()
    {
        return is_object($this->value);
    }

    /**
     * @return bool
     */
    public function isNull()
    {
        return null === $this->value;
    }
}
