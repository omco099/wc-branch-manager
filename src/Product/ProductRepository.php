<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

/**
 * Repository scaffold for product branch data.
 */
final class ProductRepository
{
    public function findByProduct(int $productId): array
    {
        return [];
    }

    public function save(int $productId, array $branches): void
    {
    }
}
