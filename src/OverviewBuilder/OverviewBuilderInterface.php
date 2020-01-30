<?php

namespace Drupal\wmentity_overview\OverviewBuilder;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityListBuilderInterface;
use Drupal\wmentity_overview\Annotation\OverviewBuilder;
use Drupal\wmentity_overview\Common\ColumnInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface OverviewBuilderInterface extends EntityListBuilderInterface
{
    /** The database query used to fetch the entity id's. */
    public function getQuery(): SelectInterface;

    /**
     * An array of columns for the overview table header.
     *
     * @return ColumnInterface[]
     */
    public function getColumns(): array;

    /** Information about the overview builder. */
    public function getDefinition(): OverviewBuilder;

    /**
     * Instantiates a new instance of this overview builder.
     *
     * This is a factory method that returns a new instance of this object. The
     * factory should pass any needed dependencies into the constructor of this
     * object, but not the container itself. Every call to this method must return
     * a new instance of this object; that is, it may not implement a singleton.
     *
     * @param ContainerInterface $container
     *   The service container this object should use.
     * @param OverviewBuilder $definition
     *   The definition containing metadata from the annotation.
     *
     * @return static
     *   A new instance of the overview builder.
     */
    public static function create(ContainerInterface $container, OverviewBuilder $definition);
}
