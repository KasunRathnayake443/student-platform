<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class LearningClass extends Model
{

    protected $fillable = [
        'grade_id',
        'name',
        'medium',
        'is_active',
    ];



    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }




    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            Student::class,
            'class_student',
            'learning_class_id',
            'student_id'
        )
        ->withTimestamps();
    }





    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            Teacher::class,
            'learning_class_teacher',
            'learning_class_id',
            'teacher_id'
        )
        ->withTimestamps();
    }

}