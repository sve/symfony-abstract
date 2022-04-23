<?php

namespace Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

class AbstractRepository extends EntityRepository
{
    /**
     * @var string
     */
    public $entityName;

    /**
     * @var string
     */
    public $entityClassName;

    /**
     * @var ClassMetadata
     */
    public $classMetadata;

    /**
     * @var EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityName = $this->buildEntityNameFromRepositoryClassName(static::class);
        $this->entityClassName = $this->buildEntityClassNameFromEntityName($this->entityName);
        $this->classMetadata = $entityManager->getClassMetadata($this->entityClassName);

        parent::__construct($entityManager, $this->classMetadata);
    }

    /**
     * @param $entities
     * @return mixed
     */
    public function create($entities)
    {
        $this->persistMultipleEntities($entities);
        $this->_em->flush();

        return $entities;
    }

    /**
     * @param $entities
     * @return mixed
     */
    public function update($entities)
    {
        $this->persistMultipleEntities($entities);
        $this->_em->flush();

        return $entities;
    }

    /**
     * @param $entities
     * @return void
     */
    public function remove($entities)
    {
        $this->removeMultipleEntities($entities);
        $this->_em->flush();
    }

    /**
     * @param $entities
     * @return void
     */
    protected function persistMultipleEntities($entities)
    {
        if (is_array($entities) || $entities instanceof ArrayCollection) {
            foreach ($entities as $entity) {
                $this->_em->persist($entity);
            }

            return;
        }

        $this->_em->persist($entities);
    }

    /**
     * @param $entities
     * @return void
     */
    protected function removeMultipleEntities($entities)
    {
        if (is_array($entities) || $entities instanceof ArrayCollection) {
            foreach ($entities as $entity) {
                $this->_em->remove($entity);
            }

            return;
        }

        $this->_em->remove($entities);
    }

    /**
     * @param $entityName
     * @return string
     */
    protected function buildEntityClassNameFromEntityName($entityName)
    {
        return sprintf('Entity\\%s', $entityName);
    }

    /**
     * @param $className
     * @return bool|string
     */
    protected function buildEntityNameFromRepositoryClassName($className)
    {
        $basename = basename(str_replace('\\', '/', $className));

        return substr($basename, 0, -strlen('Repository'));
    }
}
