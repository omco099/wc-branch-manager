<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

use wpdb;

/**
 * Handles persistence for product branch data.
 */
final class ProductRepository
{
    /**
     * Product branch table.
     */
    private string $table;

    public function __construct(
        private readonly wpdb $database
    ) {
        $this->table = $database->prefix . 'wcbm_product_branch';
    }

    /**
     * Get all branch data for a product.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findByProduct(int $productId): array
    {
        $rows = $this->database->get_results(
            $this->database->prepare(
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
                'regular_price'  => (float) $row['regular_price'],
                'sale_price'     => $row['sale_price'] === '' ? '' : (float) $row['sale_price'],
                'stock_quantity' => (int) $row['stock_quantity'],
                'manage_stock'   => (bool) $row['manage_stock'],
                'stock_status'   => (string) $row['stock_status'],
                'is_enabled'     => (bool) $row['is_enabled'],
            ];
        }

        return $branches;
    }

    /**
     * Get branch data for a single product branch.
     *
     * @return array<string,mixed>|null
     */
    public function findBranch(
        int $productId,
        int $branchId
    ): ?array {

        $row = $this->database->get_row(
            $this->database->prepare(
                "
                SELECT
                    regular_price,
                    sale_price,
                    stock_quantity,
                    manage_stock,
                    stock_status,
                    is_enabled
                FROM {$this->table}
                WHERE product_id = %d
                  AND branch_id = %d
                  AND is_enabled = 1
                LIMIT 1
                ",
                $productId,
                $branchId
            ),
            ARRAY_A
        );

        if ($row === null) {
            return null;
        }

        return [
            'regular_price'  => (float) $row['regular_price'],
            'sale_price'     => $row['sale_price'] === '' ? '' : (float) $row['sale_price'],
            'stock_quantity' => (int) $row['stock_quantity'],
            'manage_stock'   => (bool) $row['manage_stock'],
            'stock_status'   => (string) $row['stock_status'],
            'is_enabled'     => (bool) $row['is_enabled'],
        ];
    }
    
    /**
     * Get enabled product IDs for a branch.
     *
     * @return int[]
     */
    public function findProductsByBranch(int $branchId): array
    {
        $productIds = $this->database->get_col(
            $this->database->prepare(
                "
                SELECT product_id
                FROM {$this->table}
                WHERE branch_id = %d
                  AND is_enabled = 1
                ORDER BY product_id ASC
                ",
                $branchId
            )
        );

        if (empty($productIds)) {
            return [];
        }

        return array_map('intval', $productIds);
    }

    /**
     * Persist branch data for a product.
     *
     * @param array<int,array<string,mixed>> $branches
     */
    public function save(
        int $productId,
        array $branches
    ): void {

        $existing = $this->database->get_col(
            $this->database->prepare(
                "
                SELECT branch_id
                FROM {$this->table}
                WHERE product_id = %d
                ",
                $productId
            )
        );

        $existing = array_flip(array_map('intval', $existing));

        foreach ($branches as $branchId => $branch) {

            $branchId = (int) $branchId;

            if (isset($existing[$branchId])) {

                $this->update(
                    $productId,
                    $branchId,
                    $branch
                );

                continue;
            }

            $this->insert(
                $productId,
                $branchId,
                $branch
            );
        }
    }

    /**
     * Insert a new branch record.
     *
     * @param array<string,mixed> $branch
     */
    private function insert(
        int $productId,
        int $branchId,
        array $branch
    ): void {

        $this->database->insert(
            $this->table,
            $this->prepareData(
                $productId,
                $branchId,
                $branch
            ),
            $this->formats()
        );
    }

    /**
     * Update an existing branch record.
     *
     * @param array<string,mixed> $branch
     */
    private function update(
        int $productId,
        int $branchId,
        array $branch
    ): void {

        $this->database->update(
            $this->table,
            $this->prepareData(
                $productId,
                $branchId,
                $branch
            ),
            [
                'product_id' => $productId,
                'branch_id'  => $branchId,
            ],
            $this->formats(),
            [
                '%d',
                '%d',
            ]
        );
    }

    /**
     * Prepare row data.
     *
     * @param array<string,mixed> $branch
     * @return array<string,mixed>
     */
    private function prepareData(
        int $productId,
        int $branchId,
        array $branch
    ): array {

        return [
            'product_id'     => $productId,
            'branch_id'      => $branchId,
            'regular_price'  => $branch['regular_price'],
            'sale_price'     => $branch['sale_price'],
            'stock_quantity' => $branch['stock_quantity'],
            'manage_stock'   => $branch['manage_stock'],
            'stock_status'   => $branch['stock_status'],
            'is_enabled'     => $branch['is_enabled'],
            'updated_at'     => current_time('mysql'),
        ];
    }

    /**
     * Database formats.
     *
     * @return array<int,string>
     */
    private function formats(): array
    {
        return [
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
    }
}