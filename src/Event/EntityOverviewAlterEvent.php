<?php

namespace Drupal\wmentity_overview\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\wmentity_overview\Annotation\OverviewBuilder;
use Drupal\wmentity_overview\OverviewBuilder\OverviewBuilderInterface;

class EntityOverviewAlterEvent extends Event
{
    /** @var OverviewBuilderInterface */
    protected $builder;
    /** @var array */
    protected $overview;

    public function __construct(
        OverviewBuilderInterface $builder,
        array &$overview
    ) {
        $this->builder = $builder;
        $this->overview = &$overview;
    }

    public function getBuilder(): OverviewBuilderInterface
    {
        return $this->builder;
    }

    public function getDefinition(): OverviewBuilder
    {
        return $this->builder->getDefinition();
    }

    public function &getOverview(): array
    {
        return $this->overview;
    }
}
