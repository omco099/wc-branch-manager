<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

use wpdb;

/**
 * Handles all database operations
 * for product branch data.
 */
final class ProductRepository
{
    /**
     * WordPress database instance.
     */
    private wpdb $db;

    /**
     * Product branch table.
     */
    private string $table;

    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;
        $this->table = $wpdb->prefix . 'wcbm_product_branch';
    }

    /**
     * Get all branch data for a product.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findByProduct(int $productId): array
    {
        $rows = $this->db->get_results(
            $this->db->prepare(
                "
                SELECT
                    branch_id,
                    regular_price,
                    sale_price,
                    stock_quantity,
                    manage_stock,
                    stock_status,
                    is_enabled
                FROM {$this->table}
                WHERE product_id = %d
                ",
                $productId
            ),
            ARRAY_A
        );

        if (empty($rows)) {
            return [];
        }

        $branches = [];

        foreach ($rows as $row) {

            $branches[(int) $row['branch_id']] = [

                'regular_price' => $row['regular_price'],

                'sale_price' => $row['sale_price'],

                'stock_quantity' => $row['stock_quantity'],

                'manage_stock' => (int) $row['manage_stock'],

                'stock_status' => $row['stock_status'],

                'enabled' => (int) $row['is_enabled'],
            ];
        }

        return $branches;
    }

    /**
     * Save branch data for a product.
     *
     * @param array<int,array<string,mixed>> $branches
     */
    public function save(
        int $productId,
        array $branches
    ): void {

        foreach ($branches as $branchId => $branch) {

            $exists = (int) $this->db->get_var(
                $this->db->prepare(
                    "
                    SELECT id
                    FROM {$this->table}
                    WHERE product_id = %d
                    AND branch_id = %d
                    ",
                    $productId,
                    $branchId
                )
            );

            $data = [

                'product_id' => $productId,

                'branch_id' => $branchId,

                'regular_price' => $branch['regular_price'] ?? null,

                'sale_price' => $branch['sale_price'] ?? null,

                'stock_quantity' => $branch['stock_quantity'] ?? null,

                'manage_stock' => empty($branch['manage_stock']) ? 0 : 1,

                'stock_status' => $branch['stock_status'] ?? 'instock',

                'is_enabled' => empty($branch['enabled']) ? 0 : 1,

                'updated_at' => current_time('mysql'),
            ];

            $format = [

                '%d',

                '%d',

                '%f',

                '%f',

                '%d',

                '%d',

                '%s',

                '%d',

                '%s',
            ];

            if ($exists > 0) {

                $this->db->update(

                    $this->table,

                    $data,

                    [

                        'product_id' => $productId,

                        'branch_id' => $branchId,

                    ],

                    $format,

                    [

                        '%d',

                        '%d',

                    ]
                );

                continue;
            }

            $this->db->insert(

                $this->table,

                $data,

                $format
            );
        }
    }
}