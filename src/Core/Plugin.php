<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Core;

use wpdb;
use Alnaseeg\BranchManager\Admin\Menu;
use Alnaseeg\BranchManager\Product\ProductDataPanel;
use Alnaseeg\BranchManager\Product\ProductDataTab;
use Alnaseeg\BranchManager\Product\ProductSaver;

/**
 * Main plugin application.
 */
final class Plugin
{
    /**
     * Boot the plugin.
     */
    public function boot(): void
    {
        $this->registerModules();
        $this->registerHooks();
    }

    /**
     * Register plugin modules.
     */
    private function registerModules(): void
    {
        (new ProductDataTab())->register();

        (new ProductDataPanel())->register();

        (new ProductSaver())->register();
    }

    /**
     * Register WordPress hooks.
     */
    private function registerHooks(): void
    {
        add_action(
            'admin_menu',
            [
                new Menu(),
                'register',
            ]
        );

        global $wpdb;

        /** @var wpdb $wpdb */
        $services = new Services($wpdb);

        /*
         * Version 1:
         * Branch Selector is intentionally disabled.
         *
         * The current branch is resolved from the current
         * Elementor branch page using PageBranchResolver.
         */

        /*
         * Register branch products shortcode.
         */
        $services->branchProductsShortcode()->register();

        add_action(
    'wp_footer',
    static function (): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div style="position:fixed;bottom:10px;right:10px;z-index:999999;background:#fff;padding:10px;border:2px solid #000;">';

        echo shortcode_exists('branch_products')
            ? 'branch_products: REGISTERED'
            : 'branch_products: NOT REGISTERED';

        echo '</div>';
    }
);
        /*
         * Register branch-specific product price filters.
         */
        $priceResolver = $services->productPriceResolver();

        add_filter(
            'woocommerce_product_get_price',
            static fn ($price, $product) => $priceResolver->price(
                $product->get_id(),
                $price
            ),
            10,
            2
        );

        add_filter(
            'woocommerce_product_get_regular_price',
            static fn ($price, $product) => $priceResolver->regularPrice(
                $product->get_id(),
                $price
            ),
            10,
            2
        );

        add_filter(
            'woocommerce_product_get_sale_price',
            static fn ($price, $product) => $priceResolver->salePrice(
                $product->get_id(),
                $price
            ),
            10,
            2
        );

        add_filter(
            'woocommerce_product_variation_get_price',
            static fn ($price, $product) => $priceResolver->price(
                $product->get_id(),
                $price
            ),
            10,
            2
        );

        add_filter(
            'woocommerce_product_variation_get_regular_price',
            static fn ($price, $product) => $priceResolver->regularPrice(
                $product->get_id(),
                $price
            ),
            10,
            2
        );

        add_filter(
            'woocommerce_product_variation_get_sale_price',
            static fn ($price, $product) => $priceResolver->salePrice(
                $product->get_id(),
                $price
            ),
            10,
            2
        );
    }
}