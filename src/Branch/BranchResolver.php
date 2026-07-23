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
        if ($this->context->has()) {
            return $this->context->current();
        }

        /*
         * Version 1:
         * Try resolving the branch from the current page first.
         */
        $branch = $this->pageResolver->resolve();

        if ($branch !== null) {
            $this->context->set($branch);

            return $branch;
        }

        /*
         * Fallback to session (legacy support).
         */
        $branchId = $this->session->get();

        if ($branchId === null) {
            return null;
        }

        $branch = $this->branches->findById($branchId);

        if ($branch === null) {
            $this->session->clear();

            return null;
        }

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