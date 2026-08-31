<?php

namespace App\Filament\Student\Pages;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Student;
use App\Services\StudentContextService;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class Dashboard extends BaseDashboard
{
    use PasswordValidationRules, ProfileValidationRules;

    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.student.pages.dashboard';

    public string $activeTab = 'dashboard';

    public string $tier = 'junior';

    /** @var int|string|null */
    public $activeClassFilterId = null;

    /** @var array<string, mixed>|null */
    public ?array $activeContext = null;

    /** @var Collection<int, array<string, mixed>>|null */
    public ?Collection $allContexts = null;

    public ?Student $student = null;

    public int $calendarYear = 0;

    public int $calendarMonth = 0;

    /** @var string|null */
    public $calendarSelectedDate = null;

    public string $calendarNoteText = '';

    public string $profileName = '';

    public string $profileEmail = '';

    public string $profilePhone = '';

    public string $profileAddress = '';

    public string $profileDateOfBirth = '';

    public string $profileGender = 'male';

    public string $profileParentName = '';

    public string $profileParentPhone = '';

    /** @var TemporaryUploadedFile|null */
    public $profilePhoto = null;

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPassword_confirmation = '';

    public string $profileMessage = '';

    public string $profileMessageType = 'success';

    public function mount(): void
    {
        $user = Auth::user();
        /** @var Student|null $student */
        $student = $user?->student;
        $this->student = $student;

        if (! $this->student instanceof Student) {
            return;
        }

        $this->tier = $this->student->getAgeTier();

        $service = app(StudentContextService::class);
        $this->activeContext = $service->getActiveContext($this->student);
        $this->allContexts = $service->getContextsGroupedBySchool($this->student);

        $this->profileName = $user->name;
        $this->profileEmail = $user->email;
        $this->profilePhone = $this->student->phone ?? '';
        $this->profileAddress = $this->student->address ?? '';
        $this->profileDateOfBirth = $this->student->date_of_birth ?? '';
        $this->profileGender = $this->student->gender ?? 'male';
        $this->profileParentName = $this->student->parent_name ?? '';
        $this->profileParentPhone = $this->student->parent_phone ?? '';

        $tab = request()->query('tab');
        if (is_string($tab) && in_array($tab, ['dashboard', 'classes', 'lessons', 'assignments', 'quizzes', 'grades', 'calendar', 'profile'], true)) {
            $this->activeTab = $tab;
        }
        $classFilter = request()->query('class_filter');
        if ($classFilter !== null && ctype_digit((string) $classFilter)) {
            $this->activeClassFilterId = (int) $classFilter;
        }
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'profileName' => $this->nameRules(),
            'profileEmail' => $this->emailRules($user->id),
            'profilePhone' => ['nullable', 'string', 'max:255'],
            'profileAddress' => ['nullable', 'string', 'max:500'],
            'profileDateOfBirth' => ['nullable', 'date', 'before:today'],
            'profileGender' => ['nullable', 'in:male,female,other'],
            'profileParentName' => ['nullable', 'string', 'max:255'],
            'profileParentPhone' => ['nullable', 'string', 'max:255'],
            'profilePhoto' => ['nullable', 'image', 'max:5120'],
        ], [
            'profileName.required' => 'Please tell us your name.',
            'profileEmail.required' => 'We need an email to reach you.',
            'profileEmail.email' => 'That email does not look quite right. Try again!',
            'profileEmail.unique' => 'That email is already used by another account.',
            'profileDateOfBirth.before' => 'Your date of birth must be in the past.',
            'profilePhoto.image' => 'Please choose an image file for your photo.',
            'profilePhoto.max' => 'Your photo is too big. Pick one under 5 MB.',
        ]);

        $user->name = $validated['profileName'];
        $user->email = $validated['profileEmail'];

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $student = $this->student;
        $student->phone = $validated['profilePhone'];
        $student->address = $validated['profileAddress'];
        $student->date_of_birth = $validated['profileDateOfBirth'] ?: null;
        $student->gender = $validated['profileGender'] ?: null;
        $student->parent_name = $validated['profileParentName'];
        $student->parent_phone = $validated['profileParentPhone'];

        if ($this->profilePhoto instanceof TemporaryUploadedFile) {
            $disk = (string) config('filament.default_filesystem_disk', 'local');
            $old = $student->profile_photo;
            $path = $this->profilePhoto->store('student-profile-photos', $disk);
            $student->profile_photo = $path;

            if ($old) {
                Storage::disk($disk)->delete($old);
            }

            $this->profilePhoto = null;
        }

        $student->save();

        $this->tier = $student->getAgeTier();

        $this->profileMessage = 'Your profile is updated. Nice job!';
        $this->profileMessageType = 'success';
    }

    public function updatePassword(): void
    {
        $this->validate([
            'currentPassword' => $this->currentPasswordRules(),
            'newPassword' => $this->passwordRules(),
            'newPassword_confirmation' => ['required', 'string'],
        ], [
            'currentPassword.required' => 'Please type your current password.',
            'currentPassword.current_password' => 'Your current password is not correct. Try again!',
            'newPassword.required' => 'Please pick a new password.',
            'newPassword.confirmed' => 'The two new passwords do not match. Try again!',
            'newPassword_confirmation.required' => 'Please repeat your new password.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->reset('currentPassword', 'newPassword', 'newPassword_confirmation');

        $this->profileMessage = 'Password changed! Keep it a secret!';
        $this->profileMessageType = 'success';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->activeClassFilterId = null;
    }

    public function openTab(string $tab, ?int $classId = null): void
    {
        $this->activeTab = $tab;
        $this->activeClassFilterId = $classId;
        if ($tab === 'calendar') {
            $this->calendarSelectedDate = null;
            $this->calendarNoteText = '';
        }
    }

    public function prevCalendarMonth(): void
    {
        $m = Carbon::create($this->calendarYear ?: now()->year, $this->calendarMonth ?: now()->month, 1)
            ->subMonth();
        $this->calendarYear = $m->year;
        $this->calendarMonth = $m->month;
        $this->calendarSelectedDate = null;
        $this->calendarNoteText = '';
    }

    public function nextCalendarMonth(): void
    {
        $m = Carbon::create($this->calendarYear ?: now()->year, $this->calendarMonth ?: now()->month, 1)
            ->addMonth();
        $this->calendarYear = $m->year;
        $this->calendarMonth = $m->month;
        $this->calendarSelectedDate = null;
        $this->calendarNoteText = '';
    }

    public function saveCalendarNote(): void
    {
        $date = $this->calendarSelectedDate;
        if (! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date) || ! $this->student) {
            return;
        }

        $trimmed = trim($this->calendarNoteText);
        if ($trimmed === '') {
            return;
        }

        $this->student->calendarNotes()->updateOrCreate(
            ['note_date' => $date],
            ['content' => $trimmed]
        );

        $this->calendarNoteText = '';
    }

    public function deleteCalendarNote(int $noteId): void
    {
        if (! $this->student) {
            return;
        }

        $this->student->calendarNotes()->where('id', $noteId)->delete();
    }

    public function switchContext(string $key): void
    {
        if (! $this->student) {
            return;
        }

        $service = app(StudentContextService::class);
        $service->setActiveContext($this->student, $key);
        $this->activeContext = $service->getActiveContext($this->student);
        $this->allContexts = $service->getContextsGroupedBySchool($this->student);
    }

    public function refreshContext(): void
    {
        $service = app(StudentContextService::class);
        $this->activeContext = $service->getActiveContext($this->student);
        $this->allContexts = $service->getContextsGroupedBySchool($this->student);
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/student/login');
    }

    protected function getViewData(): array
    {
        return [
            'tier' => $this->tier,
            'student' => $this->student,
            'activeContext' => $this->activeContext,
            'allContexts' => $this->allContexts,
            'firstName' => explode(' ', $this->student?->user->name ?? 'Student')[0],
        ];
    }
}
