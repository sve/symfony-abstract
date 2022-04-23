<?php

namespace Entity;

use ArrayAccess;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Facade\EntityFacade;
use ValueObject\AbstractValueObject;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Inflector\EnglishInflector;

abstract class AbstractEntity extends AbstractValueObject implements ArrayAccess
{
    /**
     * @ORM\Id()
     */
    public $id;

    /**
     * @return mixed|void|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param $name
     * @param array $arguments
     * @return $this|mixed|void|null
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function __call($name, array $arguments)
    {
        $arguments = reset($arguments);
        if (EntityFacade::isMethodSetter($name)) {
            if ($arguments instanceof ArrayCollection) {
                $arguments = $arguments->toArray();
            }

            $propertyName = EntityFacade::makePropertyNameFromSetterMethodName($name);
            $entityName = EntityFacade::makeEntityNameFromEntityClassName(static::class);
            $reflectionClass = new \ReflectionClass($this);

            if (is_array($arguments)) {
                $possibleProperty = key($arguments);
                if ($reflectionClass->hasProperty($possibleProperty)) {
                    $this->id = $propertyName;
                    $setter = sprintf('set%s', ucfirst($possibleProperty));
                    $this->{$setter}($arguments[$possibleProperty]);

                    return $this;
                }
            } elseif ($reflectionClass->hasProperty($propertyName)) {
                $this->id = $propertyName;
                $setter = sprintf('set%s', ucfirst($propertyName));
                $this->{$setter}($arguments);

                return $this;
            } elseif (EntityFacade::isPropertyId($propertyName)) {
                $this->id = $arguments;

                return $this;
            }

            if (!$reflectionClass->hasProperty($propertyName)) {
                if ($propertyName !== lcfirst($entityName)) {
                    $this->id = $propertyName;
                }
                $this->set($arguments);

                return $this;
            }

            $reflectionProperty = $reflectionClass->getProperty($propertyName);
            $reader = new AnnotationReader();
            $propertyAnnotation = $reader->getPropertyAnnotation(
                $reflectionProperty,
                ORM\Annotation::class
            );

            if (!isset($propertyAnnotation->targetEntity)) {
                return $this;
            }

            $entityClass = sprintf('%s\%s', __NAMESPACE__, $propertyAnnotation->targetEntity);
            foreach ($arguments as $id => $value) {
                /**
                 * @var \Entity\AbstractEntity $entity
                 */
                $entity = new $entityClass;
                $entity->id = $id;
                $entity->set($value);

                if ($reflectionClass->hasProperty($propertyName)) {
                    $inflector = new EnglishInflector();
                    $entityProperty = new ArrayCollection($inflector->singularize($propertyName));
                    $entityProperty = sprintf('%s_id', $entityProperty->last());
                    $entity->{$entityProperty} = $this->id;
                }

                $arguments[$id] = $entity;
            }

            if ($reflectionProperty->isPrivate()) {
                $reflectionProperty->setAccessible(true);
                if (!$reflectionProperty->getValue($this)) {
                    $reflectionProperty->setValue($this, new ArrayCollection($arguments));
                } else {
                    $value = $reflectionProperty->getValue($this);
                    $value = $value->add($arguments);
                    $reflectionProperty->setValue($this, $value);
                }
            } else {
                if (!$this->{$propertyName}) {
                    $this->{$propertyName} = new ArrayCollection($arguments);
                } else {
                    $this->{$propertyName} = $this->{$propertyName}->add($arguments);
                }
            }
        } elseif (EntityFacade::isMethodGetter($name)) {
            $propertyName = lcfirst(substr($name, 3));
            return $this->{$propertyName};
        }
    }

    /**
     * @param $id
     * @param $value
     * @return void
     */
    public function __set($id, $value)
    {
        $this->{$id} = $value;
    }

    /**
     * @param $id
     * @return mixed|void|null
     * @throws \ReflectionException
     */
    public function __get($id)
    {
        if (isset($this->{$id})) {
            return $this->{$id};
        }

        $reflectionClass = new \ReflectionClass($this);

        if (!$reflectionClass->hasProperty($id)) {
            return null;
        }


        $reflectionProperty = $reflectionClass->getProperty($id);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($this);
    }

    /**
     * @param $id
     * @return bool
     */
    public function __isset($id)
    {
        return isset($this->{$id});
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        if ($this->__isset($offset)) {
            unset($this->{$offset});
        }
    }

    /**
     * @param mixed $offset
     * @return mixed|void|null
     * @throws \ReflectionException
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * @param $data
     * @return array
     */
    public function objectToArray($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = [];
            foreach ($data as $key => $value)
            {
                if ($key === 'id') {
                    $result[$key] = (string) $value;
                } else {
                    $result[$key] = (is_array($data) || is_object($data)) ? $this->objectToArray($value) : $value;
                }
            }
            return $result;
        }
        return $data;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->objectToArray($this);
    }
}
