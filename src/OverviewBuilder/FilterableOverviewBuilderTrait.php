<?php

namespace Drupal\wmentity_overview\OverviewBuilder;

use Drupal\Core\Form\FormStateInterface;

trait FilterableOverviewBuilderTrait
{
    public function buildFilterForm(array $form, FormStateInterface $formState): array
    {
        $form['#attached']['library'][] = 'wmentity_overview/filter-form';

        $form['actions']['wrapper'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['wmentity-overview-filter-form__actions']],
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
