<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Checkout;

use Alnaseeg\BranchManager\Branch\BranchRepository;
use WC_Order;
use WC_Order_Item_Product;

/**
 * Handles branch information for WooCommerce orders.
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
        /*
         * Save branch information when the order is created.
         */
        add_action(
            'woocommerce_checkout_create_order',
            [$this, 'saveBranchMeta'],
            10,
            2
        );

        /*
         * Display the branch under the product
         * inside the WooCommerce order items table.
         */
        add_action(
            'woocommerce_after_order_itemmeta',
            [$this, 'displayBranchInOrderItem'],
            10,
            3
        );
    }

    /**
     * Save the cart branch into the order metadata.
     *
     * The cart is restricted to one branch only.
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
         * All products in the cart must belong
         * to the same branch.
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

    /**
     * Display branch name below the product
     * in the WooCommerce order items table.
     *
     * @param int                    $itemId
     * @param WC_Order_Item_Product  $item
     * @param WC_Product|null        $product
     */
    public function displayBranchInOrderItem(
        int $itemId,
        $item,
        $product = null
    ): void {

        if (! $item instanceof WC_Order_Item_Product) {
            return;
        }

        $order = $item->get_order();

        if (! $order instanceof WC_Order) {
            return;
        }

        $branchName = $order->get_meta(
            '_wcbm_branch_name',
            true
        );

        if ($branchName === '') {
            return;
        }

        ?>
        <div class="wcbm-order-item-branch">
            <strong>
                <?php esc_html_e(
                    'Branch:',
                    'alnaseeg-branch-manager'
                ); ?>
            </strong>

            <?php echo esc_html($branchName); ?>
        </div>
        <?php
    }
}