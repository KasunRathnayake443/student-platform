<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Lesson extends Model
{
    protected $fillable = [
        'learning_class_id',
        'teacher_id',
        'title',
        'description',
        'content',
        'video_url',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function learningClass(): BelongsTo
    {
        return $this->belongsTo(
            LearningClass::class,
            'learning_class_id'
        );
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(
            LessonAttachment::class
        )->orderBy('sort_order');
    }

    protected static function booted(): void
    {
        static::deleting(function (Lesson $lesson) {

            $lesson->loadMissing('attachments');

            foreach ($lesson->attachments as $attachment) {

                if (
                    $attachment->file_path &&
                    Storage::disk('public')->exists(
                        $attachment->file_path
                    )
                ) {
                    Storage::disk('public')->delete(
                        $attachment->file_path
                    );
                }

                $attachment->delete();
            }
        });
    }
}