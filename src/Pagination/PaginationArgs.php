<?php

namespace Nuwave\Lighthouse\Pagination;

use GraphQL\Error\Error;
use Illuminate\Support\Arr;
use Illuminate\Pagination\Paginator;
use Laravel\Scout\Builder as ScoutBuilder;

class PaginationArgs
{
    /**
     * @var int
     */
    public $page;

    /**
     * @var int
     */
    public $first;

    /**
     * Create a new instance from user given args.
     *
     * @param  mixed[]  $args
     * @param  \Nuwave\Lighthouse\Pagination\PaginationType|null  $paginationType
     * @param  int|null  $paginateMaxCount
     * @return static
     *
     * @throws \GraphQL\Error\Error
     */
    public static function extractArgs(array $args, ?PaginationType $paginationType, ?int $paginateMaxCount): self
    {
        $instance = new static();

        if ($paginationType->isConnection()) {
            $instance->first = $args['first'];
            $instance->page = self::calculateCurrentPage(
                $instance->first,
                Cursor::decode($args)
            );
        } else {
            $instance->first = $args[config('lighthouse.pagination_amount_argument')];
            $instance->page = Arr::get($args, 'page', 1);
        }

        if ($instance->first <= 0) {
            throw new Error(
                "Requested pagination amount must be more than 0, got $instance->first"
            );
        }

        // Make sure the maximum pagination count is not exceeded
        if (
            $paginateMaxCount !== null
            && $instance->first > $paginateMaxCount
        ) {
            throw new Error(
                "Maximum number of {$paginateMaxCount} requested items exceeded. Fetch smaller chunks."
            );
        }

        return $instance;
    }

    /**
     * Calculate the current page to inform the user about the pagination state.
     *
     * @param  int  $first
     * @param  int  $after
     * @param  int  $defaultPage
     * @return int
     */
    protected static function calculateCurrentPage(int $first, int $after, int $defaultPage = 1): int
    {
        return $first && $after
            ? (int) floor(($first + $after) / $first)
            : $defaultPage;
    }

    /**
     * Apply the args to a builder, constructing a paginator.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function applyToBuilder($builder)
    {
        if ($builder instanceof ScoutBuilder) {
            /*
            use AggregationPaginator instead of default LengtAwarePaginator
            see original approach in ScoutBuilder::paginate
            */

            $engine = $builder->model->searchableUsing();

            $page = $this->page;

            $perPage = $this->first;

            $results = $builder->model->newCollection($engine->map(
                $builder, $rawResults = $engine->paginate($builder, $perPage, $page), $builder->model
            )->all());

            $aggregations = [];
            $raw = $builder->raw();
            if (!empty($raw['aggregations'])) {
                // format aggregations to expected format
                /*
                from
                [name => [buckets => [[key, doc_count, top_hits]]]]

                to
                [name => 'name', buckets => [value => key, count => doc_count, top => [top_hits]]]
                */
                $aggregations = array_map(function($key, $value) {
                    return [
                        'name' => $key,
                        'buckets' => array_map(function($bucket) {
                            return [
                                'value' => $bucket['key'],
                                'count' => $bucket['doc_count'],
                                'top' => $bucket['top_hits']['hits']['hits']
                            ];
                        }, $value['buckets']),
                    ];
                }, array_keys($raw['aggregations']), $raw['aggregations']);
            }

            $paginator = (new AggregationPaginator(
                $results,
                $engine->getTotalCount($rawResults),
                $perPage,
                $this->page,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ],
                $aggregations
            ));

            return $paginator->appends('query', $builder->query);
        }

        return $builder->paginate($this->first, ['*'], 'page', $this->page);
    }
}
