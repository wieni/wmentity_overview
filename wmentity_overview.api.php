<?php

use Drupal\wmentity_overview\Annotation\OverviewBuilder;

function hook_entity_overview_alter(OverviewBuilder $definition, array &$overview)
{
    if (!empty($overview['form'])) {
        $overview['form']['#attributes']['class'][] = 'custom-entity-overview__form';
    }

    $overview['table']['#attributes']['class'][] = 'custom-entity-overview__table';
}

function hook_entity_overview_alternatives_alter(array &$alternatives, OverviewBuilder $definition)
{
    $routeMatch = \Drupal::routeMatch();
    $overviewBuilders = \Drupal::getContainer()->get('plugin.manager.wmentity_overview_builder');

    if (!$vocabulary = $routeMatch->getParameter('taxonomy_vocabulary')) {
        return;
    }

    if ($definition->getEntityTypeId() !== 'taxonomy_term') {
        return;
    }

    $filters = ['vid' => $vocabulary->id()];
    $alternatives = array_merge(
        $alternatives,
        $overviewBuilders->getAlternativesByFilters($definition, $filters)
    );
}

function hook_wmentity_overview_builder_info_alter(array &$definitions)
{
    $definitions['node.page']['class'] = \Drupal\my_module\Entity\Overview\Node\PageOverview::class;
}
