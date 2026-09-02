<?php

namespace App\Filament\SchoolAdmin\Scopes;

use App\Models\Grade;
use Illuminate\Support\Facades\Auth;

/**
 * Shared helpers for scoping school-admin queries and form
 * options to the schools the current admin is assigned to.
 */
class SchoolAdminScopes
{
    /**
     * IDs of the schools the current admin is assigned to.
     *
     * @return array<int, int>
     */
    public static function schoolIds(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        return $user
            ->schools()
            ->pluck('schools.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * IDs of the grades that belong to the admin's schools.
     *
     * @return array<int, int>
     */
    public static function gradeIds(): array
    {
        $ids = static::schoolIds();

        if ($ids === []) {
            return [];
        }

        return Grade::query()
            ->whereIn('school_id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
