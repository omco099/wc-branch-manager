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
         */
        add_action(
            'woocommerce_check_cart_items',
            [$this, 'synchronizeCart'],
            1
        );

        /*
         * Remove stale WooCommerce notices generated
         * when products from a previous branch become
         * non-purchasable.
         */
        add_action(
            'wp_loaded',
            [$this, 'clearStaleBranchNotices'],
            99
        );
    }

    /**
     * Remove cart items that do not belong
     * to the currently active branch.
     */
    public function synchronizeCart(): void
    {
        if ($this->synchronizing) {
            return;
        }

        if (
            is_admin()
            && !wp_doing_ajax()
        ) {
            return;
        }

        if (!function_exists('WC')) {
            return;
        }

        $cart = WC()->cart;

        if ($cart === null) {
            return;
        }

        if ($cart->is_empty()) {
            return;
        }

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
                 * findBranch() returns NULL when the product
                 * is disabled or unavailable in this branch.
                 */
                $branchData = $this->productRepository->findBranch(
                    $productId,
                    $branchId
                );

                if ($branchData !== null) {
                    continue;
                }

                /*
                 * Remove products belonging to another branch
                 * without generating our own notice.
                 */
                $cart->remove_cart_item(
                    $cartItemKey
                );
            }

        } finally {
            $this->synchronizing = false;
        }
    }

    /**
     * Remove stale WooCommerce notices caused by products
     * becoming non-purchasable after switching branches.
     *
     * Other WooCommerce errors and notices are preserved.
     */
    public function clearStaleBranchNotices(): void
    {
        if (
            is_admin()
            && !wp_doing_ajax()
        ) {
            return;
        }

        if (!function_exists('WC')) {
            return;
        }

        $session = WC()->session;

        if ($session === null) {
            return;
        }

        $notices = $session->get(
            'wc_notices',
            []
        );

        if (!is_array($notices) || $notices === []) {
            return;
        }

        $changed = false;

        foreach ($notices as $noticeType => $typeNotices) {

            if (!is_array($typeNotices)) {
                continue;
            }

            foreach ($typeNotices as $index => $notice) {

                $message = '';

                if (is_array($notice)) {
                    $message = isset($notice['notice'])
                        ? wp_strip_all_tags((string) $notice['notice'])
                        : '';
                } elseif (is_string($notice)) {
                    $message = wp_strip_all_tags($notice);
                }

                if ($message === '') {
                    continue;
                }

                /*
                 * WooCommerce generates this message when
                 * cart validation finds a product that is
                 * no longer purchasable.
                 *
                 * In our plugin this can happen naturally
                 * when the customer switches branches.
                 */
                if (
                    stripos(
                        $message,
                        'can no longer be purchased'
                    ) === false
                ) {
                    continue;
                }

                unset(
                    $notices[$noticeType][$index]
                );

                $changed = true;
            }

            if (
                isset($notices[$noticeType])
                && is_array($notices[$noticeType])
            ) {
                $notices[$noticeType] = array_values(
                    $notices[$noticeType]
                );
            }
        }

        if (!$changed) {
            return;
        }

        /*
         * Keep all other WooCommerce notices intact.
         */
        $session->set(
            'wc_notices',
            $notices
        );
    }
}