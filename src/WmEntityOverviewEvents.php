<?php

namespace Drupal\wmentity_overview;

final class WmEntityOverviewEvents
{
    /**
     * Will be triggered after the render array is built.
     *
     * The event object is an instance of
     * @uses \Drupal\wmentity_overview\Event\EntityOverviewAlterEvent
     */
    public const ENTITY_OVERVIEW_ALTER = 'wmentity_overview.entity_overview.alter';
}
