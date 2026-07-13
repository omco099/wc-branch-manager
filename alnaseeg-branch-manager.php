<?php

declare(strict_types=1);

/*
Plugin Name: Alnaseeg Branch Manager
Description: Branch management for WooCommerce.
Version: 0.1.0
Author: Alnaseeg
*/

use Alnaseeg\BranchManager\Core\Plugin;

if (! defined('ABSPATH')) {
    exit;
}

define('ALNASEEG_BRANCH_MANAGER_PLUGIN_FILE', __FILE__);
define('ALNASEEG_BRANCH_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ALNASEEG_BRANCH_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ALNASEEG_BRANCH_MANAGER_PLUGIN_VERSION', '0.1.0');

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/bootstrap/bootstrap.php';

/**
 * Boot the plugin.
 */
(new Plugin())->boot();