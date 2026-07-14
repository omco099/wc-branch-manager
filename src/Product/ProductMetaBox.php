<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

use WP_Post;

/**
 * Registers and renders the Branch Pricing meta box
 * on WooCommerce product edit screens.
 */
final class ProductMetaBox
{
    /**
     * Register WordPress hooks.
     */
    public function register(): void
    {
        add_action(
            'add_meta_boxes',
            [$this, 'addMetaBox']
        );
    }

    /**
     * Register the meta box.
     */
    public function addMetaBox(): void
    {
        add_meta_box(
            'wcbm-branch-pricing',
            __('Branch Pricing & Stock', 'alnaseeg-branch-manager'),
            [$this, 'render'],
            'product',
            'normal',
            'default'
        );
    }

    /**
     * Render the meta box.
     */
    public function render(WP_Post $post): void
    {
        wp_nonce_field(
            'wcbm_save_product_branches',
            'wcbm_nonce'
        );

        ?>

        <div class="wcbm-product-branches">

            <p>

                <strong><?php esc_html_e('Branch pricing will appear here.', 'alnaseeg-branch-manager'); ?></strong>

            </p>

            <p>

                <?php esc_html_e(
                    'Next step: build pricing and stock fields for the three branches.',
                    'alnaseeg-branch-manager'
                ); ?>

            </p>

        </div>

        <?php
    }
}
