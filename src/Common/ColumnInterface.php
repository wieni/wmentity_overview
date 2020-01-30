<?php

namespace Drupal\wmentity_overview\Common;

interface ColumnInterface
{
    /** The name of the database field to sort on. */
    public function getName(): string;

    /** The localized title of the table column. */
    public function getLabel();

    /** Whether the field should be sortable in the table. */
    public function isSortable(): bool;

    /** The default sort direction, if the field is sortable. */
    public function getSortDirection(): string;
}
