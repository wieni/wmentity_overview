<?php

namespace Drupal\wmentity_overview\OverviewBuilder\Traits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * @mixin EntityListBuilder
 * @property EntityTypeManagerInterface $entityTypeManager
 */
trait BundleEntityTrait
{
    /** @return EntityInterface[] */
    protected function getBundleEntities(): array
    {
        $bundleEntityType = $this->entityType->getBundleEntityType();

        if (!$bundleEntityType) {
            return [];
        }

        return $this->entityTypeManager
            ->getStorage($bundleEntityType)
            ->loadMultiple();
    }

    protected function getBundleEntity(string $bundle): ?EntityInterface
    {
        $bundleEntityType = $this->entityType->getBundleEntityType();

        if (!$bundleEntityType) {
            return null;
        }

        return $this->entityTypeManager
            ->getStorage($bundleEntityType)
            ->load($bundle);
    }
}
