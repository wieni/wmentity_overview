<?php

namespace Drupal\wmentity_overview\Form;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\Html;
use Drupal\Core\Action\ActionInterface;
use Drupal\Core\Action\ActionManager;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\wmentity_overview\OverviewBuilder\BulkActionOverviewBuilderInterface;
use Drupal\wmentity_overview\OverviewBuilder\OverviewBuilderInterface;
use Drupal\wmentity_overview\OverviewBuilder\OverviewBuilderManager;
use Drupal\wmentity_overview\Plugin\Action\ActionPluginFormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BulkActionForm implements FormInterface, ContainerInjectionInterface
{
    use DependencySerializationTrait;
    use StringTranslationTrait;

    /** @var string */
    protected $overviewBuilderId;
    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var EntityRepositoryInterface */
    protected $entityRepository;
    /** @var ActionManager */
    protected $actionManager;
    /** @var OverviewBuilderManager */
    protected $overviewBuilderManager;

    public static function create(ContainerInterface $container)
    {
        $instance = new static();
        $instance->entityTypeManager = $container->get('entity_type.manager');
        $instance->entityRepository = $container->get('entity.repository');
        $instance->actionManager = $container->get('plugin.manager.action');
        $instance->overviewBuilderManager = $container->get('plugin.manager.wmentity_overview_builder');

        return $instance;
    }

    public function getFormId(): string
    {
        return 'wmentity_overview_bulk_action_form';
    }

    public function buildForm(array $form, FormStateInterface $formState, ?array $table = null, ?string $overviewBuilderId = null): array
    {
        if (!is_array($table)) {
            throw new \RuntimeException('BulkActionForm needs a table render array to wrap');
        }

        if (!$overviewBuilder = $this->getOverviewBuilder($formState)) {
            throw new \RuntimeException('BulkActionForm needs an OverviewBuilder');
        }

        $form['#attached']['library'][] = 'wmentity_overview/bulk-action-form';

        $formWrapperId = Html::getUniqueId('wmentity-overview-bulk-action-form');
        $form['form'] = [
            '#type' => 'container',
            '#attributes' => [
                'class' => ['wmentity-overview-bulk-action-form__form'],
            ],
        ];

        $form['form']['bulk_action'] = [
            '#type' => 'select',
            '#options' => $this->getActionOptions($overviewBuilder),
            '#empty_option' => $this->t('Choose a bulk action'),
            '#required' => true,
            '#ajax' => [
                'callback' => [static::class, 'onBulkActionsAjax'],
                'wrapper' => $formWrapperId,
            ],
        ];

        $actionId = $formState->getUserInput()['bulk_action'] ?? '';
        $action = $this->getAction($actionId);

        $form['configuration'] = [
            '#type' => 'container',
            '#attributes' => [
                'class' => ['wmentity-overview-bulk-action-form__form'],
                'id' => $formWrapperId,
            ],
        ];

        if ($action instanceof PluginFormInterface || $action instanceof ActionPluginFormInterface) {
            $form['configuration']['form'] = [];
            $subformState = SubformState::createForSubform($form['configuration']['form'], $form, $formState);

            if ($subform = $action->buildConfigurationForm($form['configuration']['form'], $subformState)) {
                $form['configuration']['form'] = $subform;
                $form['configuration']['form']['#tree'] = true;
            }
        }

        $form['form']['actions'] = [
            '#type' => 'actions',
        ];

        $form['form']['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Execute'),
        ];

        // Tableselect needs table rows as children
        $rows = $table['#rows'];
        unset($table['#rows']);
        $table += $rows;

        $table['#tableselect'] = true;
        $form['table'] = $table;

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $formState): void
    {
        if (!empty($form['configuration']['form'])) {
            $pluginId = $formState->getValue('bulk_action');
            $action = $this->getAction($pluginId);
            $subFormState = SubformState::createForSubform($form['configuration']['form'], $form, $formState);
            $entities = $this->getEntities($formState);

            if ($action instanceof PluginFormInterface) {
                $action->validateConfigurationForm($form, $subFormState);
            }

            if ($action instanceof ActionPluginFormInterface) {
                $action->validateConfigurationForm($form, $subFormState, $entities);
            }
        }
    }

    public function submitForm(array &$form, FormStateInterface $formState): void
    {
        $pluginId = $formState->getValue('bulk_action');
        $action = $this->getAction($pluginId);
        $entities = $this->getEntities($formState);

        if (!empty($form['configuration']['form'])) {
            $subFormState = SubformState::createForSubform($form['configuration']['form'], $form, $formState);

            if ($action instanceof PluginFormInterface) {
                $action->submitConfigurationForm($form, $subFormState);
            }

            if ($action instanceof ActionPluginFormInterface) {
                $action->submitConfigurationForm($form, $subFormState, $entities);
            }
        }

        $action->executeMultiple($entities);
    }

    protected function getActionOptions(BulkActionOverviewBuilderInterface $overviewBuilder): array
    {
        $entityTypeId = $overviewBuilder->getDefinition()->getEntityTypeId();
        $definitions = $this->actionManager->getDefinitionsByType($entityTypeId);
        $included = $overviewBuilder->getBulkActionPlugins();
        $options = [];

        foreach ($included as $key => $value) {
            if (is_int($key)) {
                $options[$value] = $definitions[$value]['label'];
            } else {
                $options[$key] = $value;
            }
        }

        return $options;
    }

    protected function getAction(string $pluginId): ?ActionInterface
    {
        try {
            $action = $this->actionManager->createInstance($pluginId);
        } catch (PluginException $e) {
            return null;
        }

        return $action;
    }

    protected function getOverviewBuilder(?FormStateInterface $formState = null): BulkActionOverviewBuilderInterface
    {
        if ($formState && !empty($formState->getBuildInfo()['args'])) {
            $this->overviewBuilderId = $formState->getBuildInfo()['args'][1];
        }

        if (isset($this->overviewBuilderId)) {
            return $this->overviewBuilderManager
                ->createInstance($this->overviewBuilderId);;
        }

        throw new \RuntimeException('BulkActionForm needs an OverviewBuilder');
    }

    public static function onBulkActionsAjax(array $form, FormStateInterface $formState)
    {
        $formState->setRebuild(true);

        return $form['configuration'];
    }

    protected function getEntities(FormStateInterface $formState): array
    {
        if (!$rows = array_filter($formState->getValue('table', []))) {
            return [];
        }

        $keys = array_keys($rows);
        $overviewBuilder = $this->getOverviewBuilder($formState);

        return array_reduce($keys, static function (array $entities, $key) use ($overviewBuilder) {
            if (empty($key) || !is_string($key)) {
                return $entities;
            }

            if (!$entity = $overviewBuilder->getEntityByRowKey($key)) {
                return $entities;
            }

            $entities[] = $entity;
            return $entities;
        }, []);
    }
}
