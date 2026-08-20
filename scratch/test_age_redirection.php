<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$emails = ['student9@example.com', 'student5@example.com', 'student1@example.com'];

foreach ($emails as $email) {
    $user = User::where('email', $email)->first();
    $student = $user?->student;
    $age = $student?->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->age : 'N/A';
    $tier = $student?->getAgeTier();
    echo "Email: {$email} | Name: {$user?->name} | Age: {$age} | Tier: {$tier}\n";
}
