<?php

namespace Drupal\wmentity_overview\Common;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Utility\TableSort;

class Column implements ColumnInterface
{
    /** @var string */
    protected $name;
    /** @var MarkupInterface|string|null */
    protected $label;
    /** @var bool */
    protected $sortable;
    /** @var string|null */
    protected $defaultSortDirection;

    public function __construct(
        string $name,
        $label = null,
        bool $sortable = true,
        ?string $defaultSortDirection = null
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->sortable = $sortable;
        $this->defaultSortDirection = $defaultSortDirection;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function getDefaultSortDirection(): ?string
    {
        return $this->defaultSortDirection;
    }
}
