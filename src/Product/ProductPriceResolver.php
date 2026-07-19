<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

use Alnaseeg\BranchManager\Branch\BranchResolver;

/**
 * Resolves product prices for the currently selected branch.
 */
final class ProductPriceResolver
{
    /**
     * Cached branch data per product during the current request.
     *
     * @var array<int,array<string,mixed>|null>
     */
    private array $cache = [];

    public function __construct(
        private readonly BranchResolver $branchResolver,
        private readonly ProductRepository $productRepository
    ) {
    }

    /**
     * Resolve the active product price.
     */
    public function price(
        int $productId,
        float|string $defaultPrice
    ): float|string {

        $branchData = $this->branchData($productId);

        if ($branchData === null) {
            return $defaultPrice;
        }

        return $branchData['sale_price'] !== ''
            ? $branchData['sale_price']
            : $branchData['regular_price'];
    }

    /**
     * Resolve the regular product price.
     */
    public function regularPrice(
        int $productId,
        float|string $defaultPrice
    ): float|string {

        $branchData = $this->branchData($productId);

        if ($branchData === null) {
            return $defaultPrice;
        }

        return $branchData['regular_price'];
    }

    /**
     * Resolve the sale product price.
     */
    public function salePrice(
        int $productId,
        float|string $defaultPrice
    ): float|string {

        $branchData = $this->branchData($productId);

        if ($branchData === null) {
            return $defaultPrice;
        }

        return $branchData['sale_price'];
    }

    /**
     * Get current branch data for a product.
     *
     * @return array<string,mixed>|null
     */
    private function branchData(
        int $productId
    ): ?array {

        if (array_key_exists($productId, $this->cache)) {
            return $this->cache[$productId];
        }

        $branch = $this->branchResolver->resolve();

        if ($branch === null) {
            return $this->cache[$productId] = null;
        }

        return $this->cache[$productId] = $this->productRepository->findBranch(
            $productId,
            $branch->id()
        );
    }
}