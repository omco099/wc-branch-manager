<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Core;

use wpdb;
use Alnaseeg\BranchManager\Branch\BranchContext;
use Alnaseeg\BranchManager\Branch\BranchRepository;
use Alnaseeg\BranchManager\Branch\BranchResolver;
use Alnaseeg\BranchManager\Branch\BranchSession;
use Alnaseeg\BranchManager\Product\ProductPriceResolver;
use Alnaseeg\BranchManager\Product\ProductRepository;

/**
 * Creates and stores application services.
 */
final class Services
{
    /**
     * Cached service instances.
     *
     * @var array<string,object>
     */
    private array $services = [];

    public function __construct(
        private readonly wpdb $wpdb
    ) {
    }

    /**
     * Branch repository.
     */
    public function branchRepository(): BranchRepository
    {
        return $this->services[__METHOD__]
            ??= new BranchRepository($this->wpdb);
    }

    /**
     * Branch session.
     */
    public function branchSession(): BranchSession
    {
        return $this->services[__METHOD__]
            ??= new BranchSession();
    }

    /**
     * Branch context.
     */
    public function branchContext(): BranchContext
    {
        return $this->services[__METHOD__]
            ??= new BranchContext();
    }

    /**
     * Branch resolver.
     */
    public function branchResolver(): BranchResolver
    {
        return $this->services[__METHOD__]
            ??= new BranchResolver(
                $this->branchRepository(),
                $this->branchSession(),
                $this->branchContext()
            );
    }

    /**
     * Product repository.
     */
    public function productRepository(): ProductRepository
    {
        return $this->services[__METHOD__]
            ??= new ProductRepository($this->wpdb);
    }

    /**
     * Product price resolver.
     */
    public function productPriceResolver(): ProductPriceResolver
    {
        return $this->services[__METHOD__]
            ??= new ProductPriceResolver(
                $this->branchResolver(),
                $this->productRepository()
            );
    }
}