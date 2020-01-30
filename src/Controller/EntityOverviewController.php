<?php

namespace Drupal\wmentity_overview\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\wmentity_overview\Event\EntityOverviewAlterEvent;
use Drupal\wmentity_overview\OverviewBuilder\OverviewBuilderInterface;
use Drupal\wmentity_overview\OverviewBuilder\OverviewBuilderManager;
use Drupal\wmentity_overview\WmEntityOverviewEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EntityOverviewController implements ContainerInjectionInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var OverviewBuilderManager */
    protected $overviewBuilders;

    public static function create(ContainerInterface $container)
    {
        $instance = new static;
        $instance->eventDispatcher = $container->get('event_dispatcher');
        $instance->overviewBuilders = $container->get('plugin.manager.wmentity_overview_builder');

        return $instance;
    }

    public function show(OverviewBuilderInterface $builder)
    {
        $definition = $builder->getDefinition();

        if ($definition->isOverride() && $alternatives = $this->overviewBuilders->getAlternativesByFilters($definition)) {
            $builder = $this->overviewBuilders->createInstance(reset($alternatives)->getId());
        }

        $overview = $builder->render();

        $this->eventDispatcher->dispatch(
            WmEntityOverviewEvents::ENTITY_OVERVIEW_ALTER,
            new EntityOverviewAlterEvent($definition, $overview)
        );

        return $overview;
    }
}
