<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Cart;

use Alnaseeg\BranchManager\Branch\BranchResolver;
use Alnaseeg\BranchManager\Product\ProductRepository;

/**
 * Keeps the WooCommerce cart synchronized
 * with the currently active branch.
 */
final class BranchCartManager
{
    /**
     * Prevent duplicate synchronization
     * during the same request.
     */
    private bool $synchronizing = false;

    public function __construct(
        private readonly BranchResolver $branchResolver,
        private readonly ProductRepository $productRepository
    ) {
    }

    /**
     * Register hooks.
     */
    public function register(): void
    {
        /*
         * Normal frontend requests.
         */
        add_action(
            'wp_loaded',
            [$this, 'synchronizeCart'],
            20
        );

        /*
         * Run before WooCommerce validates the cart.
         *
         * This is especially important for Cart
         * and Checkout requests.
         */
        add_action(
            'woocommerce_check_cart_items',
            [$this, 'synchronizeCart'],
            1
        );
    }

    /**
     * Remove cart items that do not belong
     * to the currently active branch.
     */
    public function synchronizeCart(): void
    {
        /*
         * Prevent recursion / duplicate execution.
         */
        if ($this->synchronizing) {
            return;
        }

        /*
         * Frontend only.
         *
         * WooCommerce AJAX requests are allowed because
         * Checkout updates may run through AJAX.
         */
        if (
            is_admin()
            && !wp_doing_ajax()
        ) {
            return;
        }

        /*
         * WooCommerce must be available.
         */
        if (!function_exists('WC')) {
            return;
        }

        $cart = WC()->cart;

        /*
         * Cart has not been initialized yet.
         */
        if ($cart === null) {
            return;
        }

        /*
         * Nothing to synchronize.
         */
        if ($cart->is_empty()) {
            return;
        }

        /*
         * Resolve the currently active branch.
         */
        $branch = $this->branchResolver->resolve();

        if ($branch === null) {
            return;
        }

        $branchId = $branch->id();

        $this->synchronizing = true;

        try {

            foreach ($cart->get_cart() as $cartItemKey => $cartItem) {

                /*
                 * Variations use the variation ID.
                 * Simple products use the product ID.
                 */
                $productId = !empty($cartItem['variation_id'])
                    ? (int) $cartItem['variation_id']
                    : (int) $cartItem['product_id'];

                if ($productId <= 0) {
                    continue;
                }

                /*
                 * findBranch() returns NULL when:
                 *
                 * - Product does not belong to this branch.
                 * - Product is disabled in this branch.
                 */
                $branchData = $this->productRepository->findBranch(
                    $productId,
                    $branchId
                );

                if ($branchData !== null) {
                    continue;
                }

                /*
                 * Remove the old branch product silently.
                 *
                 * No wc_add_notice() is used because
                 * changing branch is expected behaviour.
                 */
                $cart->remove_cart_item(
                    $cartItemKey
                );
            }

        } finally {
            $this->synchronizing = false;
        }
    }
}