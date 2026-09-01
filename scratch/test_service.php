<?php

use App\Services\QuizImportService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Storage;

require 'C:\Users\Kasun Rathnayake\Herd\student-platform/vendor/autoload.php';
$app = require 'C:\Users\Kasun Rathnayake\Herd\student-platform/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$disk = Storage::disk(config('filament.default_filesystem_disk', 'local'));
echo 'disk='.config('filament.default_filesystem_disk', 'local').PHP_EOL;
$disk->put('quiz_imports/test.csv', "question,option_a,option_b,correct_option,points\nQ?,A,B,A,1\n");
echo 'exists='.var_export($disk->exists('quiz_imports/test.csv'), true).PHP_EOL;
try {
    $svc = app(QuizImportService::class);
    $r = $svc->parseStoredPath('quiz_imports/test.csv');
    echo 'parsed='.count($r).PHP_EOL;
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage().PHP_EOL;
}
$disk->delete('quiz_imports/test.csv');
