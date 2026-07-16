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
        error_log('========== WCBM SAVE START ==========');
        error_log('Product ID: ' . $productId);

        if (! $this->isValidRequest($productId)) {
            error_log('Validation failed.');
            return;
        }

        error_log('Validation passed.');

        $branches = $this->collectBranchData();

        error_log(print_r($branches, true));

        global $wpdb;

        $repository = new ProductRepository($wpdb);

        $repository->save(
            $productId,
            $branches
        );

        error_log('Repository save finished.');
        error_log('========== WCBM SAVE END ==========');
    }

    /**
     * Validate save request.
     */
    private function isValidRequest(int $productId): bool
    {
        if (! current_user_can('edit_product', $productId)) {
            error_log('Permission failed.');
            return false;
        }

        if (! isset($_POST['wcbm_branch_nonce'])) {
            error_log('Nonce field missing.');
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
            error_log('Nonce verification failed.');
            return false;
        }

        if (
            ! isset($_POST['wcbm_branch'])
            || ! is_array($_POST['wcbm_branch'])
        ) {
            error_log('Branch data missing.');
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