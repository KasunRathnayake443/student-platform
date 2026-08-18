<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable, PasskeyAuthenticatable;


    protected $fillable = [
        'name',
        'email',
        'password',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(
            School::class
        )
        ->withTimestamps();
    }




public function learningClasses(): BelongsToMany
{
    return $this->belongsToMany(
        LearningClass::class,
        'class_student'
    )
    ->withPivot('student_enrollment_id')
    ->withTimestamps();
}

public function studentEnrollments(): HasMany
{
    return $this->hasMany(StudentEnrollment::class, 'student_id');
}

public function student(): HasOne
{
    return $this->hasOne(Student::class);
}
public function schoolAdmin(): HasOne
{
    return $this->hasOne(
        SchoolAdmin::class
    );
}

public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Get the user's initials for avatar display.
     */
    public function initials(): string
    {
        return collect(explode(' ', $this->name))
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->take(2)
            ->implode('');
    }

}