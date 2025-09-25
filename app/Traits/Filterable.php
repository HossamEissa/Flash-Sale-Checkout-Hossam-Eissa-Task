<?php

namespace App\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

trait Filterable
{
    /**
     * Scope a query to filter by specified criteria.
     */
    public function scopeFilter(Builder $builder, array $filters = null)
    {
        // If no filters provided, get them from request
        if ($filters === null) {
            $filters = Request::query();
        }

        // Get filterable fields for this model
        $filterableFields = $this->getFilterableFields();

        // Apply direct field filters
        foreach ($filterableFields['fields'] ?? [] as $field => $config) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $this->applyFieldFilter($builder, $field, $filters[$field], $config);
            }
        }

        // Apply relationship filters
        foreach ($filterableFields['relationships'] ?? [] as $relationship => $config) {
            if (isset($filters[$relationship]) && $filters[$relationship] !== '') {
                $this->applyRelationshipFilter($builder, $relationship, $filters[$relationship], $config);
            }
        }

        // Apply date range filters
        foreach ($filterableFields['date_ranges'] ?? [] as $field => $config) {
            $this->applyDateRangeFilter($builder, $field, $filters, $config);
        }

        // Apply custom filters
        foreach ($filterableFields['custom'] ?? [] as $filterName => $callback) {

            if (isset($filters[$filterName]) && $filters[$filterName] !== '') {
                $callback($builder, $filters[$filterName], $filters);
            }
        }

        return $builder;
    }

    /**
     * Apply a filter to a direct field.
     */
    protected function applyFieldFilter(Builder $builder, string $field, $value, array $config)
    {
        $operator = $config['operator'] ?? '=';
        $column = $config['column'] ?? $field;

        switch ($operator) {
            case 'like':
                $builder->where($column, 'like', "%{$value}%");
                break;
            case 'in':
                if (is_array($value)) {
                    $builder->whereIn($column, $value);
                } else {
                    $builder->whereIn($column, explode(',', $value));
                }
                break;
            case 'between':
                if (is_array($value) && count($value) === 2) {
                    $builder->whereBetween($column, $value);
                }
                break;
            case 'not_null':
                if ($value) {
                    $builder->whereNotNull($column);
                } else {
                    $builder->whereNull($column);
                }
                break;
            case '>=':
            case '<=':
            case '>':
            case '<':
            case '!=':
            case '=':
            default:
                $builder->where($column, $operator, $value);
                break;
        }
    }

    /**
     * Apply a filter to a relationship.
     */
    protected function applyRelationshipFilter(Builder $builder, string $relationship, $value, array $config)
    {
        $field = $config['field'] ?? 'id';
        $operator = $config['operator'] ?? '=';

        $builder->whereHas($relationship, function ($query) use ($field, $operator, $value) {
            switch ($operator) {
                case 'like':
                    $query->where($field, 'like', "%{$value}%");
                    break;
                case 'in':
                    if (is_array($value)) {
                        $query->whereIn($field, $value);
                    } else {
                        $query->whereIn($field, explode(',', $value));
                    }
                    break;
                default:
                    $query->where($field, $operator, $value);
                    break;
            }
        });
    }

    /**
     * Apply date range filters.
     */
    protected function applyDateRangeFilter(Builder $builder, string $field, array $filters, array $config)
    {
        $column = $config['column'] ?? $field;
        $startKey = $config['start_key'] ?? "{$field}_start";
        $endKey = $config['end_key'] ?? "{$field}_end";

        if (isset($filters[$startKey]) && $filters[$startKey] !== '') {
            $builder->whereDate($column, '>=', $filters[$startKey]);
        } else if (isset($filters[$endKey]) && $filters[$endKey] !== '') {
            $builder->whereDate($column, '<=', $filters[$endKey]);
        }

    }

    /**
     * Get filterable fields for the model.
     * Override this method in each model to define filterable fields.
     */
    protected function getFilterableFields(): array
    {
        return [
            'fields' => [],
            'relationships' => [],
            'date_ranges' => [],
            'custom' => []
        ];
    }
}
