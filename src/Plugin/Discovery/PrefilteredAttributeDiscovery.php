<?php

namespace Drupal\wmentity_overview\Plugin\Discovery;

use Drupal\Core\Plugin\Discovery\AttributeDiscoveryWithAnnotations;

/**
 * Attribute and annotation discovery that doesn't load every class it scans.
 *
 * Overview builders and filter storages can live anywhere in a module, so
 * their managers scan whole namespaces (an empty subdirectory). Annotation
 * discovery parses those files without loading them, but attribute discovery
 * autoloads every class it finds to read its attributes: thousands of classes
 * on a cold cache, deprecated core classes among them, which raise
 * deprecations just by being loaded.
 *
 * A file that doesn't mention the plugin type's name can't declare one of its
 * plugins (an aliased or fully qualified attribute still names the class in
 * the file), so it gets the same file cache entry the parent stores for a
 * class without a plugin, and the parent skips it without loading it.
 */
class PrefilteredAttributeDiscovery extends AttributeDiscoveryWithAnnotations
{
    public function getDefinitions()
    {
        $markers = array_unique([
            $this->getShortClassName($this->pluginDefinitionAttributeName),
            $this->getShortClassName($this->pluginDefinitionAnnotationName),
        ]);

        foreach ($this->getPluginNamespaces() as $dirs) {
            foreach ($dirs as $dir) {
                if (!is_dir($dir)) {
                    continue;
                }

                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
                );

                foreach ($iterator as $fileinfo) {
                    $path = $fileinfo->getPathName();
                    if ($fileinfo->getExtension() !== 'php' || $this->fileCache->get($path)) {
                        continue;
                    }

                    $source = (string) file_get_contents($path);
                    foreach ($markers as $marker) {
                        if (str_contains($source, $marker)) {
                            continue 2;
                        }
                    }

                    $this->fileCache->set($path, [null]);
                }
            }
        }

        return parent::getDefinitions();
    }

    protected function getShortClassName(string $class): string
    {
        $position = strrpos($class, '\\');

        return $position === false ? $class : substr($class, $position + 1);
    }
}
