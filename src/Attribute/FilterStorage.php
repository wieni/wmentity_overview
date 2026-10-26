<?php

namespace Drupal\wmentity_overview\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Attribute counterpart of the FilterStorage annotation.
 *
 * @see \Drupal\wmentity_overview\Annotation\FilterStorage
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class FilterStorage extends Plugin
{
}
