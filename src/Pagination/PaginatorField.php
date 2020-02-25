<?php

namespace Nuwave\Lighthouse\Pagination;

use Nuwave\Lighthouse\Support\Contracts\AggregationPaginator;
use Illuminate\Support\Collection;

class PaginatorField
{
    /**
     * Resolve paginator info for connection.
     *
     * @param  \Nuwave\Lighthouse\Support\Contracts\AggregationPaginator  $root
     * @return array
     */
    public function paginatorInfoResolver(AggregationPaginator $root): array
    {
        return [
            'count' => $root->count(),
            'currentPage' => $root->currentPage(),
            'firstItem' => $root->firstItem(),
            'hasMorePages' => $root->hasMorePages(),
            'lastItem' => $root->lastItem(),
            'lastPage' => $root->lastPage(),
            'perPage' => $root->perPage(),
            'total' => $root->total(),
        ];
    }

    /**
     * Resolve data for connection.
     *
     * @param  \Nuwave\Lighthouse\Support\Contracts\AggregationPaginator  $root
     * @return \Illuminate\Support\Collection
     */
    public function dataResolver(AggregationPaginator $root): Collection
    {
        return $root->values();
    }
}
