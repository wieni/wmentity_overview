<?php

namespace Drupal\wmentity_overview\OverviewBuilder;

use Drupal\wmentity_overview\Annotation\OverviewBuilder;
use Drupal\wmentity_overview\FilterStorage\FilterStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class FilterableOverviewBuilderBase extends OverviewBuilderBase implements FilterableOverviewBuilderInterface
{
    use FilterableOverviewBuilderTrait;

    /** @var FilterStorageInterface */
    protected $filters;

    public static function create(
        ContainerInterface $container,
        OverviewBuilder $definition
    ) {
        $instance = parent::create($container, $definition);
        $instance->filters = $container->get('plugin.manager.wmentity_overview_filter_storage')
            ->createInstance($definition->getFilterStorageId())
            ->setEntityType($instance->entityType);

        return $instance;
    }
}
