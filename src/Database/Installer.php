<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Database;

final class Installer
{
    public const DB_VERSION = '1.0.0';

    private const BRANCHES_TABLE = 'wcbm_branches';
    private const PRODUCT_BRANCH_TABLE = 'wcbm_product_branch';

    /**
     * Run database installation.
     */
    public function activate(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charsetCollate = $wpdb->get_charset_collate();

        $queries = [
            $this->getBranchesTableSql($wpdb->prefix, $charsetCollate),
            $this->getProductBranchTableSql($wpdb->prefix, $charsetCollate),
        ];

        dbDelta($queries);

        update_option(
            'wcbm_db_version',
            self::DB_VERSION
        );
    }

    /**
     * Branches table.
     */
    private function getBranchesTableSql(
        string $prefix,
        string $charsetCollate
    ): string {

        $table = $prefix . self::BRANCHES_TABLE;

        return "CREATE TABLE {$table} (

            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

            name VARCHAR(255) NOT NULL,

            slug VARCHAR(255) NOT NULL,

            page_id BIGINT UNSIGNED NULL,

            status VARCHAR(20) NOT NULL DEFAULT 'active',

            created_at DATETIME NOT NULL,

            updated_at DATETIME NOT NULL,

            PRIMARY KEY (id),

            UNIQUE KEY slug (slug),

            KEY page_id (page_id)

        ) {$charsetCollate};";
    }

    /**
     * Product branch table.
     */
    private function getProductBranchTableSql(
        string $prefix,
        string $charsetCollate
    ): string {

        $table = $prefix . self::PRODUCT_BRANCH_TABLE;

        return "CREATE TABLE {$table} (

            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

            product_id BIGINT UNSIGNED NOT NULL,

            branch_id BIGINT UNSIGNED NOT NULL,

            regular_price DECIMAL(10,2) NULL,

            sale_price DECIMAL(10,2) NULL,

            stock_quantity INT UNSIGNED NULL,

            manage_stock TINYINT(1) NOT NULL DEFAULT 0,

            stock_status VARCHAR(20) NOT NULL DEFAULT 'instock',

            is_enabled TINYINT(1) NOT NULL DEFAULT 1,

            updated_at DATETIME NOT NULL,

            PRIMARY KEY (id),

            UNIQUE KEY product_branch (product_id, branch_id),

            KEY product_id (product_id),

            KEY branch_id (branch_id)

        ) {$charsetCollate};";
    }
}