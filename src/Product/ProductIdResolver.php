<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

use WC_Product;
use WC_Product_Variation;

/**
 * Resolves the correct product identifier.
 *
 * The Branch Manager stores branch data using
 * the WooCommerce product ID.
 *
 * For variable products this means:
 *
 * - Product          => Parent ID
 * - ProductVariation => Variation ID
 */
final class ProductIdResolver
{
    /**
     * Resolve the identifier that should be used
     * throughout the plugin.
     */
    public function resolve(
        WC_Product $product
    ): int {

        if ($product instanceof WC_Product_Variation) {
            return $product->get_id();
        }

        return $product->get_id();
    }

    /**
     * Determine whether the product
     * is a variation.
     */
    public function isVariation(
        WC_Product $product
    ): bool {
        return $product instanceof WC_Product_Variation;
    }

    /**
     * Return the parent product ID.
     *
     * For simple products this returns
     * the product ID itself.
     */
    public function parentId(
        WC_Product $product
    ): int {

        if ($product instanceof WC_Product_Variation) {
            return (int) $product->get_parent_id();
        }

        return $product->get_id();
    }
}