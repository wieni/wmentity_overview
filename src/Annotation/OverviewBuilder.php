<?php

namespace Drupal\wmentity_overview\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * @Annotation
 */
class OverviewBuilder extends Plugin
{
    public const TYPE_CUSTOM = 'custom';
    public const TYPE_ENTITY_OVERRIDE = 'entity';
    public const TYPE_ROUTE_OVERRIDE = 'route';

    /** @var string */
    public $entity_type;
    /** @var bool */
    public $override = false;
    /** @var string */
    public $bundle;
    /** @var string */
    public $route_name;
    /** @var string */
    public $id;
    /** @var array */
    public $filters = [];
    /** @var string */
    public $filter_storage = 'query_param';

    public function getType(): string
    {
        if (isset($this->definition['override'])) {
            return self::TYPE_ENTITY_OVERRIDE;
        }

        if (isset($this->definition['route_name'])) {
            return self::TYPE_ROUTE_OVERRIDE;
        }

        return self::TYPE_CUSTOM;
    }

    public function getEntityTypeId(): string
    {
        return $this->definition['entity_type'];
    }

    public function getRouteName(): ?string
    {
        return $this->definition['route_name'] ?? null;
    }

    public function getFilters(): array
    {
        return $this->definition['filters'];
    }

    public function getFilterStorageId(): string
    {
        return $this->definition['filter_storage'];
    }

    public function isOverride(): bool
    {
        return $this->definition['override'];
    }
}
