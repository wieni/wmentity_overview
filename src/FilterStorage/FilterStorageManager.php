<?php

namespace Drupal\wmentity_overview\FilterStorage;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wmentity_overview\Annotation\FilterStorage;
use Drupal\wmentity_overview\Attribute\FilterStorage as FilterStorageAttribute;

/**
 * @method FilterStorageInterface createInstance($plugin_id, array $configuration = [])
 */
class FilterStorageManager extends DefaultPluginManager
{
    public function __construct(
        \Traversable $namespaces,
        CacheBackendInterface $cacheBackend,
        ModuleHandlerInterface $moduleHandler
    ) {
        parent::__construct(
            '',
            $namespaces,
            $moduleHandler,
            FilterStorageInterface::class,
            FilterStorageAttribute::class,
            FilterStorage::class
        );
        $this->alterInfo('wmentity_overview_filter_storage_info');
        $this->setCacheBackend($cacheBackend, 'wmentity_overview_filter_storages');
    }
}
