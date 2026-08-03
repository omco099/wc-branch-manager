<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

use Alnaseeg\BranchManager\Branch\BranchRepository;
use WP_Post;

/**
 * Registers branch fields inside WooCommerce variations.
 */
final class VariationDataPanel
{
    /**
     * Register WordPress hooks.
     */
    public function register(): void
    {
        add_action(
            'woocommerce_product_after_variable_attributes',
            [$this, 'render'],
            10,
            3
        );
    }

    /**
     * Render branch fields for a variation.
     *
     * @param int                 $loop
     * @param array<string,mixed> $variationData
     * @param WP_Post             $variation
     */
    public function render(
        int $loop,
        array $variationData,
        WP_Post $variation
    ): void {

        global $wpdb;

        $branchRepository = new BranchRepository(
            $wpdb
        );

        $productRepository = new ProductRepository(
            $wpdb
        );

        $variationFields = new VariationFields();

        $branches = $branchRepository->all();

        $productData = $productRepository->findByProduct(
            (int) $variation->ID
        );

        ?>

        <div class="wcbm-variation-panel">

            <h4 style="margin:16px 0 12px;">
                <?php esc_html_e(
                    'Branch Data',
                    'alnaseeg-branch-manager'
                ); ?>
            </h4>

            <?php

            $variationFields->render(
                $loop,
                (int) $variation->ID,
                $branches,
                $productData
            );

            ?>

        </div>

        <?php
    }
}