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
     * Register WordPress / WooCommerce hooks.
     */
    public function register(): void
    {
        add_action(
            'woocommerce_product_query',
            [$this, 'filterProductQuery'],
            20
        );

        add_action(
            'pre_get_posts',
            [$this, 'filterSearchQuery'],
            20
        );
    }

    /**
     * Filter the main WooCommerce catalog query.
     *
     * Applies to:
     * - Shop
     * - Product categories
     * - Product tags
     * - WooCommerce product archives
     */
    public function filterProductQuery(WP_Query $query): void
    {
        if (is_admin()) {
            return;
        }

        $branch = $this->branchResolver->resolve();

        if ($branch === null) {
            return;
        }

        $productIds = $this->productRepository->findProductsByBranch(
            $branch->id()
        );

        /*
         * Important:
         *
         * post__in = [] does NOT mean "return nothing"
         * in WP_Query.
         *
         * Therefore use [0] when the branch has
         * no enabled products.
         */
        if ($productIds === []) {
            $productIds = [0];
        }

        $existingIds = $query->get('post__in');

        /*
         * Another plugin/theme may already be restricting
         * the query to specific products.
         *
         * In that case use the intersection instead
         * of overwriting the existing restriction.
         */
        if (
            is_array($existingIds)
            && $existingIds !== []
        ) {
            $productIds = array_values(
                array_intersect(
                    array_map('intval', $existingIds),
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

    /**
     * Filter frontend product search results
     * according to the active branch.
     */
    public function filterSearchQuery(WP_Query $query): void
    {
        if (is_admin()) {
            return;
        }

        if (!$query->is_main_query()) {
            return;
        }

        if (!$query->is_search()) {
            return;
        }

        /*
         * Only interfere with searches that can
         * return WooCommerce products.
         */
        $postType = $query->get('post_type');

        if (
            $postType !== 'product'
            && $postType !== ['product']
        ) {
            return;
        }

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

        $existingIds = $query->get('post__in');

        if (
            is_array($existingIds)
            && $existingIds !== []
        ) {
            $productIds = array_values(
                array_intersect(
                    array_map('intval', $existingIds),
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