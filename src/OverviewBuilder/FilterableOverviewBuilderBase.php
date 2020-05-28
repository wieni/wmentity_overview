<?php

namespace Drupal\wmentity_overview\OverviewBuilder;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wmentity_overview\Annotation\OverviewBuilder;
use Drupal\wmentity_overview\FilterStorage\FilterStorageInterface;
use Drupal\wmentity_overview\Form\FilterForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class FilterableOverviewBuilderBase extends OverviewBuilderBase implements FilterableOverviewBuilderInterface
{
    /** @var FormBuilderInterface */
    protected $formBuilder;
    /** @var FilterStorageInterface */
    protected $filters;

    public static function create(
        ContainerInterface $container,
        OverviewBuilder $definition
    ) {
        $instance = parent::create($container, $definition);
        $instance->formBuilder = $container->get('form_builder');
        $instance->filters = $container->get('plugin.manager.wmentity_overview_filter_storage')
            ->createInstance($definition->getFilterStorageId())
            ->setEntityType($instance->entityType);

        return $instance;
    }

    public function render()
    {
        $overview = parent::render();

        $overview['form'] = $this->formBuilder->getForm(FilterForm::class, $this);
        $overview['form']['#weight'] = -1;

        return $overview;
    }

    public function buildFilterForm(array $form, FormStateInterface $formState): array
    {
        $form['actions']['wrapper'] = [
            '#type' => 'container',
        ];

        $form['actions']['wrapper']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Search'),
            '#attributes' => ['class' => ['button--submit']],
        ];

        $form['actions']['wrapper']['reset'] = [
            '#type' => 'submit',
            '#attributes' => ['class' => ['button--reset']],
            '#value' => $this->t('Reset'),
            '#submit' => [[$this->filters, 'reset']],
        ];

        return $form;
    }

    public function submitFilterForm(array &$form, FormStateInterface $formState): void
    {
        $formState->cleanValues();

        foreach ($formState->getValues() as $key => $value) {
            if ($value === null || $value === '') {
                $this->filters->remove($key);
                continue;
            }

            $value = $this->processFilterValue($key, $value);
            $this->filters->set($key, $value);
        }
    }

    protected function processFilterValue(string $key, $value)
    {
        return $value;
    }
}
