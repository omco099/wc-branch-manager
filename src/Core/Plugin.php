<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Core;

use Alnaseeg\BranchManager\Admin\Menu;

/**
 * Main plugin application.
 */
final class Plugin
{
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
     * Register WordPress hooks.
     */
    private function registerHooks(): void
    {
        add_action(
            'admin_menu',
            [
                new Menu(),
                'register',
            ]
        );
    }
}