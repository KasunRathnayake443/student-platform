<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningClass extends Model
{
    public const DEFAULT_EMOJI = '📘';

    /**
     * Emojis admins can pick to visually identify a class on the student dashboard.
     *
     * @var array<string, string>
     */
    public const EMOJI_OPTIONS = [
        '🔢' => '🔢 Mathematics',
        '📐' => '📐 Geometry',
        '🧮' => '🧮 Counting / Abacus',
        '📖' => '📖 English / Reading',
        '✏️' => '✏️ Writing',
        '📝' => '📝 Notes / Exams',
        '🗣️' => '🗣️ Language / Speaking',
        '📚' => '📚 Library / Literature',
        '🔬' => '🔬 Science',
        '🧪' => '🧪 Chemistry',
        '⚛️' => '⚛️ Physics',
        '🧬' => '🧬 Biology',
        '🌍' => '🌍 Geography / World',
        '🗺️' => '🗺️ Maps',
        '🌱' => '🌱 Environment',
        '🐾' => '🐾 Animals',
        '🏛️' => '🏛️ History',
        '🎨' => '🎨 Art & Drawing',
        '🎭' => '🎭 Drama',
        '🎵' => '🎵 Music',
        '🎤' => '🎤 Singing',
        '🎬' => '🎬 Film / Media',
        '📷' => '📷 Photography',
        '🏃' => '🏃 Physical Education',
        '⚽' => '⚽ Sports',
        '🏏' => '🏏 Cricket',
        '🏊' => '🏊 Swimming',
        '🧘' => '🧘 Health & Wellbeing',
        '🩺' => '🩺 Health',
        '💻' => '💻 Computing',
        '🖥️' => '🖥️ ICT / Computer Lab',
        '🤖' => '🤖 Robotics / AI',
        '🌐' => '🌐 Internet / Web',
        '🚀' => '🚀 STEM / Space',
        '📊' => '📊 Business / Statistics',
        '💰' => '💰 Finance',
        '🍳' => '🍳 Cooking',
        '🧵' => '🧵 Craft / Sewing',
        '🔧' => '🔧 Technical / Workshop',
        '🧩' => '🧩 Puzzles / Thinking',
        '♟️' => '♟️ Chess',
        '🌼' => '🌼 Nature',
        '💡' => '💡 Ideas / Innovation',
        '🌟' => '🌟 Stars / General',
        '🎯' => '🎯 Focus / Goals',
        '🎮' => '🎮 Games / Coding',
    ];

    protected $fillable = [

        'grade_id',

        'name',

        'medium',

        'emoji',

        'is_active',

    ];

    /**
     * @return array<string, string>
     */
    public static function emojiOptions(): array
    {
        return self::EMOJI_OPTIONS;
    }

    /**
     * The emoji to show for this class, falling back to the default when none was picked.
     */
    public function getDisplayEmojiAttribute(): string
    {
        return filled($this->emoji) ? $this->emoji : self::DEFAULT_EMOJI;
    }

    /**
     * @return BelongsTo<Grade, $this>
     */
    /**
     * @return BelongsTo<Grade, $this>
     */
    public function grade(): BelongsTo
    {

        return $this->belongsTo(Grade::class);

    }

    /*
    |--------------------------------------------------------------------------
    | Students Assigned To This Class
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function students(): BelongsToMany
    {

        return $this->belongsToMany(

            Student::class,

            'class_student',

            'learning_class_id',

            'student_id'

        )
            ->withPivot([

                'student_enrollment_id',

            ])
            ->withTimestamps();

    }

    /*
    |--------------------------------------------------------------------------
    | Student Enrollments Assigned To This Class
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsToMany<StudentEnrollment, $this>
     */
    public function enrollments(): BelongsToMany
    {

        return $this->belongsToMany(

            StudentEnrollment::class,

            'class_student',

            'learning_class_id',

            'student_enrollment_id'

        )
            ->withPivot([

                'student_id',

            ])
            ->withTimestamps();

    }

    /*
    |--------------------------------------------------------------------------
    | Teachers
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsToMany<Teacher, $this>
     */
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

    /*
       |--------------------------------------------------------------------------
       | Lessons
       |--------------------------------------------------------------------------
       */

    /**
     * @return HasMany<Lesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(
            Lesson::class,
            'learning_class_id'
        )->orderBy('sort_order');
    }

    /**
     * @return HasMany<Assignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(
            Assignment::class
        );
    }

    /**
     * @return HasMany<Quiz, $this>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(
            Quiz::class,
            'learning_class_id'
        );
    }
}
