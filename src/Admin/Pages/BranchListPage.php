<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Admin\Pages;

/**
 * Renders the Branches admin page.
 */
final class BranchListPage
{
    /**
     * Render branches page.
     */
    public function render(): void
    {
        ?>
        <div class="wrap">

            <h1 class="wp-heading-inline">
                <?php esc_html_e('Branches', 'alnaseeg-branch-manager'); ?>
            </h1>

            <a href="<?php echo esc_url(admin_url('admin.php?page=wcbm-branches&action=create')); ?>"
               class="page-title-action">
                <?php esc_html_e('Add New Branch', 'alnaseeg-branch-manager'); ?>
            </a>

            <hr class="wp-header-end">

            <div class="notice notice-info inline">
                <p>
                    <?php esc_html_e(
                        'No branches have been created yet.',
                        'alnaseeg-branch-manager'
                    ); ?>
                </p>
            </div>

            <table class="widefat striped">

                <thead>

                <tr>

                    <th width="80">
                        <?php esc_html_e('ID', 'alnaseeg-branch-manager'); ?>
                    </th>

                    <th>
                        <?php esc_html_e('Name', 'alnaseeg-branch-manager'); ?>
                    </th>

                    <th>
                        <?php esc_html_e('Slug', 'alnaseeg-branch-manager'); ?>
                    </th>

                    <th width="120">
                        <?php esc_html_e('Status', 'alnaseeg-branch-manager'); ?>
                    </th>

                    <th width="160">
                        <?php esc_html_e('Actions', 'alnaseeg-branch-manager'); ?>
                    </th>

                </tr>

                </thead>

                <tbody>

                <tr>

                    <td colspan="5">

                        <?php esc_html_e(
                            'No branches found.',
                            'alnaseeg-branch-manager'
                        ); ?>

                    </td>

                </tr>

                </tbody>

            </table>

        </div>
        <?php
    }
}