<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Branch;

/**
 * Resolves the current branch.
 */
final class BranchResolver
{
    /**
     * Create a new resolver instance.
     */
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchSession $session,
        private readonly BranchContext $context,
        private readonly PageBranchResolver $pageResolver
    ) {
    }

    /**
     * Resolve the current branch.
     */
    public function resolve(): ?Branch
    {
        /*
         * Return the already resolved branch
         * during the current request.
         */
        if ($this->context->has()) {
            return $this->context->current();
        }

        /*
         * Version 1:
         * Resolve the branch from the current
         * Elementor branch page first.
         */
        $branch = $this->pageResolver->resolve();

        if ($branch !== null) {

            /*
             * Keep the branch available during
             * the current request.
             */
            $this->context->set($branch);

            /*
             * Persist the branch in WooCommerce
             * session so subsequent requests such as
             * Add to Cart, Cart and Checkout retain
             * the same branch context.
             */
            $this->session->set(
                $branch->id()
            );

            return $branch;
        }

        /*
         * The current request is not a branch page.
         *
         * Try restoring the previously resolved
         * branch from WooCommerce session.
         */
        $branchId = $this->session->get();

        if ($branchId === null) {
            return null;
        }

        $branch = $this->branches->findById(
            $branchId
        );

        /*
         * Invalid or deleted branch.
         */
        if ($branch === null) {

            $this->session->clear();
            $this->context->clear();

            return null;
        }

        /*
         * Do not allow an inactive branch
         * to become the current branch.
         */
        if (!$branch->isActive()) {

            $this->session->clear();
            $this->context->clear();

            return null;
        }

        /*
         * Restore the branch into the request context.
         */
        $this->context->set($branch);

        return $branch;
    }

    /**
     * Get the current branch if available.
     */
    public function current(): ?Branch
    {
        return $this->resolve();
    }

    /**
     * Determine whether a branch has been resolved.
     */
    public function has(): bool
    {
        return $this->resolve() !== null;
    }

    /**
     * Clear the resolved branch.
     */
    public function clear(): void
    {
        $this->context->clear();
        $this->session->clear();
    }
}