<?php

namespace Nuwave\Lighthouse\Support\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AggregationPaginator extends LengthAwarePaginator
{
    /**
     * Get the aggregations for the items in the data store.
     *
     * @return array
     */
    public function aggregations();
}
