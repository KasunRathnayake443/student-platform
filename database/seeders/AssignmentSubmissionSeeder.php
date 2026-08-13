<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Database\Seeder;

class AssignmentSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Get assignments with their students
        |--------------------------------------------------------------------------
        */

        $assignments = Assignment::query()
            ->with([
                'learningClass.students.user',
                'teacher',
            ])
            ->get();

        if ($assignments->isEmpty()) {
            $this->command->warn(
                'No assignments found. Create at least one assignment first.'
            );

            return;
        }

        $created = 0;

        foreach ($assignments as $assignment) {

            $students = $assignment
                ->learningClass
                ?->students;

            if (! $students || $students->isEmpty()) {
                $this->command->warn(
                    "No students found for assignment: {$assignment->title}"
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Create submissions for students in this class
            |--------------------------------------------------------------------------
            */

            foreach ($students->take(5) as $index => $student) {

                /*
                |--------------------------------------------------------------------------
                | Do not create duplicates
                |--------------------------------------------------------------------------
                */

                $submission = AssignmentSubmission::firstOrNew([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Already exists
                |--------------------------------------------------------------------------
                */

                if ($submission->exists) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Different submission scenarios
                |--------------------------------------------------------------------------
                */

                $scenario = $index % 4;

                switch ($scenario) {

                    /*
                    |--------------------------------------------------------------------------
                    | Scenario 1 - Submitted, not graded
                    |--------------------------------------------------------------------------
                    */

                    case 0:

                        $submission->content =
                            '<p>This is a sample student answer. '
                            . 'The student has submitted the assignment '
                            . 'but the teacher has not graded it yet.</p>';

                        $submission->submitted_at =
                            now()->subDays(2);

                        $submission->is_late = false;

                        $submission->score = null;

                        $submission->feedback = null;

                        $submission->graded_by = null;

                        $submission->graded_at = null;

                        $submission->status = 'submitted';

                        break;


                    /*
                    |--------------------------------------------------------------------------
                    | Scenario 2 - Graded
                    |--------------------------------------------------------------------------
                    */

                    case 1:

                        $score = min(
                            (float) $assignment->max_score,
                            round(
                                (float) $assignment->max_score * 0.82,
                                2
                            )
                        );

                        $submission->content =
                            '<p>This is a sample student answer '
                            . 'that has already been graded by the '
                            . 'assigned teacher.</p>';

                        $submission->submitted_at =
                            now()->subDays(4);

                        $submission->is_late = false;

                        $submission->score = $score;

                        $submission->feedback =
                            '<p>Good work. Your answer demonstrates '
                            . 'a good understanding of the topic. '
                            . 'A little more detail would improve it.</p>';

                        $submission->graded_by =
                            $assignment->teacher_id;

                        $submission->graded_at =
                            now()->subDays(3);

                        $submission->status = 'graded';

                        break;


                    /*
                    |--------------------------------------------------------------------------
                    | Scenario 3 - Late submission
                    |--------------------------------------------------------------------------
                    */

                    case 2:

                        $submission->content =
                            '<p>This is a sample late submission. '
                            . 'The student submitted the assignment '
                            . 'after the normal deadline.</p>';

                        $submission->submitted_at =
                            now()->subDays(1);

                        $submission->is_late = true;

                        $submission->score = null;

                        $submission->feedback = null;

                        $submission->graded_by = null;

                        $submission->graded_at = null;

                        $submission->status = 'submitted';

                        break;


                    /*
                    |--------------------------------------------------------------------------
                    | Scenario 4 - Graded with higher score
                    |--------------------------------------------------------------------------
                    */

                    default:

                        $score = min(
                            (float) $assignment->max_score,
                            round(
                                (float) $assignment->max_score * 0.94,
                                2
                            )
                        );

                        $submission->content =
                            '<p>This is another sample student '
                            . 'answer that has been graded.</p>';

                        $submission->submitted_at =
                            now()->subDays(5);

                        $submission->is_late = false;

                        $submission->score = $score;

                        $submission->feedback =
                            '<p>Excellent work. Very clear answer '
                            . 'with good supporting information.</p>';

                        $submission->graded_by =
                            $assignment->teacher_id;

                        $submission->graded_at =
                            now()->subDays(4);

                        $submission->status = 'graded';

                        break;
                }

                $submission->save();

                $created++;
            }
        }

        $this->command->info(
            "{$created} assignment submissions created."
        );
    }
}