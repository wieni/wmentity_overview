<?php

namespace Drupal\wmentity_overview\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\EntityActionBase;
use Drupal\Core\Entity\ContentEntityInterface;

abstract class BatchActionBase extends EntityActionBase
{
    public function executeMultiple(array $entities): void
    {
        $batch = $this->getBatch($entities);

        if (empty($batch['operations'])) {
            return;
        }

        batch_set($batch);
    }

    public function execute(?ContentEntityInterface $entity = null): void
    {
        $this->executeMultiple([$entity]);
    }

    public function processBatch(array $data, array &$context): void
    {
        if (!isset($context['results']['processed'])) {
            $context['results']['processed'] = 0;
        }

        $entity = $this->entityTypeManager
            ->getStorage($data['entity_type'])
            ->load($data['entity_id']);

        if ($entity->hasTranslation($data['langcode'])) {
            $entity = $entity->getTranslation($data['langcode']);
        }

        if ($entity instanceof ContentEntityInterface) {
            $this->processEntity($entity, $context);
        }

        $context['results']['processed']++;
    }

    abstract protected function processEntity(ContentEntityInterface $entity, array &$context): void;

    protected function getBatch(array $entities): array
    {
        $operations = [];

        foreach ($entities as $entity) {
            $operations[] = [
                [$this, 'processBatch'],
                [
                    [
                        'entity_type' => $entity->getEntityTypeId(),
                        'entity_id' => $entity->id(),
                        'langcode' => $entity->language()->getId(),
                    ],
                ],
            ];
        }

        return [
            'operations' => $operations,
        ];
    }
}
