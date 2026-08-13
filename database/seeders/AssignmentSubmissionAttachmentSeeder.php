<?php

namespace Database\Seeders;

use App\Models\AssignmentSubmission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmissionAttachmentSeeder extends Seeder
{
    public function run(): void
    {
        $submissions = AssignmentSubmission::query()
            ->with('attachments')
            ->get();

        if ($submissions->isEmpty()) {
            $this->command->warn(
                'No assignment submissions found. Run AssignmentSubmissionSeeder first.'
            );

            return;
        }

        $created = 0;

        foreach ($submissions as $submission) {

            /*
            |--------------------------------------------------------------------------
            | Only add attachments to submitted submissions
            |--------------------------------------------------------------------------
            */

            if (
                ! in_array(
                    $submission->status,
                    ['submitted', 'graded', 'returned'],
                    true
                )
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Don't duplicate attachments
            |--------------------------------------------------------------------------
            */

            if ($submission->attachments->isNotEmpty()) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Create sample submission directory
            |--------------------------------------------------------------------------
            */

            $directory =
                'assignment-submissions/'
                . $submission->id;

            /*
            |--------------------------------------------------------------------------
            | Sample PDF
            |--------------------------------------------------------------------------
            |
            | We create a small text-based file with a .pdf filename for
            | testing the Filament UI. It is only test data.
            |
            */

            $pdfPath =
                $directory
                . '/student-answer.pdf';

            Storage::disk('public')->put(
                $pdfPath,
                "%PDF-1.4\n"
                . "% Sample student submission\n"
                . "Assignment Submission ID: "
                . $submission->id
                . "\n"
            );

            $submission->attachments()->create([
                'original_name' => 'student-answer.pdf',
                'file_path' => $pdfPath,
                'mime_type' => 'application/pdf',
                'file_size' => Storage::disk('public')
                    ->size($pdfPath),
                'sort_order' => 0,
            ]);

            $created++;

            /*
            |--------------------------------------------------------------------------
            | Sample text document
            |--------------------------------------------------------------------------
            */

            $txtPath =
                $directory
                . '/additional-notes.txt';

            Storage::disk('public')->put(
                $txtPath,
                "Sample additional notes for assignment submission #"
                . $submission->id
                . "\n\n"
                . "This is test submission data created by "
                . "AssignmentSubmissionAttachmentSeeder."
            );

            $submission->attachments()->create([
                'original_name' => 'additional-notes.txt',
                'file_path' => $txtPath,
                'mime_type' => 'text/plain',
                'file_size' => Storage::disk('public')
                    ->size($txtPath),
                'sort_order' => 1,
            ]);

            $created++;

            /*
            |--------------------------------------------------------------------------
            | Sample image
            |--------------------------------------------------------------------------
            |
            | Create a very small SVG image so we don't need to ship an
            | actual image file with the project.
            |
            */

            $imagePath =
                $directory
                . '/student-work.svg';

            $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="300">
    <rect width="600" height="300" fill="#f3f4f6"/>
    <rect x="40" y="40" width="520" height="220"
          rx="16" fill="#ffffff" stroke="#d1d5db"/>
    <text x="300" y="135"
          text-anchor="middle"
          font-family="Arial"
          font-size="28"
          fill="#111827">
        Sample Student Work
    </text>
    <text x="300" y="180"
          text-anchor="middle"
          font-family="Arial"
          font-size="18"
          fill="#6b7280">
        Assignment Submission
    </text>
</svg>
SVG;

            Storage::disk('public')->put(
                $imagePath,
                $svg
            );

            $submission->attachments()->create([
                'original_name' => 'student-work.svg',
                'file_path' => $imagePath,
                'mime_type' => 'image/svg+xml',
                'file_size' => Storage::disk('public')
                    ->size($imagePath),
                'sort_order' => 2,
            ]);

            $created++;
        }

        $this->command->info(
            "{$created} submission attachments created."
        );
    }
}