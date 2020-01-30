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
    /** @var string */
    protected $sortDirection;

    public function __construct(
        string $name,
        $label = null,
        bool $sortable = true,
        string $sortDirection = TableSort::DESC
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->sortable = $sortable;
        $this->sortDirection = $sortDirection;
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

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }
}
