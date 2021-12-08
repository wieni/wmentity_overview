<?php

namespace Drupal\wmentity_overview\OverviewBuilder;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wmentity_overview\Annotation\OverviewBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @see DraggableListBuilder
 */
abstract class DraggableOverviewBuilderBase extends OverviewBuilderBase implements FormInterface
{
    /**
     * The key to use for the form element containing the entities.
     * @var string
     */
    protected $entitiesKey = 'entities';

    /**
     * The entities being listed.
     *
     * @var EntityInterface[]
     */
    protected $entities = [];

    /**
     * Name of the entity's weight field
     * @var string
     */
    protected $weightKey;

    /** @var FormBuilderInterface */
    protected $formBuilder;

    public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage)
    {
        parent::__construct($entity_type, $storage);

        // Check if the entity type supports weighting.
        if ($this->entityType->hasKey('weight')) {
            $this->weightKey = $this->entityType->getKey('weight');
        }

        // Draggable overviews are ordered by weight
        $this->sort = false;
        // Draggable overviews cannot be paged
        $this->limit = false;
    }

    public static function create(ContainerInterface $container, OverviewBuilder $definition)
    {
        $instance = parent::create($container, $definition);
        $instance->formBuilder = $container->get('form_builder');

        return $instance;
    }

    public function buildHeader()
    {
        $header = parent::buildHeader();

        if (!empty($this->weightKey)) {
            $header['weight'] = $this->t('Weight');
        }

        return $header;
    }

    public function buildRow(EntityInterface $entity)
    {
        $row = parent::buildRow($entity);

        if (!empty($this->weightKey)) {
            // Override default values to markup elements.
            $row['#attributes']['class'][] = 'draggable';
            $row['#weight'] = $entity->get($this->weightKey)->value;
            // Add weight column.
            $row['weight'] = [
                '#type' => 'weight',
                '#title' => $this->t('Weight for @title', ['@title' => $entity->label()]),
                '#title_display' => 'invisible',
                '#default_value' => $entity->get($this->weightKey)->value,
                '#attributes' => ['class' => ['weight']],
            ];
        }

        return $row;
    }

    public function render()
    {
        if (!empty($this->weightKey)) {
            return $this->formBuilder->getForm($this);
        }

        return parent::render();
    }

    public function getFormId()
    {
        return sprintf('wmoverview_builder_draggable_%s', $this->getDefinition()->getId());
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form[$this->entitiesKey] = [
            '#type' => 'table',
            '#header' => $this->buildHeader(),
            '#empty' => $this->t('There are no @label yet.', ['@label' => $this->entityType->getPluralLabel()]),
            '#tabledrag' => [
                [
                    'action' => 'order',
                    'relationship' => 'sibling',
                    'group' => 'weight',
                ],
            ],
        ];

        $this->entities = $this->load();
        $delta = 10;

        // Change the delta of the weight field if have more than 20 entities.
        if (!empty($this->weightKey)) {
            $count = count($this->entities);
            if ($count > 20) {
                $delta = ceil($count / 2);
            }
        }

        foreach ($this->entities as $entity) {
            $row = $this->buildRow($entity);

            foreach (array_keys($row) as $i) {
                if (isset($row[$i]['data'])) {
                    $row[$i] = $row[$i]['data'];
                }
            }

            if (isset($row['label'])) {
                $row['label'] = ['#markup' => $row['label']];
            }

            if (isset($row['weight'])) {
                $row['weight']['#delta'] = $delta;
            }

            $form[$this->entitiesKey][$this->getRowKeyByEntity($entity)] = $row;
        }

        uasort($form[$this->entitiesKey], [SortArray::class, 'sortByWeightProperty']);

        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Save'),
            '#button_type' => 'primary',
        ];

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        foreach ($form_state->getValue($this->entitiesKey) as $id => $value) {
            if (!isset($this->entities[$id])) {
                continue;
            }

            $originalWeight = $this->entities[$id]->get($this->weightKey)->value;
            if ($originalWeight === $value['weight']) {
                continue;
            }

            $this->entities[$id]->set($this->weightKey, $value['weight']);
            $this->entities[$id]->save();
        }
    }
}
