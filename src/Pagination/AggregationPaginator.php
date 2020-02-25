<?php

namespace Nuwave\Lighthouse\Pagination;

use Nuwave\Lighthouse\Support\Contracts\AggregationPaginator as AggregationPaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;

class AggregationPaginator extends LengthAwarePaginator implements AggregationPaginatorContract
{
    /**
     * The total number of items before slicing.
     *
     * @var array
     */
    protected $aggregations;

    /**
     * Create a new paginator instance.
     *
     * @param  mixed  $items
     * @param  int  $total
     * @param  int  $perPage
     * @param  int|null  $currentPage
     * @param  array  $options (path, query, fragment, pageName)
     * @return void
     */
    public function __construct($items, $total, $perPage, $currentPage = null, array $options = [], array $aggregations = [])
    {
        parent::__construct($items, $total, $perPage, $currentPage, $options);
        $this->aggregations = $aggregations;
    }

    /**
     * Get the total number of items being paginated.
     *
     * @return int
     */
    public function aggregations()
    {
        return $this->aggregations;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'last_page_url' => $this->url($this->lastPage()),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path(),
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
            'total' => $this->total(),
            'aggregations' => $this->aggregations(),
        ];
    }
}
