<?php

namespace Drupal\wmentity_overview\FilterStorage;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\wmentity_overview\Annotation\FilterStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @FilterStorage(
 *     id = "query_param",
 * )
 */
class QueryParamFilterStorage extends FilterStorageBase implements ContainerFactoryPluginInterface
{
    protected const RESERVED_PARAMS = [
        /** @see \Drupal\Core\Routing\RedirectDestination */
        'destination',
    ];

    /** @var RequestStack */
    protected $requestStack;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = new static;
        $instance->requestStack = $container->get('request_stack');

        return $instance;
    }

    public function getAll(): array
    {
        return $this->getParams()->all();
    }

    public function get(string $key)
    {
        return $this->getAll()[$key] ?? null;
    }

    public function set(string $key, $value): FilterStorageInterface
    {
        if (in_array($key, self::RESERVED_PARAMS, true)) {
            throw new \InvalidArgumentException("'{$key}' is a reserved query parameter and should not be used as filter key.");
        }

        $this->getParams()->set($key, $value);

        return $this;
    }

    public function remove(string $key): void
    {
        $this->getParams()->remove($key);
    }

    public function reset(): void
    {
        $this->getRequest()->query = new ParameterBag;
    }

    protected function getParams(): ParameterBag
    {
        $request = $this->getRequest();

        if (!$request) {
            return new ParameterBag;
        }

        return $request->query;
    }

    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}
