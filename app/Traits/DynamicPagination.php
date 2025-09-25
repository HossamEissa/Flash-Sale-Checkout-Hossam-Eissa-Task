<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait DynamicPagination
{
    /**
     * Dynamically paginate the query based on request parameters
     *
     * @param Builder $builder The query builder instance
     * @param callable|int|null $perPage Number of items per page
     * @param array|string $columns Columns to select
     * @param string $pageName Name of the page parameter
     * @param int|null $page Current page number
     * @param callable|int|null $total Total number of items
     * @return mixed Collection if pagination is 'none', otherwise LengthAwarePaginator
     */
    public function scopeDynamicPaginate(
        Builder $builder,
        callable|int|null $limit = null,
        array|string $columns = ['*'],
        string $pageName = 'page',
        int|null $page = null,
        callable|int|null $total = null
    ) {
        return $builder->when(request('pagination') === 'none', fn($query) => $query->get())
            ->unless(request('pagination') === 'none', fn($query) => $query->paginate(request('limit', $limit), $columns, $pageName, $page, $total));
    }

    /**
     * Dynamically simple paginate the query based on request parameters
     *
     * @param Builder $builder The query builder instance
     * @param int|null $perPage Number of items per page
     * @param array|string $columns Columns to select
     * @param string $pageName Name of the page parameter
     * @param int|null $page Current page number
     * @return mixed Collection if pagination is 'none', otherwise Paginator
     */
    public function scopeDynamicSimplePaginate(
        Builder $builder,
        int|null $perPage = null,
        array|string $columns = ['*'],
        string $pageName = 'page',
        int|null $page = null
    ) {
        return $builder->when(request('pagination') === 'none', fn($query) => $query->get())
            ->unless(request('pagination') === 'none', fn($query) => $query->simplePaginate(request('per_page', $perPage), $columns, $pageName, $page));
    }

    /**
     * Apply a dynamic limit to the query based on request parameters
     *
     * @param Builder $builder The query builder instance
     * @return Builder The modified query builder
     */
    public function scopeDynamicLimit(Builder $builder, int $defaultLimit = 0): Builder
    {
        return $builder->when(request('limit', $defaultLimit), fn($query, $limit) => $query->limit($limit));
    }
}
