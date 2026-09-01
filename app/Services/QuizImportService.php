<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class QuizImportService
{
    /**
     * The expected CSV column headers, in order.
     *
     * @var array<int, string>
     */
    public const HEADERS = [
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'option_f',
        'correct_option',
        'points',
        'explanation',
    ];

    /**
     * Build a CSV template that users can download and feed to any AI.
     */
    public function buildTemplate(): string
    {
        $rows = [
            self::HEADERS,
            [
                'What is the capital of France?',
                'London',
                'Paris',
                'Berlin',
                'Madrid',
                '',
                '',
                'B',
                '1',
                'Paris is the capital and largest city of France.',
            ],
        ];

        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new RuntimeException('Unable to open a temporary stream.');
        }

        foreach ($rows as $row) {
            fputcsv($stream, $row, escape: '\\');
        }

        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        return $contents;
    }

    /**
     * Parse an uploaded CSV file into a list of question data arrays
     * compatible with the Filament questions repeater.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws RuntimeException
     */
    public function parse(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['csv', 'txt', 'tsv'], true)) {
            throw new RuntimeException('Unsupported file type. Please upload a CSV file.');
        }

        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw new RuntimeException('Unable to read the uploaded file.');
        }

        $header = fgetcsv($handle, escape: '\\');

        if ($header === false) {
            fclose($handle);
            throw new RuntimeException('The uploaded file is empty.');
        }

        $header = array_map(
            fn ($value) => $this->cleanValue((string) $value),
            $header
        );

        if (! $this->hasValidHeader($header)) {
            fclose($handle);
            throw new RuntimeException(
                'The file is missing required columns. '
                .'Expected: '.implode(', ', self::HEADERS).'. '
                .'Please download the template and use that structure.'
            );
        }

        $questions = [];

        while (($row = fgetcsv($handle, escape: '\\')) !== false) {
            $data = $this->mapRow($header, $row);

            if (empty($data['question_text'])) {
                continue;
            }

            $questions[] = $data;
        }

        fclose($handle);

        if ($questions === []) {
            throw new RuntimeException('No valid questions were found in the uploaded file.');
        }

        return $questions;
    }

    /**
     * Parse a stored file path (as produced by a Filament FileUpload field).
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws RuntimeException
     */
    public function parseStoredPath(string $path): array
    {
        $disk = Storage::disk((string) config('filament.default_filesystem_disk', 'local'));

        if (! $disk->exists($path)) {
            throw new RuntimeException('The uploaded file could not be found.');
        }

        $contents = $disk->get($path);

        $tempPath = tempnam(sys_get_temp_dir(), 'quiz_import_');

        if ($tempPath === false) {
            throw new RuntimeException('Unable to process the uploaded file.');
        }

        try {
            file_put_contents($tempPath, $contents);

            $file = new UploadedFile($tempPath, basename($path), null, null, true);

            return $this->parse($file);
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * Whether the parsed header contains at least the required columns.
     *
     * @param  array<int, string>  $header
     */
    protected function hasValidHeader(array $header): bool
    {
        $required = ['question', 'option_a', 'option_b', 'correct_option'];

        foreach ($required as $column) {
            if (! in_array($column, $header, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Trim surrounding whitespace and strip a leading UTF-8 BOM.
     */
    protected function cleanValue(string $value): string
    {
        $value = trim($value);

        if (str_starts_with($value, "\xEF\xBB\xBF")) {
            $value = substr($value, 3);
        }

        return $value;
    }

    /**
     * Map a CSV row (by header) onto the question repeater data shape.
     *
     * @param  array<int, string>  $header
     * @param  array<int, string|null>  $row
     * @return array<string, mixed>
     */
    protected function mapRow(array $header, array $row): array
    {
        $values = [];

        foreach ($header as $index => $column) {
            $values[$column] = $row[$index] ?? '';
        }

        $options = [];

        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $letter) {
            $text = trim((string) ($values['option_'.strtolower($letter)] ?? ''));

            if ($text !== '') {
                $options[] = [
                    'option_text' => $text,
                    'is_correct' => strtoupper(trim((string) ($values['correct_option'] ?? ''))) === $letter,
                ];
            }
        }

        $points = (int) ($values['points'] ?? 1);

        if ($points < 1) {
            $points = 1;
        }

        return [
            'question_text' => trim((string) ($values['question'] ?? '')),
            'points' => $points,
            'explanation' => trim((string) ($values['explanation'] ?? '')) ?: null,
            'question_image' => null,
            'question_video' => null,
            'options' => $options,
        ];
    }
}
