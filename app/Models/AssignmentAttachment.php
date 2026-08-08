<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentAttachment extends Model
{
    protected $fillable = [
        'assignment_id',
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
    | Assignment
    |--------------------------------------------------------------------------
    */

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(
            Assignment::class
        );
    }
}