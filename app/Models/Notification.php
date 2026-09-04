<?php

namespace App\Models;

use App\Enums\NotificationMentionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    protected $fillable = [
        'sender_id',
        'title',
        'body',
        'icon',
        'color',
        'mention_type',
        'mention_id',
    ];

    protected $casts = [
        'mention_type' => NotificationMentionType::class,
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    public function recipientUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'notification_recipients',
            'notification_id',
            'recipient_id'
        )
            ->withPivot(['is_read', 'read_at'])
            ->withTimestamps();
    }

    /**
     * The mentioned item (lesson / assignment / quiz), resolved polymorphically.
     */
    public function mentionedLesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'mention_id');
    }

    public function mentionedAssignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'mention_id');
    }

    public function mentionedQuiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'mention_id');
    }

    public function getMentionedItem(): ?Model
    {
        return match ($this->mention_type) {
            NotificationMentionType::Lesson => $this->mentionedLesson()->first(),
            NotificationMentionType::Assignment => $this->mentionedAssignment()->first(),
            NotificationMentionType::Quiz => $this->mentionedQuiz()->first(),
            default => null,
        };
    }
}
