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
use Drupal\wmentity_overview\Action\SubmitFormActionInterface;
use Drupal\wmentity_overview\OverviewBuilder\BulkActionOverviewBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BulkActionForm implements FormInterface, ContainerInjectionInterface
{
    use DependencySerializationTrait;
    use StringTranslationTrait;

    /** @var BulkActionOverviewBuilderInterface */
    protected $overviewBuilder;
    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var EntityRepositoryInterface */
    protected $entityRepository;
    /** @var ActionManager */
    protected $actionManager;

    public static function create(ContainerInterface $container)
    {
        $instance = new static();
        $instance->entityTypeManager = $container->get('entity_type.manager');
        $instance->entityRepository = $container->get('entity.repository');
        $instance->actionManager = $container->get('plugin.manager.action');

        return $instance;
    }

    public function getFormId(): string
    {
        return 'wmentity_overview_bulk_action_form';
    }

    public function buildForm(array $form, FormStateInterface $formState, ?array $table = null, ?BulkActionOverviewBuilderInterface $overviewBuilder = null): array
    {
        if (!is_array($table)) {
            throw new \RuntimeException('BulkActionForm needs a table render array to wrap');
        }

        if ($overviewBuilder === null) {
            throw new \RuntimeException('BulkActionForm needs an OverviewBuilder');
        }

        $this->overviewBuilder = $overviewBuilder;

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

        if ($action instanceof PluginFormInterface) {
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

            if ($action instanceof PluginFormInterface) {
                $subFormState = SubformState::createForSubform($form['configuration']['form'], $form, $formState);
                $action->validateConfigurationForm($form, $subFormState);
            }
        }
    }

    public function submitForm(array &$form, FormStateInterface $formState): void
    {
        if (!$rows = array_filter($formState->getValue('table', []))) {
            return;
        }

        $pluginId = $formState->getValue('bulk_action');
        $action = $this->getAction($pluginId);

        if ($action instanceof PluginFormInterface && !empty($form['configuration']['form'])) {
            $subFormState = SubformState::createForSubform($form['configuration']['form'], $form, $formState);
            $action->submitConfigurationForm($form, $subFormState);
        }

        $overviewBuilder = $this->getOverviewBuilder($formState);
        $entities = $this->getEntitiesFromRowKeys(
            $overviewBuilder->getDefinition()->getEntityTypeId(),
            array_keys($rows)
        );

        if ($action instanceof SubmitFormActionInterface) {
            $action->submitForm($form, $formState, $entities);
        } else {
            $action->executeMultiple($entities);
        }
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
        if (isset($this->overviewBuilder)) {
            return $this->overviewBuilder;
        }

        if ($formState && !empty($formState->getBuildInfo()['args'])) {
            return $this->overviewBuilder = $formState->getBuildInfo()['args'][1];
        }

        throw new \RuntimeException('BulkActionForm needs an OverviewBuilder');
    }

    public static function onBulkActionsAjax(array $form, FormStateInterface $formState)
    {
        $formState->setRebuild(true);

        return $form['configuration'];
    }

    protected function getEntitiesFromRowKeys(string $entityTypeId, array $keys): array
    {
        $storage = $this->entityTypeManager->getStorage($entityTypeId);

        return array_reduce($keys, function (array $entities, $key) use ($storage) {
            if (empty($key) || !is_string($key)) {
                return $entities;
            }

            $parts = explode('.', $key);

            if (count($parts) !== 2) {
                return $entities;
            }

            [$id, $langcode] = $parts;

            if (!$entity = $storage->load($id)) {
                return $entities;
            }

            $entities[] = $this->entityRepository->getTranslationFromContext($entity, $langcode);

            return $entities;
        }, []);
    }
}
