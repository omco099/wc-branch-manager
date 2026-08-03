<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

use Alnaseeg\BranchManager\Branch\Branch;

/**
 * Renders branch fields for product variations.
 */
final class VariationFields
{
    /**
     * Render variation branch fields.
     *
     * @param Branch[] $branches
     * @param array<int,array<string,mixed>> $productData
     */
    public function render(
        int $loop,
        int $variationId,
        array $branches,
        array $productData
    ): void {

        foreach ($branches as $branch) {

            $branchId = $branch->id();

            $values = $productData[$branchId] ?? [];

            ?>

            <div
                class="wcbm-variation-branch"
                style="margin:15px 0;padding:15px;border:1px solid #dcdcde;border-radius:6px;background:#fff;"
            >

                <h4 style="margin:0 0 15px;">
                    <?php echo esc_html($branch->name()); ?>
                </h4>

                <p class="form-row form-row-full">

                    <label>

                        <input
                            type="checkbox"
                            value="1"
                            name="wcbm_variation[<?php echo esc_attr((string) $variationId); ?>][<?php echo esc_attr((string) $branchId); ?>][is_enabled]"
                            <?php checked(! empty($values['is_enabled'])); ?>
                        >

                        <?php
                        esc_html_e(
                            'Available In This Branch',
                            'alnaseeg-branch-manager'
                        );
                        ?>

                    </label>

                </p>

                <p class="form-row form-row-first">

                    <label>

                        <input
                            type="checkbox"
                            value="1"
                            name="wcbm_variation[<?php echo esc_attr((string) $variationId); ?>][<?php echo esc_attr((string) $branchId); ?>][manage_stock]"
                            <?php checked(! empty($values['manage_stock'])); ?>
                        >

                        <?php
                        esc_html_e(
                            'Manage Stock',
                            'alnaseeg-branch-manager'
                        );
                        ?>

                    </label>

                </p>

                <p class="form-row form-row-last">

                    <label>

                        <?php
                        esc_html_e(
                            'Stock Quantity',
                            'alnaseeg-branch-manager'
                        );
                        ?>

                    </label>

                    <input
                        type="number"
                        class="short"
                        min="0"
                        step="1"
                        name="wcbm_variation[<?php echo esc_attr((string) $variationId); ?>][<?php echo esc_attr((string) $branchId); ?>][stock_quantity]"
                        value="<?php echo esc_attr((string) ($values['stock_quantity'] ?? '')); ?>"
                    >

                </p>

                <div style="clear:both;"></div>

            </div>

            <?php
        }
    }
}