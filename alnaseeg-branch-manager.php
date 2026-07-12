<?php

declare(strict_types=1);

/*
Plugin Name: Alnaseeg Branch Manager
Description: Initial plugin architecture skeleton.
Version: 0.1.0
Author: Alnaseeg
*/

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('ALNASEEG_BRANCH_MANAGER_PLUGIN_FILE')) {
    define('ALNASEEG_BRANCH_MANAGER_PLUGIN_FILE', __FILE__);
}

if (!defined('ALNASEEG_BRANCH_MANAGER_PLUGIN_DIR')) {
    define('ALNASEEG_BRANCH_MANAGER_PLUGIN_DIR', __DIR__ . '/');
}

if (!defined('ALNASEEG_BRANCH_MANAGER_PLUGIN_VERSION')) {
    define('ALNASEEG_BRANCH_MANAGER_PLUGIN_VERSION', '0.1.0');
}

if (!defined('ALNASEEG_BRANCH_MANAGER_PLUGIN_NAME')) {
    define('ALNASEEG_BRANCH_MANAGER_PLUGIN_NAME', 'Alnaseeg Branch Manager');
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

require_once __DIR__ . '/bootstrap/bootstrap.php';
