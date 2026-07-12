<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Database;

final class Installer
{
    /**
     * Run the database installation routine.
     */
    public function activate(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $queries         = array(
            $this->get_branches_table_sql($wpdb->prefix, $charset_collate),
            $this->get_product_branch_table_sql($wpdb->prefix, $charset_collate),
        );

        dbDelta($queries);
    }

    /**
     * Build the branches table SQL.
     */
    private function get_branches_table_sql(string $prefix, string $charset_collate): string
    {
        $table_name = $prefix . 'wcbm_branches';

        return "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            page_id bigint(20) unsigned NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY  (id),
            KEY slug (slug),
            KEY page_id (page_id)
        ) {$charset_collate};";
    }

    /**
     * Build the product branch table SQL.
     */
    private function get_product_branch_table_sql(string $prefix, string $charset_collate): string
    {
        $table_name = $prefix . 'wcbm_product_branch';

        return "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            product_id bigint(20) unsigned NOT NULL,
            branch_id bigint(20) unsigned NOT NULL,
            price decimal(10,2) NULL,
            sale_price decimal(10,2) NULL,
            stock_quantity bigint(20) NULL,
            manage_stock tinyint(1) NOT NULL DEFAULT 0,
            stock_status varchar(20) NOT NULL DEFAULT 'instock',
            enabled tinyint(1) NOT NULL DEFAULT 1,
            updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY branch_id (branch_id)
        ) {$charset_collate};";
    }
}
