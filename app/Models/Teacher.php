<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Teacher extends Model
{
    protected $fillable = [

        'user_id',
        'profile_photo',
        'employee_no',
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
            'school_teacher'
        )->withTimestamps();
    }


    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            Teacher::class,
            'learning_class_teacher'
        )->withTimestamps();
    }
}