<?php

namespace Drupal\wmentity_overview\FilterStorage;

use ArrayIterator;
use Drupal\Core\Entity\EntityTypeInterface;

abstract class FilterStorageBase implements FilterStorageInterface, \ArrayAccess, \IteratorAggregate
{
    /** @var EntityTypeInterface */
    protected $entityType;

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return $this->offsetGet($offset) !== null;
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->getAll());
    }

    public function setEntityType(EntityTypeInterface $entityType): FilterStorageInterface
    {
        $this->entityType = $entityType;

        return $this;
    }
}
