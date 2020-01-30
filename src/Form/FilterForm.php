<?php

namespace Drupal\wmentity_overview\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wmentity_overview\OverviewBuilder\FilterableOverviewBuilderInterface;

class FilterForm implements FormInterface
{
    /** @var FilterableOverviewBuilderInterface */
    protected $builder;

    public function getFormId(): string
    {
        return 'wmentity_overview_filter_form';
    }

    public function buildForm(array $form, FormStateInterface $formState, $builder = null): array
    {
        if (!$builder instanceof FilterableOverviewBuilderInterface) {
            throw new \RuntimeException('FilterForm needs a FilterableOverviewBuilderInterface');
        }

        $this->builder = $builder;

        return $builder->buildFilterForm($form, $formState);
    }

    public function validateForm(array &$form, FormStateInterface $formState): void
    {
    }

    public function submitForm(array &$form, FormStateInterface $formState): void
    {
        $this->builder->submitFilterForm($form, $formState);
    }
}
