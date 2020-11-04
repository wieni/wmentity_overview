<?php

namespace Drupal\wmentity_overview\Action;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a dummy action that can act as the bulk action form submit handler.
 */
interface SubmitFormActionInterface
{
    public function submitForm(array &$form, FormStateInterface $formState, array $entities): void;
}
