<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Catalog;

use Alnaseeg\BranchManager\Branch\BranchResolver;
use Alnaseeg\BranchManager\Product\ProductRepository;
use WP_Query;

/**
 * Filters WooCommerce catalog queries
 * according to the currently active branch.
 */
final class BranchCatalogFilter
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
            'pre_get_posts',
            [$this, 'filterMainQuery'],
            20
        );

        add_action(
            'woocommerce_product_query',
            [$this, 'filterProductQuery'],
            20
        );
    }

    /**
     * Filter frontend queries.
     */
    public function filterMainQuery(WP_Query $query): void
    {
        if (is_admin()) {
            return;
        }

        if (!$query->is_main_query()) {
            return;
        }

        /*
         * Shop archive.
         */
        if ($query->is_post_type_archive('product')) {
            $this->applyBranchProducts($query);
            return;
        }

        /*
         * Product category.
         */
        if ($query->is_tax('product_cat')) {
            $this->applyBranchProducts($query);
            return;
        }

        /*
         * Product tag.
         */
        if ($query->is_tax('product_tag')) {
            $this->applyBranchProducts($query);
            return;
        }

        /*
         * WooCommerce search.
         */
        $postType = $query->get('post_type');

        if (
            $query->is_search()
            && (
                $postType === 'product'
                || (
                    is_array($postType)
                    && in_array('product', $postType, true)
                )
            )
        ) {
            $this->applyBranchProducts($query);
        }
    }

    /**
     * WooCommerce product query.
     */
    public function filterProductQuery(WP_Query $query): void
    {
        if (is_admin()) {
            return;
        }

        $this->applyBranchProducts($query);
    }

    /**
     * Restrict query to current branch products.
     */
    private function applyBranchProducts(WP_Query $query): void
    {
        $branch = $this->branchResolver->resolve();

        if ($branch === null) {
            return;
        }

        $productIds = $this->productRepository->findProductsByBranch(
            $branch->id()
        );

        if ($productIds === []) {
            $productIds = [0];
        }

        $productIds = array_values(
            array_unique(
                array_map('intval', $productIds)
            )
        );

        $existing = $query->get('post__in');

        if (
            is_array($existing)
            && $existing !== []
        ) {

            $productIds = array_values(
                array_intersect(
                    array_map('intval', $existing),
                    $productIds
                )
            );

            if ($productIds === []) {
                $productIds = [0];
            }
        }

        $query->set(
            'post__in',
            $productIds
        );
    }
}