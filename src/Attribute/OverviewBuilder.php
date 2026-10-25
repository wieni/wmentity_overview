<?php

namespace Drupal\wmentity_overview\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Attribute counterpart of the OverviewBuilder annotation.
 *
 * Takes the same properties, with the same defaults. Plugins still receive
 * their definition as an \Drupal\wmentity_overview\Annotation\OverviewBuilder.
 *
 * @see \Drupal\wmentity_overview\Annotation\OverviewBuilder
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class OverviewBuilder extends Plugin
{
    public function __construct(
        string $id,
        public readonly string $entity_type,
        public readonly bool $override = false,
        public readonly ?string $bundle = null,
        public readonly ?string $route_name = null,
        public readonly array $filters = [],
        public readonly string $filter_storage = 'query_param',
        ?string $deriver = null,
    ) {
        parent::__construct($id, $deriver);
    }
}
