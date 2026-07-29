<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Cart;

use Alnaseeg\BranchManager\Branch\BranchResolver;
use Alnaseeg\BranchManager\Product\ProductRepository;

/**
 * Validates cart operations against branch product data.
 */
final class CartValidator
{
    public function __construct(
        private readonly BranchResolver $branchResolver,
        private readonly ProductRepository $productRepository
    ) {
    }

    /**
     * Register WooCommerce validation hooks.
     */
    public function register(): void
    {
        add_filter(
            'woocommerce_add_to_cart_validation',
            [$this, 'validateAddToCart'],
            10,
            5
        );
    }

    /**
     * Validate adding a product to the cart.
     *
     * @param bool  $passed
     * @param int   $productId
     * @param int   $quantity
     * @param int   $variationId
     * @param array<string,mixed> $variations
     */
    public function validateAddToCart(
        bool $passed,
        int $productId,
        int $quantity,
        int $variationId = 0,
        array $variations = []
    ): bool {

        if (!$passed) {
            return false;
        }

        $branch = $this->branchResolver->resolve();

        /*
         * No branch context means this validator
         * should not interfere yet.
         *
         * Shop/product/category handling will be
         * implemented separately.
         */
        if ($branch === null) {
            return $passed;
        }

        /*
         * For variations, use the variation ID.
         * For simple products, use the product ID.
         */
        $resolvedProductId = $variationId > 0
            ? $variationId
            : $productId;

        $branchData = $this->productRepository->findBranch(
            $resolvedProductId,
            $branch->id()
        );

        /*
         * findBranch() only returns enabled products.
         *
         * Therefore NULL means that the product is
         * not available for this branch.
         */
        if ($branchData === null) {

            wc_add_notice(
                __(
                    'This product is not available in the selected branch.',
                    'alnaseeg'
                ),
                'error'
            );

            return false;
        }

        /*
         * Product explicitly marked out of stock.
         */
        if (
            !(bool) $branchData['manage_stock']
            && (string) $branchData['stock_status'] === 'outofstock'
        ) {

            wc_add_notice(
                __(
                    'This product is currently out of stock in this branch.',
                    'alnaseeg'
                ),
                'error'
            );

            return false;
        }

        /*
         * Quantity validation only applies when
         * branch stock management is enabled.
         */
        if (!(bool) $branchData['manage_stock']) {
            return $passed;
        }

        $availableQuantity = (int) $branchData['stock_quantity'];

        if ($availableQuantity <= 0) {

            wc_add_notice(
                __(
                    'This product is currently out of stock in this branch.',
                    'alnaseeg'
                ),
                'error'
            );

            return false;
        }

        /*
         * Include the quantity already present
         * in the customer's cart.
         */
        $quantityInCart = $this->quantityInCart(
            $resolvedProductId
        );

        $requestedQuantity = $quantityInCart + $quantity;

        if ($requestedQuantity > $availableQuantity) {

            wc_add_notice(
                sprintf(
                    /* translators: %d: available stock quantity */
                    __(
                        'Only %d item(s) are available in this branch.',
                        'alnaseeg'
                    ),
                    $availableQuantity
                ),
                'error'
            );

            return false;
        }

        return $passed;
    }

    /**
     * Get current quantity of a product in the cart.
     */
    private function quantityInCart(
        int $productId
    ): int {

        if (
            !function_exists('WC')
            || WC()->cart === null
        ) {
            return 0;
        }

        $quantity = 0;

        foreach (WC()->cart->get_cart() as $cartItem) {

            $cartProductId = !empty($cartItem['variation_id'])
                ? (int) $cartItem['variation_id']
                : (int) $cartItem['product_id'];

            if ($cartProductId !== $productId) {
                continue;
            }

            $quantity += (int) $cartItem['quantity'];
        }

        return $quantity;
    }
}