<?php

namespace Drupal\wmentity_overview\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\wmentity_overview\Annotation\OverviewBuilder;
use Drupal\wmentity_overview\Controller\EntityOverviewController;
use Drupal\wmentity_overview\OverviewBuilder\OverviewBuilderManager;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class CollectionRouteSubscriber extends RouteSubscriberBase
{
    /** @var OverviewBuilderManager */
    protected $overviewBuilders;

    public function __construct(
        OverviewBuilderManager $overviewBuilders
    ) {
        $this->overviewBuilders = $overviewBuilders;
    }

    public static function getSubscribedEvents()
    {
        // Run before Drupal\Core\EventSubscriber/ParamConverterSubscriber
        $events[RoutingEvents::ALTER] = ['onAlterRoutes', -180];

        return $events;
    }

    protected function alterRoutes(RouteCollection $collection): void
    {
        $definitions = $this->overviewBuilders->getDefinitionsByRouteName();

        foreach ($collection as $routeName => $route) {
            $defaults = $route->getDefaults();

            if (empty($defaults['_controller']) && !empty($defaults['_entity_overview'])) {
                $definition = new OverviewBuilder(
                    $this->overviewBuilders->getDefinition($defaults['_entity_overview'])
                );
                $this->addOverviewBuilder($route, $definition);
            }

            foreach ($definitions[$routeName] ?? [] as $definition) {
                $this->addOverviewBuilder($route, $definition);
            }
        }
    }

    protected function addOverviewBuilder(Route $route, OverviewBuilder $builder): void
    {
        $defaults = $route->getDefaults();
        $defaults['_controller'] = EntityOverviewController::class . '::show';
        $defaults['builder'] = $builder->getId();
        $defaults['entity_type'] = $builder->getEntityTypeId();

        $options = $route->getOptions();
        $options['parameters']['builder']['type'] = 'wmentity_overview';

        $route->setDefaults($defaults);
        $route->setOptions($options);
    }
}
