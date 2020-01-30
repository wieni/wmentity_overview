<?php

namespace Drupal\wmentity_overview\FilterStorage;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\wmentity_overview\Annotation\FilterStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @FilterStorage(
 *     id = "session",
 * )
 */
class SessionFilterStorage extends FilterStorageBase implements ContainerFactoryPluginInterface
{
    /** @var SessionInterface */
    protected $session;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = new static;
        $instance->session = $container->get('session');

        return $instance;
    }

    public function getAll(): array
    {
        return $this->session->get($this->getSessionKey(), []);
    }

    public function get(string $key)
    {
        return $this->getAll()[$key] ?? null;
    }

    public function set(string $key, $value): FilterStorageInterface
    {
        $data = $this->session->get($this->getSessionKey());
        $data[$key] = $value;
        $this->session->set($this->getSessionKey(), $data);

        return $this;
    }

    public function remove(string $key): void
    {
        $data = $this->session->get($this->getSessionKey());
        unset($data[$key]);
        $this->session->set($this->getSessionKey(), $data);
    }

    public function reset(): void
    {
        $this->session->remove($this->getSessionKey());
    }

    protected function getSessionKey(): string
    {
        return sprintf(
            'wmentity_overview.filters.%s',
            $this->entityType->id(),
        );
    }
}
