<?php

namespace Drupal\wmentity_overview\OverviewBuilder;

interface BulkActionOverviewBuilderInterface extends OverviewBuilderInterface
{
    /**
     * @return array
     *   An array of actions plugins that should be displayed in the bulk action dropdown.
     *   The key should be the plugin ID and the value should be the label. You can also
     *   choose to only provide a value, in which case the default action label will be used.
     *
     * @see \Drupal\Core\Action\ActionInterface
     */
    public function getBulkActionPlugins(): array;
}
