<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Shortcodes;

use Alnaseeg\BranchManager\Branch\BranchResolver;
use Alnaseeg\BranchManager\Product\ProductRepository;

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
     */
    public function render(
        array $attributes = [],
        ?string $content = null
    ): string {
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

        if (!current_user_can('manage_woocommerce')) {
            return '';
        }

        $output = '<div class="abm-branch-products-debug">';

        $output .= '<strong>Branch:</strong> ';
        $output .= esc_html($branch->name());

        $output .= '<br>';

        $output .= '<strong>Branch ID:</strong> ';
        $output .= esc_html((string) $branch->id());

        $output .= '<br>';

        $output .= '<strong>Product IDs:</strong> ';
        $output .= esc_html(
            implode(', ', $productIds)
        );

        $output .= '</div>';

        return $output;
    }
}