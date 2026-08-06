<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Cart;

use Alnaseeg\BranchManager\Branch\BranchResolver;
use Alnaseeg\BranchManager\Product\ProductRepository;

/**
 * Validates adding products to the cart
 * according to the current branch.
 */
final class CartValidator
{
    public function __construct(
        private readonly BranchResolver $branchResolver,
        private readonly ProductRepository $productRepository
    ) {
    }

    /**
     * Register WooCommerce hooks.
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
     * @param array<string,mixed> $variations
     */
    public function validateAddToCart(
        bool $passed,
        int $productId,
        int $quantity,
        int $variationId = 0,
        array $variations = []
    ): bool {

        if (! $passed) {
            return false;
        }

        $branch = $this->branchResolver->resolve();

        /*
         * No branch selected.
         */
        if ($branch === null) {
            return $passed;
        }

        /*
         * Variable products use the parent product.
         * WooCommerce handles the selected variation.
         */
        $productId = $variationId > 0
            ? wp_get_post_parent_id($variationId)
            : $productId;

        $branchData = $this->productRepository->findBranch(
            $productId,
            $branch->id()
        );

        if (
            $branchData === null
            || empty($branchData['is_enabled'])
        ) {

            wc_add_notice(
                __(
                    'This product is not available in the selected branch.',
                    'alnaseeg-branch-manager'
                ),
                'error'
            );

            return false;
        }

        return true;
    }
}