<?php

namespace Drupal\wmentity_overview\Plugin\Discovery;

use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;

/**
 * For plugin managers that scan whole namespaces for attributes.
 *
 * @see \Drupal\wmentity_overview\Plugin\Discovery\PrefilteredAttributeDiscovery
 */
trait PrefilteredDiscoveryTrait
{
    protected function getDiscovery()
    {
        if (!$this->discovery) {
            $this->discovery = new ContainerDerivativeDiscoveryDecorator(new PrefilteredAttributeDiscovery(
                $this->subdir,
                $this->namespaces,
                $this->pluginDefinitionAttributeName,
                $this->pluginDefinitionAnnotationName,
                $this->additionalAnnotationNamespaces
            ));
        }

        return $this->discovery;
    }
}
