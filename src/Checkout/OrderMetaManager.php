<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Checkout;

use Alnaseeg\BranchManager\Branch\BranchResolver;
use WC_Order;

defined('ABSPATH') || exit;

/**
 * Saves branch information into WooCommerce orders.
 */
final class OrderMetaManager
{
    public function __construct(
        private readonly BranchResolver $branchResolver
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
     * Save the current branch into the order metadata.
     */
    public function saveBranchMeta(
        WC_Order $order,
        array $_data
    ): void {
        $branch = $this->branchResolver->current();

        if ($branch === null) {
            return;
        }

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