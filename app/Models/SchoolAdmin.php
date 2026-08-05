<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolAdmin extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(
            School::class,
            'school_user', // pivot table
            'user_id',     // foreign key on pivot referencing SchoolAdmin
            'school_id',   // foreign key on pivot referencing School
            'user_id',     // local key on SchoolAdmin table (not 'id')
            'id'           // local key on School table
        )->withTimestamps();
    }
}