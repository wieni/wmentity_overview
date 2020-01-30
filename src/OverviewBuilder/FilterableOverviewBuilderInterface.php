<?php

namespace Drupal\wmentity_overview\OverviewBuilder;

use Drupal\Core\Form\FormStateInterface;

interface FilterableOverviewBuilderInterface extends OverviewBuilderInterface
{
    /**
     * Filter form constructor.
     *
     * @param array $form
     *   An associative array containing the structure of the form.
     * @param FormStateInterface $formState
     *   The current state of the form.
     *
     * @return array
     *   The form structure.
     */
    public function buildFilterForm(array $form, FormStateInterface $formState): array;

    /**
     * Filter form submission handler.
     *
     * @param array $form
     *   An associative array containing the structure of the form.
     * @param FormStateInterface $formState
     *   The current state of the form.
     */
    public function submitFilterForm(array &$form, FormStateInterface $formState): void;
}
