<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class School extends Model
{
    protected $fillable = [
        'name',
        'code',
        'logo',
        'address',
        'phone',
        'email',
        'is_active',
    ];


    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }


    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }


    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            StudentEnrollment::class,
            'school_id',        
            'id',               
            'id',               
            'student_id'        
        );
    }


    public function classes()
    {
        return $this->hasManyThrough(
            LearningClass::class,
            Grade::class
        );
    }
}