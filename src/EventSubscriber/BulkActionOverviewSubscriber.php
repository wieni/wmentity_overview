<?php

namespace Drupal\wmentity_overview\EventSubscriber;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\wmentity_overview\Event\EntityOverviewAlterEvent;
use Drupal\wmentity_overview\Form\BulkActionForm;
use Drupal\wmentity_overview\OverviewBuilder\BulkActionOverviewBuilderInterface;
use Drupal\wmentity_overview\WmEntityOverviewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BulkActionOverviewSubscriber implements EventSubscriberInterface
{
    /** @var FormBuilderInterface */
    protected $formBuilder;

    public function __construct(
        FormBuilderInterface $formBuilder
    ) {
        $this->formBuilder = $formBuilder;
    }

    public static function getSubscribedEvents()
    {
        $events[WmEntityOverviewEvents::ENTITY_OVERVIEW_ALTER] = ['onOverviewAlter'];

        return $events;
    }

    public function onOverviewAlter(EntityOverviewAlterEvent $event): void
    {
        $builder = $event->getBuilder();

        if (!$builder instanceof BulkActionOverviewBuilderInterface) {
            return;
        }

        $overview = &$event->getOverview();
        $overview['table'] = $this->formBuilder->getForm(BulkActionForm::class, $overview['table'], $builder);
    }
}
