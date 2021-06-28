<?php

namespace Drupal\wmentity_overview\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wmentity_overview\OverviewBuilder\FilterableOverviewBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FilterForm implements FormInterface, ContainerInjectionInterface
{
    /** @var RequestStack */
    protected $requestStack;
    /** @var FilterableOverviewBuilderInterface */
    protected $builder;

    public static function create(ContainerInterface $container)
    {
        $instance = new static();
        $instance->requestStack = $container->get('request_stack');

        return $instance;
    }

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

        // Workaround for https://www.drupal.org/project/drupal/issues/2950883
        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->query->has('destination')) {
            $request->query->remove('destination');
        }
    }
}
