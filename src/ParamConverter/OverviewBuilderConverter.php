<?php

namespace Drupal\wmentity_overview\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Drupal\wmentity_overview\OverviewBuilder\OverviewBuilderManager;
use Symfony\Component\Routing\Route;

class OverviewBuilderConverter implements ParamConverterInterface
{
    /** @var OverviewBuilderManager */
    protected $overviewBuilderManager;

    public function __construct(
        OverviewBuilderManager $overviewBuilderManager
    ) {
        $this->overviewBuilderManager = $overviewBuilderManager;
    }

    public function convert($value, $definition, $name, array $defaults)
    {
        return $this->overviewBuilderManager->createInstance($value);
    }

    public function applies($definition, $name, Route $route)
    {
        return !empty($definition['type'])
            && $definition['type'] === 'wmentity_overview';
    }
}
