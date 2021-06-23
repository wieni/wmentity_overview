<?php

namespace Drupal\wmentity_overview\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\wmentity_overview\OverviewBuilder\OverviewBuilderInterface;
use Drupal\wmentity_overview\OverviewBuilder\OverviewBuilderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityOverviewController implements ContainerInjectionInterface
{
    /** @var ModuleHandlerInterface */
    protected $moduleHandler;
    /** @var OverviewBuilderManager */
    protected $overviewBuilders;

    public function __construct(
        ModuleHandlerInterface $moduleHandler,
        OverviewBuilderManager $overviewBuilders
    ) {
        $this->moduleHandler = $moduleHandler;
        $this->overviewBuilders = $overviewBuilders;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('module_handler'),
            $container->get('plugin.manager.wmentity_overview_builder')
        );
    }

    public function show(OverviewBuilderInterface $builder)
    {
        $definition = $builder->getDefinition();

        if ($definition->isOverride() && $alternatives = $this->overviewBuilders->getAlternatives($definition)) {
            $builder = $this->overviewBuilders->createInstance(reset($alternatives)->getId());
        }

        $overview = $builder->render();

        $this->moduleHandler->alter('entity_overview', $overview, $builder);

        return $overview;
    }
}
