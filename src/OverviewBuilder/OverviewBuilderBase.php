<?php

namespace Drupal\wmentity_overview\OverviewBuilder;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\TableSortExtender;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\wmentity_overview\Annotation\OverviewBuilder;
use Drupal\wmentity_overview\Common\ColumnInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class OverviewBuilderBase extends EntityListBuilder implements OverviewBuilderInterface
{
    /**
     * Enable table sorting
     * @var bool
     */
    protected $sort = true;

    /**
     * The annotation containing metadata about this plugin
     * @var OverviewBuilder
     */
    protected $definition;

    /** @var Connection */
    protected $database;

    public static function create(
        ContainerInterface $container,
        OverviewBuilder $definition
    ) {
        $entityTypeManager = $container->get('entity_type.manager');
        $entityType = $entityTypeManager->getDefinition($definition->getEntityTypeId());
        $storage = $entityTypeManager->getStorage($definition->getEntityTypeId());

        $instance = new static($entityType, $storage);
        $instance->definition = $definition;
        $instance->database = $container->get('database');

        return $instance;
    }

    public function getDefinition(): OverviewBuilder
    {
        return $this->definition;
    }

    public function buildHeader()
    {
        return array_map(
            function (ColumnInterface $column) {
                $data = [
                    'data' => $column->getLabel(),
                ];

                if ($this->sort && $column->isSortable()) {
                    $data['field'] = $data['specifier'] = $column->getSortField();
                }

                if ($defaultSort = $column->getDefaultSortDirection()) {
                    $data['sort'] = $defaultSort;
                }

                return $data;
            },
            $this->getColumns()
        );
    }

    public function load()
    {
        $q = $this->getQuery();

        if ($this->limit) {
            $q = $q->extend(PagerSelectExtender::class);
            $q->limit($this->limit);
        }

        if ($this->sort) {
            $sort = $q->extend(TableSortExtender::class);
            $sort->orderByHeader($this->buildHeader());
        }

        $rows = $q->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        return array_reduce(
            $rows,
            function (array $entities, array $row) {
                if ($entity = $this->getEntityFromRow($row)) {
                    $key = implode('.', [$entity->id(), $entity->language()->getId()]);
                    $entities[$key] = $entity;
                }

                return $entities;
            },
            []
        );
    }

    public function getQuery(): SelectInterface
    {
        $table = $this->entityType->getDataTable() ?? $this->entityType->getBaseTable();
        $alias = $this->entityType->id();

        $fields = [
            $this->entityType->getKey('id'),
        ];

        if ($langcodeKey = $this->entityType->getKey('langcode')) {
            $fields[] = $langcodeKey;
        }

        return $this->database->select($table, $alias)
            ->fields($alias, $fields);
    }

    protected function getEntityFromRow(array $row): ?EntityInterface
    {
        $idKey = $this->entityType->getKey('id');

        if (!isset($row[$idKey])) {
            return null;
        }

        $entity = $this->storage->load($row[$idKey]);

        if (!$entity) {
            return null;
        }

        $langcodeKey = $this->entityType->getKey('langcode');

        if (!isset($row[$langcodeKey])) {
            return $entity;
        }

        $langcode = $row[$langcodeKey];

        if ($entity->hasTranslation($langcode)) {
            return $entity->getTranslation($langcode);
        }

        return null;
    }

    public function render()
    {
        $build['table'] = [
            '#type' => 'table',
            '#header' => $this->buildHeader(),
            '#title' => $this->getTitle(),
            '#rows' => [],
            '#empty' => $this->t('There are no @label yet.', ['@label' => $this->entityType->getPluralLabel()]),
            '#cache' => [
                'contexts' => $this->entityType->getListCacheContexts(),
                'tags' => $this->entityType->getListCacheTags(),
            ],
        ];

        foreach ($this->load() as $key => $entity) {
            if ($row = $this->buildRow($entity)) {
                $build['table']['#rows'][$key] = $row;
            }
        }

        // Only add the pager if a limit is specified.
        if ($this->limit) {
            $build['pager'] = [
                '#type' => 'pager',
            ];
        }

        return $build;
    }
}
