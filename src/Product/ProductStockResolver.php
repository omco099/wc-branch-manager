<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

use Alnaseeg\BranchManager\Branch\BranchResolver;

/**
 * Resolves product stock data for the current branch.
 */
final class ProductStockResolver
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
     * Resolve whether stock management is enabled
     * for the product in the current branch.
     */
    public function manageStock(
        int $productId,
        bool $defaultManageStock
    ): bool {

        $branchData = $this->branchData($productId);

        if ($branchData === null) {
            return $defaultManageStock;
        }

        return (bool) $branchData['manage_stock'];
    }

    /**
     * Resolve the stock quantity for the current branch.
     */
    public function stockQuantity(
        int $productId,
        int|float|null $defaultQuantity
    ): int|float|null {

        $branchData = $this->branchData($productId);

        if ($branchData === null) {
            return $defaultQuantity;
        }

        if (!(bool) $branchData['manage_stock']) {
            return $defaultQuantity;
        }

        return (int) $branchData['stock_quantity'];
    }

    /**
     * Resolve the stock status for the current branch.
     */
    public function stockStatus(
        int $productId,
        string $defaultStatus
    ): string {

        $branchData = $this->branchData($productId);

        if ($branchData === null) {
            return $defaultStatus;
        }

        if (!(bool) $branchData['is_enabled']) {
            return 'outofstock';
        }

        if ((bool) $branchData['manage_stock']) {
            return (int) $branchData['stock_quantity'] > 0
                ? 'instock'
                : 'outofstock';
        }

        $status = (string) $branchData['stock_status'];

        return in_array(
            $status,
            [
                'instock',
                'outofstock',
                'onbackorder',
            ],
            true
        )
            ? $status
            : $defaultStatus;
    }

    /**
     * Determine whether the product is purchasable
     * in the current branch.
     */
    public function isPurchasable(
        int $productId,
        bool $defaultPurchasable
    ): bool {

        $branchData = $this->branchData($productId);

        if ($branchData === null) {
            return $defaultPurchasable;
        }

        if (!(bool) $branchData['is_enabled']) {
            return false;
        }

        if ((bool) $branchData['manage_stock']) {
            return (int) $branchData['stock_quantity'] > 0;
        }

        return (string) $branchData['stock_status'] !== 'outofstock';
    }

    /**
     * Determine whether the product is enabled
     * for the current branch.
     */
    public function isEnabled(
        int $productId
    ): bool {

        $branchData = $this->branchData($productId);

        if ($branchData === null) {
            return false;
        }

        return (bool) $branchData['is_enabled'];
    }

    /**
     * Get branch data for the current product.
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