<?php

namespace Drupal\wmentity_overview\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\wmentity_overview\Annotation\OverviewBuilder;

class EntityOverviewAlternativesAlterEvent extends Event
{
    /** @var OverviewBuilder */
    protected $definition;
    /** @var OverviewBuilder[] */
    protected $alternatives;

    public function __construct(
        OverviewBuilder $definition,
        array &$alternatives
    ) {
        $this->definition = $definition;
        $this->alternatives = &$alternatives;
    }

    public function getDefinition(): OverviewBuilder
    {
        return $this->definition;
    }

    public function getAlternatives(): array
    {
        return $this->alternatives;
    }

    public function setAlternatives(array $alternatives): self
    {
        $this->alternatives = $alternatives;
        return $this;
    }
}
