<?php

use Drupal\wmentity_overview\Annotation\OverviewBuilder;
use Drupal\wmentity_overview\Event\EntityOverviewAlterEvent;
use Drupal\wmentity_overview\WmEntityOverviewEvents;

function wmentity_overview_entity_overview_alter(OverviewBuilder $definition, array $overview)
{
    $dispatcher = \Drupal::getContainer()->get('event_dispatcher');
    $event = new EntityOverviewAlterEvent($definition, $overview);

    $dispatcher->dispatch(WmEntityOverviewEvents::ENTITY_OVERVIEW_ALTER, $event);
}
