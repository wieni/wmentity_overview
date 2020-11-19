<?php

namespace Drupal\wmentity_overview\OverviewBuilder\Traits;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\user\EntityOwnerInterface;

/**
 * @mixin EntityListBuilder
 * @mixin BundleEntityTrait
 */
trait ColumnBuilderTrait
{
    protected function buildLabelColumn(EntityInterface $entity, ?string $rel = 'canonical'): array
    {
        if ($entity->hasLinkTemplate($rel) && $entity->toUrl($rel)->access()) {
            $data = $entity->toLink(null, $rel)->toRenderable();
        } else {
            $data = ['#plain_text' => $entity->label()];
        }

        return ['data' => $data];
    }

    protected function buildBundleColumn(EntityInterface $entity): array
    {
        return [
            'data' => [
                '#plain_text' => $this->getBundleEntity($entity->bundle())->label(),
            ],
        ];
    }

    protected function buildChangedColumn(EntityChangedInterface $entity): array
    {
        return [
            'data' => [
                '#plain_text' => date('d/m/Y H:i', $entity->getChangedTime()),
            ],
        ];
    }

    protected function buildCreatedColumn(EntityInterface $entity): array
    {
        return [
            'data' => [
                '#plain_text' => date('d/m/Y H:i', $entity->getCreatedTime()),
            ],
        ];
    }

    protected function buildOperationsColumn(EntityInterface $entity): array
    {
        return [
            'data' => $this->buildOperations($entity),
        ];
    }

    protected function buildOwnerColumn(EntityOwnerInterface $entity): array
    {
        return [
            'data' => [
                '#plain_text' => $entity->getOwner()->getDisplayName(),
            ],
        ];
    }

    protected function buildTextColumn($text): array
    {
        return [
            'data' => [
                '#plain_text' => $text,
            ],
        ];
    }
}
