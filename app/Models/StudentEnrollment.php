<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEnrollment extends Model
{
    protected $fillable = [

        'student_id',
        'school_id',
        'grade_id',
        'academic_year',
        'status',

    ];


    protected $casts = [
        'academic_year' => 'integer',
    ];


    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }


    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }


    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
}