<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Branch;

use wpdb;

/**
 * Resolves the current branch from the current request.
 */
final class BranchResolver
{
    /**
     * Branch repository.
     */
    private BranchRepository $branches;

    public function __construct()
    {
        global $wpdb;

        $this->branches = new BranchRepository($wpdb);
    }

    /**
     * Resolve current branch.
     */
    public function resolve(): ?Branch
    {
        $slug = $this->currentSlug();

        if ($slug === '') {
            return null;
        }

        return $this->branches->findBySlug($slug);
    }

    /**
     * Get current page slug.
     */
    private function currentSlug(): string
    {
        $post = get_queried_object();

        if (! $post instanceof \WP_Post) {
            return '';
        }

        return (string) $post->post_name;
    }
}