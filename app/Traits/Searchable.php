<?php

namespace App\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

trait Searchable
{
    /**
     * Scope a query to search across specified fields and relationships.
     */
    public function scopeSearch(Builder $builder)
    {
        $search = Request::get('search');

        if (!$search) {
            return $builder;
        }

        return $builder->where(function ($query) use ($search) {
            // Get searchable fields for this model
            $searchableFields = $this->getSearchableFields();

            // Search in direct fields
            foreach ($searchableFields['fields'] ?? [] as $field) {
                $query->orWhere($field, 'like', "%{$search}%");
            }

            // Search in relationships
            foreach ($searchableFields['relationships'] ?? [] as $relationship => $fields) {
                $query->orWhereHas($relationship, function ($relationQuery) use ($search, $fields) {
                    $relationQuery->where(function ($subQuery) use ($search, $fields) {
                        foreach ($fields as $field) {
                            $subQuery->orWhere($field, 'like', "%{$search}%");
                        }
                    });
                });
            }

            // Search in nested relationships
            foreach ($searchableFields['nested_relationships'] ?? [] as $relationship => $nestedData) {
                $query->orWhereHas($relationship, function ($relationQuery) use ($search, $nestedData) {
                    foreach ($nestedData as $nestedRelation => $fields) {
                        $relationQuery->orWhereHas($nestedRelation, function ($nestedQuery) use ($search, $fields) {
                            $nestedQuery->where(function ($subQuery) use ($search, $fields) {
                                foreach ($fields as $field) {
                                    $subQuery->orWhere($field, 'like', "%{$search}%");
                                }
                            });
                        });
                    }
                });
            }
        });
    }

    /**
     * Get searchable fields for the model.
     * Override this method in each model to define searchable fields.
     */
    protected function getSearchableFields(): array
    {
        return [
            'fields' => [],
            'relationships' => [],
            'nested_relationships' => []
        ];
    }
}
