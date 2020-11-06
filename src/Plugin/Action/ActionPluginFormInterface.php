<?php

namespace Drupal\wmentity_overview\Plugin\Action;

use Drupal\Core\Action\ActionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Provides an interface for an embeddable plugin form for action plugins.
 * The only difference with PluginFormInterface is that entities are passed to the validation & submit handlers
 *
 * @see ActionInterface
 * @see PluginFormInterface
 */
interface ActionPluginFormInterface {

  /**
   * Form constructor.
   *
   * @see PluginFormInterface::buildConfigurationForm()
   */
  public function buildConfigurationForm(array $form, FormStateInterface $formState);

  /**
   * Form validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form as built
   *   by static::buildConfigurationForm().
   * @param FormStateInterface $formState
   *   The current state of the form. Calling code should pass on a subform
   *   state created through \Drupal\Core\Form\SubformState::createForSubform().
   * @param EntityInterface[] $entities
   *   The entities on which this action should be executed.
   *
   * @see PluginFormInterface::validateConfigurationForm()
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $formState, array $entities);

    /**
     * Form submission handler.
     *
     * @param array $form
     *   An associative array containing the structure of the plugin form as built
     *   by static::buildConfigurationForm().
     * @param FormStateInterface $formState
     *   The current state of the form. Calling code should pass on a subform
     *   state created through \Drupal\Core\Form\SubformState::createForSubform().
     * @param EntityInterface[] $entities
     *   The entities on which this action should be executed.
     *
     * @see PluginFormInterface::submitConfigurationForm()
     */
  public function submitConfigurationForm(array &$form, FormStateInterface $formState, array $entities);

}
