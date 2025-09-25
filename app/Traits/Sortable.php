<?php

namespace App\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

trait Sortable
{
    /**
     * Scope a query to order by specified field and direction.
     */
    public function scopeSort(Builder $builder)
    {
        $sortBy = Request::get('sort_by', $this->getDefaultSortField());
        $sortOrder = Request::get('sort_order', $this->getDefaultSortOrder());

        // Validate sort order
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? $sortOrder : 'desc';

        // Check if the field is sortable
        if ($this->isSortableField($sortBy)) {
            $builder->orderBy($sortBy, $sortOrder);
        } else {
            // Fall back to default sorting if field is not sortable
            $builder->orderBy($this->getDefaultSortField(), $this->getDefaultSortOrder());
        }
    }

    /**
     * Get the default field to sort by.
     * Override this method in models to change the default.
     */
    protected function getDefaultSortField(): string
    {
        return 'id';
    }

    /**
     * Get the default sort order.
     * Override this method in models to change the default.
     */
    protected function getDefaultSortOrder(): string
    {
        return 'desc';
    }

    /**
     * Check if a field is sortable.
     * Override this method in models to define sortable fields.
     */
    protected function isSortableField(string $field): bool
    {
        // Get sortable fields for this model
        $sortableFields = $this->getSortableFields();

        return in_array($field, $sortableFields);
    }

    /**
     * Get sortable fields for the model.
     * Override this method in each model to define sortable fields.
     */
    protected function getSortableFields(): array
    {
        // Default to all fillable fields plus common fields
        return array_merge(
            $this->fillable ?? [],
            ['id', 'created_at', 'updated_at']
        );
    }
}
