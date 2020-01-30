<?php

namespace Drupal\wmentity_overview\FilterStorage;

use Drupal\Core\Entity\EntityTypeInterface;

interface FilterStorageInterface
{
    public function getAll(): array;

    public function get(string $key);

    public function set(string $key, $value): self;

    public function remove(string $key): void;

    public function reset(): void;

    public function setEntityType(EntityTypeInterface $entityType): self;
}
