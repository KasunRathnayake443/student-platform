<?php

namespace App\Filament\SchoolAdmin\Resources\Teachers\Pages;

use App\Filament\SchoolAdmin\Resources\Teachers\TeacherResource;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    /**
     * Whether the admin clicked "Create & create another" before confirming
     * that the email already belongs to an existing account.
     */
    public bool $createAnotherAfterConfirmation = false;

    /**
     * Intercept creation when the email is already registered so the admin can
     * confirm adding that existing person to the selected school instead of
     * silently overwriting their account or teacher profile.
     */
    public function create(bool $another = false): void
    {
        if ($this->isCreating) {
            return;
        }

        if (! $this->emailBelongsToExistingUser()) {
            parent::create($another);

            return;
        }

        $this->createAnotherAfterConfirmation = $another;

        $this->mountAction('confirmExistingTeacher');
    }

    /**
     * Continue creating after the admin confirmed the existing account should
     * be linked to the selected school.
     */
    protected function createConfirmed(): void
    {
        $another = $this->createAnotherAfterConfirmation;

        $this->createAnotherAfterConfirmation = false;

        parent::create($another);
    }

    protected function confirmExistingTeacherAction(): Action
    {
        return Action::make('confirmExistingTeacher')
            ->requiresConfirmation()
            ->modalHeading('Teacher already in the system')
            ->modalDescription(function (): string {
                $user = $this->existingUserForSubmittedEmail();
                $email = $this->submittedEmail();
                $name = $user->name ?? 'A user';

                return "{$name} ({$email}) already has an account in the system. Do you want to add this teacher to {$this->selectedSchoolNames()}? Their existing account and teacher details will not be changed.";
            })
            ->modalSubmitActionLabel('Yes, add to school')
            ->modalCancelActionLabel('Cancel')
            ->action(function (): void {
                $this->createConfirmed();
            });
    }

    /**
     * Create (or link) a teacher account for the given email.
     *
     * - If a user with that email already exists, the account is reused and
     *   only the selected schools are attached. The user's name and the
     *   existing teacher profile are left untouched.
     * - If no account exists yet, a new user is created with a random
     *   password (or the one provided) and must_change_password set to true.
     */
    protected function handleRecordCreation(array $data): Teacher
    {
        return DB::transaction(function () use ($data) {
            $user = User::where('email', $data['email'])->first();

            if ($user) {
                return $this->linkExistingTeacher($user, $data);
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? Str::random(16)),
                'must_change_password' => true,
            ]);

            $user->assignRole('teacher');

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'profile_photo' => $data['profile_photo'] ?? null,
                'employee_no' => $data['employee_no'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            if (! empty($data['schools'])) {
                $teacher->schools()->syncWithoutDetaching($data['schools']);
            }

            if (! empty($data['classes'])) {
                $teacher->classes()->syncWithoutDetaching($data['classes']);
            }

            return $teacher;
        });
    }

    /**
     * Attach an existing account to the selected schools without changing any
     * of the account's or teacher profile's existing information.
     *
     * @param  array<string, mixed>  $data
     */
    protected function linkExistingTeacher(User $user, array $data): Teacher
    {
        if (! $user->hasRole('teacher')) {
            $user->assignRole('teacher');
        }

        $teacher = $user->teacher;

        if (! $teacher) {
            $teacher = Teacher::create([
                'user_id' => $user->id,
            ]);
        }

        if (! empty($data['schools'])) {
            $teacher->schools()->syncWithoutDetaching($data['schools']);
        }

        return $teacher;
    }

    protected function emailBelongsToExistingUser(): bool
    {
        return $this->existingUserForSubmittedEmail() !== null;
    }

    protected function existingUserForSubmittedEmail(): ?User
    {
        $email = $this->submittedEmail();

        if ($email === '') {
            return null;
        }

        return User::where('email', $email)->first();
    }

    protected function submittedEmail(): string
    {
        $email = data_get($this->data, 'email');

        if (! is_string($email)) {
            return '';
        }

        return trim($email);
    }

    protected function selectedSchoolNames(): string
    {
        $schoolIds = array_values(array_filter((array) data_get($this->data, 'schools', [])));

        if (empty($schoolIds)) {
            return 'the selected school';
        }

        $names = School::whereKey($schoolIds)->pluck('name')->all();

        if (empty($names)) {
            return 'the selected school';
        }

        if (count($names) === 1) {
            return $names[0];
        }

        $last = array_pop($names);

        return implode(', ', $names).' and '.$last;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (request()->has('school_id') && empty($data['schools'])) {
            $data['schools'] = [request()->get('school_id')];
        }

        return $data;
    }

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
