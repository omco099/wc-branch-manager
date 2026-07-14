<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

/**
 * Registers the Branch Data panel inside WooCommerce Product Data.
 */
final class ProductDataPanel
{
    /**
     * Register WordPress hooks.
     */
    public function register(): void
    {
        add_action(
            'woocommerce_product_data_panels',
            [$this, 'render']
        );
    }

    /**
     * Render the Branch Data panel.
     */
    public function render(): void
    {
        ?>

        <div
            id="wcbm_branch_data_panel"
            class="panel woocommerce_options_panel hidden"
        >

            <div class="options_group">

                <p>

                    <strong>

                        <?php esc_html_e(
                            'Branch Data',
                            'alnaseeg-branch-manager'
                        ); ?>

                    </strong>

                </p>

                <p>

                    <?php esc_html_e(
                        'Branch pricing and stock fields will appear here.',
                        'alnaseeg-branch-manager'
                    ); ?>

                </p>

            </div>

        </div>

        <?php
    }
}