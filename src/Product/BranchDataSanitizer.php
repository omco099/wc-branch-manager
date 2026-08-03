<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

/**
 * Sanitizes a single branch payload before persistence.
 */
final class BranchDataSanitizer
{
    /**
     * Sanitize a single branch payload.
     *
     * @param array<string,mixed> $branch
     *
     * @return array<string,mixed>
     */
    public function sanitize(
        array $branch
    ): array {

        $manageStock = ! empty(
            $branch['manage_stock']
        );

        $stockQuantity = isset($branch['stock_quantity'])
            ? max(
                0,
                (int) wp_unslash(
                    (string) $branch['stock_quantity']
                )
            )
            : 0;

        return [

            /*
             * Prices are managed by WooCommerce.
             * They remain in the database schema for
             * future compatibility but are ignored.
             */
            'regular_price' => '',
            'sale_price'    => '',

            'stock_quantity' => $stockQuantity,

            'manage_stock' => $manageStock ? 1 : 0,

            'stock_status' => $this->determineStockStatus(
                $manageStock,
                $stockQuantity
            ),

            'is_enabled' => ! empty(
                $branch['is_enabled']
            ) ? 1 : 0,
        ];
    }

    /**
     * Determine stock status.
     */
    private function determineStockStatus(
        bool $manageStock,
        int $stockQuantity
    ): string {

        if (! $manageStock) {
            return 'instock';
        }

        return $stockQuantity > 0
            ? 'instock'
            : 'outofstock';
    }
}