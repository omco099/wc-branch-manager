<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Product;

use Alnaseeg\BranchManager\Branch\Branch;

/**
 * Renders branch product fields.
 */
final class ProductFields
{
    /**
     * Render branch fields.
     *
     * @param Branch[] $branches
     * @param array<int,array<string,mixed>> $productData
     */
    public function render(
        array $branches,
        array $productData
    ): void {

        foreach ($branches as $branch) {

            $branchId = $branch->id();

            $values = $productData[$branchId] ?? [];

            ?>

            <details class="wcbm-branch-card" open>

                <summary style="padding:12px 16px;font-weight:600;cursor:pointer;">
                    <?php echo esc_html($branch->name()); ?>
                </summary>

                <div class="options_group">

                    <p class="form-field">

                        <label>
                            <?php esc_html_e(
                                'Regular Price',
                                'alnaseeg-branch-manager'
                            ); ?>
                        </label>

                        <input
                            type="number"
                            class="short"
                            step="0.01"
                            min="0"
                            name="wcbm_branch[<?php echo esc_attr((string) $branchId); ?>][regular_price]"
                            value="<?php echo esc_attr((string) ($values['regular_price'] ?? '')); ?>"
                        >

                    </p>

                    <p class="form-field">

                        <label>
                            <?php esc_html_e(
                                'Sale Price',
                                'alnaseeg-branch-manager'
                            ); ?>
                        </label>

                        <input
                            type="number"
                            class="short"
                            step="0.01"
                            min="0"
                            name="wcbm_branch[<?php echo esc_attr((string) $branchId); ?>][sale_price]"
                            value="<?php echo esc_attr((string) ($values['sale_price'] ?? '')); ?>"
                        >

                    </p>

                    <p class="form-field">

                        <label>
                            <?php esc_html_e(
                                'Manage Stock',
                                'alnaseeg-branch-manager'
                            ); ?>
                        </label>

                        <input
                            type="checkbox"
                            value="1"
                            name="wcbm_branch[<?php echo esc_attr((string) $branchId); ?>][manage_stock]"
                            <?php checked(!empty($values['manage_stock'])); ?>
                        >

                    </p>

                    <p class="form-field">

                        <label>
                            <?php esc_html_e(
                                'Stock Quantity',
                                'alnaseeg-branch-manager'
                            ); ?>
                        </label>

                        <input
                            type="number"
                            class="short"
                            min="0"
                            name="wcbm_branch[<?php echo esc_attr((string) $branchId); ?>][stock_quantity]"
                            value="<?php echo esc_attr((string) ($values['stock_quantity'] ?? '')); ?>"
                        >

                    </p>

                    <p class="form-field">

                        <label>
                            <?php esc_html_e(
                                'Available In This Branch',
                                'alnaseeg-branch-manager'
                            ); ?>
                        </label>

                        <input
                            type="checkbox"
                            value="1"
                            name="wcbm_branch[<?php echo esc_attr((string) $branchId); ?>][is_enabled]"
                            <?php checked(!empty($values['is_enabled'])); ?>
                        >

                    </p>

                </div>

            </details>

            <?php
        }
    }
}