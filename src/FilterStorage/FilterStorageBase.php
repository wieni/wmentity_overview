<?php

namespace Drupal\wmentity_overview\FilterStorage;

use Drupal\Core\Entity\EntityTypeInterface;

abstract class FilterStorageBase implements FilterStorageInterface, \ArrayAccess, \IteratorAggregate
{
    /** @var EntityTypeInterface */
    protected $entityType;

    public function offsetExists($offset)
    {
        return $this->offsetGet($offset) !== null;
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    public function getIterator()
    {
        return $this->getAll();
    }

    public function setEntityType(EntityTypeInterface $entityType): FilterStorageInterface
    {
        $this->entityType = $entityType;

        return $this;
    }
}
