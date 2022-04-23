<?php

namespace Facade;

use Doctrine\Common\Collections\ArrayCollection;
use function Symfony\Component\String\s;

class EntityFacade
{
    /**
     * @param string $methodName
     * @return bool
     */
    public static function isMethodSetter($methodName)
    {
        return s($methodName)->startsWith('set');
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public static function isMethodGetter($methodName)
    {
        return s($methodName)->startsWith('get');
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public static function isMethodHasser($methodName)
    {
        return s($methodName)->startsWith('has');
    }

    public static function isArrayCollection($value)
    {
        return $value instanceof ArrayCollection;
    }

    public static function makePropertyNameFromSetterMethodName($methodName)
    {
        return lcfirst(substr($methodName, 3));
    }

    public static function isPropertyId($propertyName)
    {
        return $propertyName === 'id';
    }

    public static function makeEntityClassFromEntityName($entityName)
    {
        return sprintf('Entity\%s', $entityName);
    }

    /**
     * @param string $className
     * @return bool|string
     */
    public static function makeEntityNameFromEntityClassName($className)
    {
        return basename(str_replace('\\', '/', $className));
    }
}
