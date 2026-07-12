<?php

declare(strict_types=1);

use Alnaseeg\BranchManager\Database\Installer;

/**
 * Bootstrap file for the plugin skeleton.
 */

if (!function_exists('alnaseeg_branch_manager_activate')) {
    /**
     * Activate the plugin and run the database installer.
     */
    function alnaseeg_branch_manager_activate(): void
    {
        $installer = new Installer();
        $installer->activate();
    }
}

register_activation_hook(ALNASEEG_BRANCH_MANAGER_PLUGIN_FILE, 'alnaseeg_branch_manager_activate');
