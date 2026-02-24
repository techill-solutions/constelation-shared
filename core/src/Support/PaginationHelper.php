<?php

declare(strict_types=1);

namespace Constelation\Shared\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PaginationHelper
{
    public static function fromPaginator(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];
    }
}
