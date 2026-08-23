<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Checkout;

use Alnaseeg\BranchManager\Branch\BranchRepository;
use WC_Order;

/**
 * Saves branch information into WooCommerce orders.
 */
final class OrderMetaManager
{
    public function __construct(
        private readonly BranchRepository $branchRepository
    ) {
    }

    /**
     * Register hooks.
     */
    public function register(): void
    {
        add_action(
            'woocommerce_checkout_create_order',
            [$this, 'saveBranchMeta'],
            10,
            2
        );
    }

    /**
     * Save the cart branch into the order metadata.
     *
     * The cart is guaranteed to contain products
     * from one branch only.
     */
    public function saveBranchMeta(
        WC_Order $order,
        array $_data
    ): void {

        if (! function_exists('WC')) {
            return;
        }

        $cart = WC()->cart;

        if ($cart === null || $cart->is_empty()) {
            return;
        }

        $branchId = null;

        /*
         * All cart items must belong to the same branch.
         *
         * We only need the branch from the first item because
         * CartValidator prevents mixing different branches.
         */
        foreach ($cart->get_cart() as $cartItem) {

            if (! isset($cartItem['wcbm_branch_id'])) {
                continue;
            }

            $currentBranchId = absint(
                $cartItem['wcbm_branch_id']
            );

            if ($currentBranchId <= 0) {
                continue;
            }

            $branchId = $currentBranchId;

            break;
        }

        if ($branchId === null) {
            return;
        }

        $branch = $this->branchRepository->findById(
            $branchId
        );

        if ($branch === null) {
            return;
        }

        /*
         * Save branch information to the order.
         */
        $order->update_meta_data(
            '_wcbm_branch_id',
            $branch->id()
        );

        $order->update_meta_data(
            '_wcbm_branch_name',
            $branch->name()
        );

        $order->update_meta_data(
            '_wcbm_branch_slug',
            $branch->slug()
        );
    }
}