<?php

namespace Drupal\wmentity_overview\Event;

use Drupal\wmentity_overview\Annotation\OverviewBuilder;
use Symfony\Component\EventDispatcher\Event;

class EntityOverviewAlterEvent extends Event
{
    /** @var OverviewBuilder */
    protected $definition;
    /** @var array */
    protected $overview;

    public function __construct(
        OverviewBuilder $definition,
        array &$overview
    ) {
        $this->definition = $definition;
        $this->overview = &$overview;
    }

    public function getDefinition(): OverviewBuilder
    {
        return $this->definition;
    }

    public function &getOverview(): array
    {
        return $this->overview;
    }
}
