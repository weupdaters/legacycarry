<?php

namespace App\Traits;

trait SafeFilter
{
    // Safe direct column filter
    public function safeWhere($query, $table, $column, $value)
    {
        if ($value === null || $value === '') return $query;

        if (\Schema::hasColumn($table, $column)) {
            return $query->where($column, $value);
        }
        return $query;
    }

    // Safe LIKE filter
    public function safeWhereLike($query, $table, $column, $value)
    {
        if ($value === null || $value === '') return $query;

        if (\Schema::hasColumn($table, $column)) {
            return $query->where($column, 'like', "%$value%");
        }
        return $query;
    }

    // Safe relation filter (like details.society.name)
    public function safeRelationWhereLike($query, $relation, $column, $value)
    {
        if (!$value) return $query;

        return $query->whereHas($relation, function ($q) use ($column, $value) {
            $q->where($column, 'like', "%$value%");
        });
    }
}
