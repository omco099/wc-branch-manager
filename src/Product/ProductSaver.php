<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

use wpdb;

/**
 * Handles saving branch product data.
 */
final class ProductSaver
{
    /**
     * Register WordPress hooks.
     */
    public function register(): void
    {
        add_action(
            'woocommerce_process_product_meta',
            [$this, 'save'],
            10,
            1
        );
    }

    /**
     * Save branch data.
     */
    public function save(int $productId): void
    {
        if (! $this->isValidRequest($productId)) {
            return;
        }

        $branches = $this->collectBranchData();

        global $wpdb;

        $repository = new ProductRepository($wpdb);

        $repository->save(
            $productId,
            $branches
        );
    }

    /**
     * Validate save request.
     */
    private function isValidRequest(int $productId): bool
    {
        if (! current_user_can('edit_product', $productId)) {
            return false;
        }

        if (! isset($_POST['wcbm_branch_nonce'])) {
            return false;
        }

        $nonce = sanitize_text_field(
            wp_unslash($_POST['wcbm_branch_nonce'])
        );

        if (
            ! wp_verify_nonce(
                $nonce,
                'wcbm_save_product_branches'
            )
        ) {
            return false;
        }

        if (
            ! isset($_POST['wcbm_branch'])
            || ! is_array($_POST['wcbm_branch'])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Collect branch data from request.
     *
     * @return array<int,array<string,mixed>>
     */
    private function collectBranchData(): array
    {
        $branches = [];

        foreach ($_POST['wcbm_branch'] as $branchId => $branch) {

            if (! is_array($branch)) {
                continue;
            }

            $branchId = (int) $branchId;

            $manageStock = ! empty($branch['manage_stock']);

            $stockQuantity = isset($branch['stock_quantity'])
                ? max(
                    0,
                    (int) wp_unslash((string) $branch['stock_quantity'])
                )
                : 0;

            $branches[$branchId] = [
                'regular_price' => $this->sanitizePrice(
                    $branch['regular_price'] ?? ''
                ),
                'sale_price' => $this->sanitizePrice(
                    $branch['sale_price'] ?? ''
                ),
                'stock_quantity' => $stockQuantity,
                'manage_stock' => $manageStock ? 1 : 0,
                'stock_status' => $this->determineStockStatus(
                    $manageStock,
                    $stockQuantity
                ),
                'is_enabled' => ! empty($branch['is_enabled']) ? 1 : 0,
            ];
        }

        return $branches;
    }

    /**
     * Sanitize price value.
     */
    private function sanitizePrice(
        mixed $value
    ): float|string {

        if (
            $value === null
            || $value === ''
        ) {
            return '';
        }

        return (float) wc_format_decimal(
            wp_unslash((string) $value)
        );
    }

    /**
     * Determine stock status.
     */
    private function determineStockStatus(
        bool $manageStock,
        int $stockQuantity
    ): string {

        if (! $manageStock) {
            return 'instock';
        }

        return $stockQuantity > 0
            ? 'instock'
            : 'outofstock';
    }
}