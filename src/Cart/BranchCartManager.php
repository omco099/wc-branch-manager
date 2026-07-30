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
        add_action(
            'wp_loaded',
            [$this, 'synchronizeCart'],
            20
        );
    }

    /**
     * Remove cart items that do not belong
     * to the currently active branch.
     */
    public function synchronizeCart(): void
    {
        /*
         * Frontend only.
         *
         * Allow WooCommerce AJAX requests because
         * cart operations may run through AJAX.
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

        /*
         * Cart must already be initialized.
         */
        $cart = WC()->cart;

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
         * Resolve the active branch.
         */
        $branch = $this->branchResolver->resolve();

        if ($branch === null) {
            return;
        }

        $branchId = $branch->id();

        /*
         * Inspect every item currently in the cart.
         */
        foreach ($cart->get_cart() as $cartItemKey => $cartItem) {

            $productId = !empty($cartItem['variation_id'])
                ? (int) $cartItem['variation_id']
                : (int) $cartItem['product_id'];

            if ($productId <= 0) {
                continue;
            }

            /*
             * findBranch() only returns a record when
             * the product is enabled for this branch.
             *
             * NULL therefore means that the product
             * must not remain in this branch's cart.
             */
            $branchData = $this->productRepository->findBranch(
                $productId,
                $branchId
            );

            if ($branchData !== null) {
                continue;
            }

            /*
             * Remove silently.
             *
             * We intentionally do not call wc_add_notice().
             * The customer has changed branch, so removing
             * products belonging to the previous branch is
             * expected application behaviour.
             */
            $cart->remove_cart_item(
                $cartItemKey
            );
        }
    }
}