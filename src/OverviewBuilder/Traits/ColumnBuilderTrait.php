<?php

namespace Drupal\wmentity_overview\OverviewBuilder\Traits;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\user\EntityOwnerInterface;

/**
 * @mixin EntityListBuilder
 * @mixin BundleEntityTrait
 */
trait ColumnBuilderTrait
{
    protected function buildLabelColumn(EntityInterface $entity, ?string $rel = 'canonical'): array
    {
        if ($entity->hasLinkTemplate($rel) && $entity->toUrl($rel)->access()) {
            $data = $entity->toLink(null, $rel)->toRenderable();
        } else {
            $data = ['#plain_text' => $entity->label()];
        }

        return ['data' => $data];
    }

    protected function buildBundleColumn(EntityInterface $entity): array
    {
        return [
            'data' => [
                '#plain_text' => $this->getBundleEntity($entity->bundle())->label(),
            ],
        ];
    }

    protected function buildChangedColumn(EntityChangedInterface $entity): array
    {
        return [
            'data' => [
                '#plain_text' => date('d/m/Y H:i', $entity->getChangedTime()),
            ],
        ];
    }

    protected function buildCreatedColumn(EntityInterface $entity): array
    {
        return [
            'data' => [
                '#plain_text' => date('d/m/Y H:i', $entity->getCreatedTime()),
            ],
        ];
    }

    protected function buildOperationsColumn(EntityInterface $entity): array
    {
        return [
            'data' => $this->buildOperations($entity),
        ];
    }

    protected function buildOwnerColumn(EntityOwnerInterface $entity): array
    {
        return [
            'data' => [
                '#plain_text' => $entity->getOwner()->getDisplayName(),
            ],
        ];
    }

    protected function buildTagColumn(string $tag, $value = null): array
    {
        return [
            'data' => [
                '#type' => 'html_tag',
                '#tag' => $tag,
                '#value' => $value,
            ],
        ];
    }

    protected function buildTextColumn($text): array
    {
        return [
            'data' => [
                '#plain_text' => $text,
            ],
        ];
    }

    protected function buildTextWithTooltipColumn($text, $tooltip): array
    {
        $column = [
            'data' => [
                '#attached' => [
                    'library' => ['wmentity_overview/tooltip']
                ],
                'tooltip' => [
                    '#type' => 'html_tag',
                    '#tag' => 'div',
                    '#value' => $tooltip,
                    '#attributes' => [
                        'data-tooltip' => 'nothing',
                    ],
                    'arrow' => [
                        '#markup' => '<div data-popper-arrow></div>'
                    ],
                ],
            ],
        ];

        if (is_array($text)) {
            $column['data']['target']['content'] = $text;
        } else {
            $column['data']['target'] = [
                '#type' => 'html_tag',
                '#tag' => 'div',
                '#value' => $text,
                '#attributes' => [
                    'data-tooltip-target' => 'nothing',
                ],
            ];
        }

        return $column;
    }

    protected function buildTruncatedTextColumn($text, int $maxLength = 50, bool $wordSafe = false, bool $addEllipsis = false, int $minWordsafeLength = 1): array
    {
        $truncatedText = Unicode::truncate($text, $maxLength, $wordSafe, $addEllipsis, $minWordsafeLength);

        return $this->buildTextWithTooltipColumn($truncatedText, $text);
    }
}
