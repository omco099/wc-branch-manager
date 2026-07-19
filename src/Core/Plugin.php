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