<?php

namespace Drupal\wmentity_overview\Plugin\Factory;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\wmentity_overview\Annotation\OverviewBuilder;
use Drupal\wmentity_overview\OverviewBuilder\OverviewBuilderInterface;

class OverviewBuilderPluginFactory extends DefaultFactory
{
    public function createInstance($pluginId, array $configuration = [])
    {
        $pluginDefinition = $this->discovery->getDefinition($pluginId);
        $pluginClass = static::getPluginClass($pluginId, $pluginDefinition, $this->interface);

        if (!is_subclass_of($pluginClass, OverviewBuilderInterface::class)) {
            throw new \RuntimeException('The OverviewBuilderPluginFactory factory should only be used for OverviewBuilder plugins.');
        }

        $definition = new OverviewBuilder($pluginDefinition);

        return $pluginClass::create(\Drupal::getContainer(), $definition);
    }
}
