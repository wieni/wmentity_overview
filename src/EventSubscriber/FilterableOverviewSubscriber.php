<?php

namespace Drupal\wmentity_overview\EventSubscriber;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\Element;
use Drupal\wmentity_overview\Event\EntityOverviewAlterEvent;
use Drupal\wmentity_overview\Form\FilterForm;
use Drupal\wmentity_overview\OverviewBuilder\FilterableOverviewBuilderInterface;
use Drupal\wmentity_overview\WmEntityOverviewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FilterableOverviewSubscriber implements EventSubscriberInterface
{
    /** @var FormBuilderInterface */
    protected $formBuilder;

    public function __construct(
        FormBuilderInterface $formBuilder
    ) {
        $this->formBuilder = $formBuilder;
    }

    public static function getSubscribedEvents(): array
    {
        $events[WmEntityOverviewEvents::ENTITY_OVERVIEW_ALTER] = ['onOverviewAlter'];

        return $events;
    }

    public function onOverviewAlter(EntityOverviewAlterEvent $event): void
    {
        $builder = $event->getBuilder();

        if (!$builder instanceof FilterableOverviewBuilderInterface) {
            return;
        }

        $form = $this->formBuilder->getForm(FilterForm::class, $builder);
        $children = array_diff(
            Element::children($form),
            ['form_id', 'form_token', 'form_build_id', 'op']
        );

        if (empty($children)) {
            return;
        }

        $overview = &$event->getOverview();
        $overview['form'] = $form;
        $overview['form']['#weight'] = -1;
    }
}
