<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Branch;

use Alnaseeg\BranchManager\Contracts\BranchRepositoryInterface;
use wpdb;

final class BranchRepository implements BranchRepositoryInterface
{
    /**
     * WordPress database instance.
     */
    public function __construct(
        private readonly wpdb $database
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?Branch
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function findBySlug(string $slug): ?Branch
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return [];
    }
}