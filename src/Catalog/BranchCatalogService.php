<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Catalog;

use WP_Query;

/**
 * Filters WooCommerce catalog queries
 * according to the current branch.
 */
final class BranchCatalogFilter
{
    public function __construct(
        private readonly BranchCatalogService $catalog
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
            [$this, 'filterWooCommerceQuery'],
            20
        );
    }

    /**
     * Filter the main frontend query.
     */
    public function filterMainQuery(
        WP_Query $query
    ): void {

        if (is_admin()) {
            return;
        }

        if (!$query->is_main_query()) {
            return;
        }

        if (!$this->isCatalogQuery($query)) {
            return;
        }

        $this->applyFilter($query);
    }

    /**
     * Filter WooCommerce product queries.
     */
    public function filterWooCommerceQuery(
        WP_Query $query
    ): void {

        if (is_admin()) {
            return;
        }

        $this->applyFilter($query);
    }

    /**
     * Determine whether this query belongs
     * to the WooCommerce catalog.
     */
    private function isCatalogQuery(
        WP_Query $query
    ): bool {

        if ($query->is_post_type_archive('product')) {
            return true;
        }

        if ($query->is_tax('product_cat')) {
            return true;
        }

        if ($query->is_tax('product_tag')) {
            return true;
        }

        /*
         * WooCommerce product search.
         *
         * Kadence search will be handled
         * by SearchFilter.
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
     * Apply branch product restriction.
     */
    private function applyFilter(
        WP_Query $query
    ): void {

        if (!$this->catalog->hasBranch()) {
            return;
        }

        $productIds = $this->catalog->queryProductIds();

        $existingIds = $query->get('post__in');

        if (
            is_array($existingIds)
            && $existingIds !== []
        ) {

            $productIds = array_values(
                array_intersect(
                    array_map(
                        'intval',
                        $existingIds
                    ),
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