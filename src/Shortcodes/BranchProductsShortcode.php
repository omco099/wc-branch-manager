<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Shortcodes;

use Alnaseeg\BranchManager\Branch\BranchResolver;
use Alnaseeg\BranchManager\Product\ProductRepository;
use WP_Query;

/**
 * Handles the branch products shortcode.
 */
final class BranchProductsShortcode
{
    /**
     * Create a new shortcode instance.
     */
    public function __construct(
        private readonly BranchResolver $branchResolver,
        private readonly ProductRepository $productRepository
    ) {
    }

    /**
     * Register the shortcode.
     */
    public function register(): void
    {
        add_shortcode(
            'branch_products',
            [$this, 'render']
        );
    }

    /**
     * Render the branch products shortcode.
     *
     * @param array<string,mixed> $attributes
     */
    public function render(
        array $attributes = [],
        ?string $content = null
    ): string {
        if (!function_exists('WC')) {
            return '';
        }

        $branch = $this->branchResolver->resolve();

        if ($branch === null) {
            return '';
        }

        $productIds = $this->productRepository->findProductsByBranch(
            $branch->id()
        );

        if ($productIds === []) {
            return '';
        }

        $query = new WP_Query([
            'post_type'              => 'product',
            'post_status'            => 'publish',
            'post__in'               => $productIds,
            'posts_per_page'         => -1,
            'orderby'                => 'post__in',
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
        ]);

        if (!$query->have_posts()) {
            return '';
        }

        ob_start();

        woocommerce_product_loop_start();

        while ($query->have_posts()) {
            $query->the_post();

            wc_get_template_part(
                'content',
                'product'
            );
        }

        woocommerce_product_loop_end();

        wp_reset_postdata();

        return (string) ob_get_clean();
    }
}