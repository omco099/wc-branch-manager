<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Core;

use Alnaseeg\BranchManager\Admin\Menu;
use Alnaseeg\BranchManager\Product\ProductDataPanel;
use Alnaseeg\BranchManager\Product\ProductDataTab;

/**
 * Main plugin application.
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
        (new ProductDataTab())->register();

        (new ProductDataPanel())->register();
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