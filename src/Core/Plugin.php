<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Core;

/**
 * Main plugin application.
 *
 * Responsible for bootstrapping all plugin modules.
 */
final class Plugin
{
    /**
     * Boot the plugin.
     */
    public function boot(): void
    {
        $this->registerModules();
        $this->registerHooks();
    }

    /**
     * Register plugin modules.
     */
    private function registerModules(): void
    {
    }

    /**
     * Register plugin hooks.
     */
    private function registerHooks(): void
    {
    }
}