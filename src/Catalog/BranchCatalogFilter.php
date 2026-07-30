<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Catalog;

use Alnaseeg\BranchManager\Branch\BranchResolver;
use Alnaseeg\BranchManager\Product\ProductRepository;
use WP_Query;

/**
 * Filters the WooCommerce catalog according
 * to the currently active branch.
 */
final class BranchCatalogFilter
{
    public function __construct(
        private readonly BranchResolver $branchResolver,
        private readonly ProductRepository $productRepository
    ) {
    }

    /**
     * Register catalog filtering hooks.
     */
    public function register(): void
    {
        /*
         * Filter the main WordPress / WooCommerce
         * catalog query before it is executed.
         */
        add_action(
            'pre_get_posts',
            [$this, 'filterMainQuery'],
            20
        );

        /*
         * Keep WooCommerce's dedicated product query
         * covered as a secondary layer.
         */
        add_action(
            'woocommerce_product_query',
            [$this, 'filterProductQuery'],
            20
        );
    }

    /**
     * Filter the main frontend catalog query.
     *
     * Covers:
     * - Shop
     * - Product categories
     * - Product tags
     * - Product search
     */
    public function filterMainQuery(WP_Query $query): void
    {
        if (is_admin()) {
            return;
        }

        if (!$query->is_main_query()) {
            return;
        }

        if (!$this->isCatalogQuery($query)) {
            return;
        }

        $this->applyBranchProducts(
            $query
        );
    }

    /**
     * Filter WooCommerce product queries.
     */
    public function filterProductQuery(WP_Query $query): void
    {
        if (is_admin()) {
            return;
        }

        $this->applyBranchProducts(
            $query
        );
    }

    /**
     * Determine whether the query belongs
     * to the WooCommerce catalog.
     */
    private function isCatalogQuery(WP_Query $query): bool
    {
        /*
         * Shop archive.
         */
        if ($query->is_post_type_archive('product')) {
            return true;
        }

        /*
         * Product category archive.
         */
        if ($query->is_tax('product_cat')) {
            return true;
        }

        /*
         * Product tag archive.
         */
        if ($query->is_tax('product_tag')) {
            return true;
        }

        /*
         * Product search.
         */
        if ($query->is_search()) {

            $postType = $query->get('post_type');

            if ($postType === 'product') {
                return true;
            }

            if (
                is_array($postType)
                && in_array(
                    'product',
                    $postType,
                    true
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Restrict a product query to products
     * enabled in the currently active branch.
     */
    private function applyBranchProducts(WP_Query $query): void
    {
        $branch = $this->branchResolver->resolve();

        /*
         * No active branch:
         * do not modify WooCommerce yet.
         */
        if ($branch === null) {
            return;
        }

        $branchProductIds = $this->productRepository->findProductsByBranch(
            $branch->id()
        );

        /*
         * WP_Query treats an empty post__in array
         * as "no restriction".
         *
         * [0] guarantees no products are returned.
         */
        if ($branchProductIds === []) {
            $branchProductIds = [0];
        }

        $branchProductIds = array_values(
            array_unique(
                array_map(
                    'intval',
                    $branchProductIds
                )
            )
        );

        /*
         * Respect restrictions already applied by
         * WooCommerce, the theme or another plugin.
         */
        $existingProductIds = $query->get(
            'post__in'
        );

        if (
            is_array($existingProductIds)
            && $existingProductIds !== []
        ) {

            $existingProductIds = array_map(
                'intval',
                $existingProductIds
            );

            $branchProductIds = array_values(
                array_intersect(
                    $existingProductIds,
                    $branchProductIds
                )
            );

            /*
             * The intersection may contain nothing.
             */
            if ($branchProductIds === []) {
                $branchProductIds = [0];
            }
        }

        $query->set(
            'post__in',
            $branchProductIds
        );
    }
}