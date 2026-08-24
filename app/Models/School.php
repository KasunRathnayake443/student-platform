<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;

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

    public function getLogoUrlAttribute(): ?string
    {
        if (blank($this->logo)) {
            return null;
        }

        if ((string) config('filament.default_filesystem_disk', 'local') === 'public') {
            return Storage::disk('public')->url($this->logo);
        }

        return route('schools.logo', ['school' => $this]);
    }

    /**
     * @return HasMany<Grade, $this>
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | School Admins
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsToMany<User, $this>
     */
    public function schoolAdmins(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'school_user',
            'school_id',
            'user_id'
        )
            ->whereHas('schoolAdmin')
            ->withTimestamps();
    }

    /**
     * @return HasMany<StudentEnrollment, $this>
     */
    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * @return HasManyThrough<Student, StudentEnrollment, $this>
     */
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

    /**
     * @return HasManyThrough<LearningClass, Grade, $this>
     */
    public function classes(): HasManyThrough
    {
        return $this->hasManyThrough(
            LearningClass::class,
            Grade::class
        );
    }

    /**
     * @return BelongsToMany<Teacher, $this>
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            Teacher::class,
            'school_teacher',
            'school_id',
            'teacher_id'
        )
            ->withTimestamps();
    }
}
