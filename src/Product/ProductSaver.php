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

        $sanitizer = new BranchDataSanitizer();

        foreach ($_POST['wcbm_branch'] as $branchId => $branch) {

            if (! is_array($branch)) {
                continue;
            }

            $branches[(int) $branchId] = $sanitizer->sanitize(
                $branch
            );
        }

        return $branches;
    }
}