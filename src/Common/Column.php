<?php

namespace Drupal\wmentity_overview\Common;

use Drupal\Component\Render\MarkupInterface;

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
    /** @var string|null */
    protected $sortField;

    public function __construct(
        string $name,
        $label = null,
        bool $sortable = true,
        ?string $defaultSortDirection = null,
        ?string $sortField = null
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->sortable = $sortable;
        $this->defaultSortDirection = $defaultSortDirection;
        $this->sortField = $sortField;
    }

    public static function create(string $name): ColumnInterface
    {
        return new static($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $value): ColumnInterface
    {
        $this->name = $value;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($value): ColumnInterface
    {
        $this->label = $value;
        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function setSortable(bool $value = true): ColumnInterface
    {
        $this->sortable = $value;
        return $this;
    }

    public function getDefaultSortDirection(): ?string
    {
        return $this->defaultSortDirection;
    }

    public function setDefaultSortDirection(?string $value): ColumnInterface
    {
        $this->defaultSortDirection = $value;
        return $this;
    }

    public function getSortField(): ?string
    {
        return $this->sortField ?? $this->getName();
    }

    public function setSortField(?string $value): ColumnInterface
    {
        $this->sortField = $value;
        return $this;
    }
}
