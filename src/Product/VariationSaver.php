<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

use wpdb;

/**
 * Handles saving branch data for product variations.
 */
final class VariationSaver
{
    /**
     * Register WordPress hooks.
     */
    public function register(): void
    {
        add_action(
            'woocommerce_save_product_variation',
            [$this, 'save'],
            10,
            2
        );
    }

    /**
     * Save variation branch data.
     */
    public function save(
        int $variationId,
        int $loop
    ): void {

        if (! current_user_can(
            'edit_product',
            $variationId
        )) {
            return;
        }

        if (
            ! isset($_POST['wcbm_branch_nonce'])
        ) {
            return;
        }

        $nonce = sanitize_text_field(
            wp_unslash(
                $_POST['wcbm_branch_nonce']
            )
        );

        if (
            ! wp_verify_nonce(
                $nonce,
                'wcbm_save_product_branches'
            )
        ) {
            return;
        }

        if (
            ! isset($_POST['wcbm_variation'])
            || ! is_array($_POST['wcbm_variation'])
        ) {
            return;
        }

        if (
            ! isset($_POST['wcbm_variation'][$variationId])
            || ! is_array($_POST['wcbm_variation'][$variationId])
        ) {
            return;
        }

        $sanitizer = new BranchDataSanitizer();

        $branches = [];

        foreach (
            $_POST['wcbm_variation'][$variationId]
            as $branchId => $branch
        ) {

            if (! is_array($branch)) {
                continue;
            }

            $branches[(int) $branchId] = $sanitizer->sanitize(
                $branch
            );
        }

        global $wpdb;

        $repository = new ProductRepository(
            $wpdb
        );

        $repository->save(
            $variationId,
            $branches
        );
    }
}