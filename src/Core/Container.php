<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Core;

/**
 * Lightweight dependency container.
 *
 * Responsible for storing and resolving shared services.
 */
final class Container
{
    /**
     * Registered services.
     *
     * @var array<string, object>
     */
    private array $services = [];

    /**
     * Register a service.
     */
    public function set(string $id, object $service): void
    {
        $this->services[$id] = $service;
    }

    /**
     * Resolve a registered service.
     *
     * @throws \RuntimeException
     */
    public function get(string $id): object
    {
        if (! isset($this->services[$id])) {
            throw new \RuntimeException(
                sprintf('Service "%s" is not registered.', $id)
            );
        }

        return $this->services[$id];
    }

    /**
     * Determine whether a service exists.
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }
}