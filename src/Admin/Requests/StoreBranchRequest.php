<?php

declare(strict_types=1);

namespace Alnaseeg\BranchManager\Admin\Requests;

/**
 * Request object for storing branch data.
 */
final class StoreBranchRequest
{
    public function validate(array $data): array
    {
        return $data;
    }
}
