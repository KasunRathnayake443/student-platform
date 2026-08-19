<?php

namespace App\Filament\Resources\Teachers\Pages;

use App\Filament\Resources\Teachers\TeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditTeacher extends EditRecord
{
    protected static string $resource =
        TeacherResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {

        $user = $this->record->user;

        /*
        |--------------------------------------------------------------------------
        | User Information
        |--------------------------------------------------------------------------
        */

        if ($user) {

            $data['name'] =
                $user->name;

            $data['email'] =
                $user->email;

        }

        /*
        |--------------------------------------------------------------------------
        | School Assignment
        |--------------------------------------------------------------------------
        */

        $data['schools'] =

            $this->record
                ->schools()
                ->pluck('schools.id')
                ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Class Assignment
        |--------------------------------------------------------------------------
        */

        $data['classes'] =

            $this->record
                ->classes()
                ->pluck('learning_classes.id')
                ->toArray();

        return $data;

    }

    protected function mutateFormDataBeforeSave(array $data): array
    {

        $user =
            $this->record->user;

        /*
        |--------------------------------------------------------------------------
        | Update User
        |--------------------------------------------------------------------------
        */

        if ($user) {

            $user->update([

                'name' => $data['name'],

                'email' => $data['email'],

            ]);

            if (! empty($data['password'])) {

                $user->update([

                    'password' => Hash::make(
                        $data['password']
                    ),

                ]);

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Update Teacher Profile
        |--------------------------------------------------------------------------
        */

        $teacherData = [

            'employee_no' => $data['employee_no'] ?? null,

            'phone' => $data['phone'] ?? null,

            'address' => $data['address'] ?? null,

        ];

        // Only update photo if a new photo is uploaded

        if (
            isset($data['profile_photo'])
            &&
            $data['profile_photo']
        ) {

            $teacherData['profile_photo'] =
                $data['profile_photo'];

        }

        $this->record->update(
            $teacherData
        );

        /*
        |--------------------------------------------------------------------------
        | Sync Schools
        |--------------------------------------------------------------------------
        */

        $this->record

            ->schools()

            ->sync(

                $data['schools'] ?? []

            );

        /*
        |--------------------------------------------------------------------------
        | Sync Classes
        |--------------------------------------------------------------------------
        */

        $this->record

            ->classes()

            ->sync(

                $data['classes'] ?? []

            );

        unset($data['name']);

        unset($data['email']);

        unset($data['password']);

        return $data;

    }

    protected function getHeaderActions(): array
    {

        return [

            Actions\ViewAction::make(),

            Actions\DeleteAction::make(),

        ];

    }
}
