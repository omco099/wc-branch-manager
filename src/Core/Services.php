<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Core;

use wpdb;
use Alnaseeg\BranchManager\Branch\BranchContext;
use Alnaseeg\BranchManager\Branch\BranchRepository;
use Alnaseeg\BranchManager\Branch\BranchResolver;
use Alnaseeg\BranchManager\Branch\BranchSelector;
use Alnaseeg\BranchManager\Branch\BranchSession;
use Alnaseeg\BranchManager\Branch\BranchSelectorRenderer;
use Alnaseeg\BranchManager\Branch\PageBranchResolver;
use Alnaseeg\BranchManager\Product\ProductPriceResolver;
use Alnaseeg\BranchManager\Product\ProductRepository;
use Alnaseeg\BranchManager\Product\ProductStockResolver;
use Alnaseeg\BranchManager\Shortcodes\BranchProductsShortcode;

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
     * Resolve branch from current page.
     */
    public function pageBranchResolver(): PageBranchResolver
    {
        return $this->services[__METHOD__]
            ??= new PageBranchResolver(
                $this->branchRepository()
            );
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
                $this->branchContext(),
                $this->pageBranchResolver()
            );
    }

    /**
     * Branch selector.
     *
     * Kept for legacy support.
     * It is not registered in Version 1.
     */
    public function branchSelector(): BranchSelector
    {
        return $this->services[__METHOD__]
            ??= new BranchSelector(
                $this->branchRepository(),
                $this->branchSession(),
                $this->branchSelectorRenderer()
            );
    }

    /**
     * Branch selector renderer.
     *
     * Kept for legacy support.
     */
    public function branchSelectorRenderer(): BranchSelectorRenderer
    {
        return $this->services[__METHOD__]
            ??= new BranchSelectorRenderer();
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

    /**
     * Product stock resolver.
     */
    public function productStockResolver(): ProductStockResolver
    {
        return $this->services[__METHOD__]
            ??= new ProductStockResolver(
                $this->branchResolver(),
                $this->productRepository()
            );
    }

    /**
     * Branch products shortcode.
     */
    public function branchProductsShortcode(): BranchProductsShortcode
    {
        return $this->services[__METHOD__]
            ??= new BranchProductsShortcode(
                $this->branchResolver(),
                $this->productRepository()
            );
    }
}