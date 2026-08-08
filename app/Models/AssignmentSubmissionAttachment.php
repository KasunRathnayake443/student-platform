<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmissionAttachment extends Model
{
    protected $fillable = [
        'assignment_submission_id',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'sort_order',
    ];


    protected $casts = [
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];


    /*
    |--------------------------------------------------------------------------
    | Submission
    |--------------------------------------------------------------------------
    */

    public function submission(): BelongsTo
    {
        return $this->belongsTo(
            AssignmentSubmission::class,
            'assignment_submission_id'
        );
    }
}