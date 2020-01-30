<?php

namespace Drupal\wmentity_overview\OverviewBuilder;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wmentity_overview\Annotation\OverviewBuilder;
use Drupal\wmentity_overview\FilterStorage\FilterStorageManager;
use Drupal\wmentity_overview\Plugin\Factory\OverviewBuilderPluginFactory;

/**
 * @method OverviewBuilderInterface createInstance($plugin_id, array $configuration = [])
 */
class OverviewBuilderManager extends DefaultPluginManager
{
    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var FilterStorageManager */
    protected $filterStorageManager;

    public function __construct(
        \Traversable $namespaces,
        CacheBackendInterface $cacheBackend,
        ModuleHandlerInterface $moduleHandler,
        EntityTypeManagerInterface $entityTypeManager,
        FilterStorageManager $filterStorageManager
    ) {
        parent::__construct(
            '',
            $namespaces,
            $moduleHandler,
            OverviewBuilderInterface::class,
            OverviewBuilder::class
        );
        $this->alterInfo('wmentity_overview_builder_info');
        $this->setCacheBackend($cacheBackend, 'wmentity_overview_builders');

        $this->factory = new OverviewBuilderPluginFactory($this, OverviewBuilderInterface::class);
        $this->entityTypeManager = $entityTypeManager;
        $this->filterStorageManager = $filterStorageManager;
    }

    /** @return OverviewBuilder[] */
    public function getAlternativesByFilters(OverviewBuilder $definition): array
    {
        $alternatives = [];
        $filterStorage = $this->filterStorageManager->createInstance($definition->getFilterStorageId());
        $possibleAlternatives = array_diff_key(
            $this->getDefinitionsByEntityType($definition->getEntityTypeId()),
            [$definition->getId() => $definition]
        );

        foreach ($possibleAlternatives as $alternative) {
            $missingFilters = array_diff_assoc($alternative->getFilters(), $filterStorage->getAll());

            if (empty($missingFilters)) {
                $alternatives[] = $alternative;
            }
        }

        return $alternatives;
    }

    /** @return OverviewBuilder[] */
    public function getDefinitionsByEntityType(string $entityTypeId): array
    {
        return array_reduce(
            $this->getDefinitions(),
            static function (array $definitions, $definition) use ($entityTypeId) {
                $definition = new OverviewBuilder($definition);

                if ($definition->getEntityTypeId() !== $entityTypeId) {
                    return $definitions;
                }

                if (!$definition->isOverride()) {
                    return $definitions;
                }

                $definitions[$definition->getId()] = $definition;

                return $definitions;
            },
            []
        );
    }

    public function getDefinitionsByRouteName(?string $type = null): array
    {
        return array_reduce(
            $this->getDefinitions(),
            static function (array $definitions, $definition) use ($type) {
                $definition = new OverviewBuilder($definition);
                $routeName = $definition->getRouteName();

                if (!$routeName) {
                    return $definitions;
                }

                if ($type && $definition->getType() !== $type) {
                    return $definitions;
                }

                $definitions[$definition->getRouteName()][$definition->getId()] = $definition;

                return $definitions;
            },
            []
        );
    }

    protected function findDefinitions(): array
    {
        $definitions = parent::findDefinitions();

        foreach ($definitions as &$definition) {
            $annotation = new OverviewBuilder($definition);

            if (!$annotation->getId()) {
                throw new \RuntimeException(sprintf("Overview builder with class '%s' is missing the id property.", $annotation->getClass()));
            }

            if (!$annotation->getEntityTypeId()) {
                throw new \RuntimeException(sprintf("Overview builder with id '%s' is missing the entity_type property.", $annotation->getId() ?? ''));
            }

            if ($annotation->isOverride()) {
                $definition['route_name'] = $this->guessRouteName($definition);
            }

            $definition['type'] = $annotation->getType();
            $definition['id'] = $annotation->getId();
        }

        return $definitions;
    }

    protected function guessRouteName(array $definition): string
    {
        $entityType = $this->entityTypeManager->getDefinition($definition['entity_type']);

        $map = [
            'node' => 'system.admin_content',
            'taxonomy_term' => 'entity.taxonomy_vocabulary.overview_form',
            'user' => 'entity.user.collection',
        ];

        if (isset($map[$entityType->id()])) {
            return $map[$entityType->id()];
        }

        if ($entityType->getProvider() === 'eck') {
            return sprintf('eck.entity.%s.list', $entityType->id());
        }

        throw new \RuntimeException(sprintf(
            'Could not determine route name of EntityOverview with id \'%s\'. Please add a route_name parameter to the annotation.',
            $definition['id'],
        ));
    }
}
