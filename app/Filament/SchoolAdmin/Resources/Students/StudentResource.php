<?php

namespace App\Filament\SchoolAdmin\Resources\Students;

use App\Filament\Resources\Students\Schemas\StudentForm;
use App\Filament\Resources\Students\Schemas\StudentInfolist;
use App\Filament\Resources\Students\Tables\StudentsTable;
use App\Filament\SchoolAdmin\Resources\Students\Pages\CreateStudent;
use App\Filament\SchoolAdmin\Resources\Students\Pages\EditStudent;
use App\Filament\SchoolAdmin\Resources\Students\Pages\ListStudents;
use App\Filament\SchoolAdmin\Resources\Students\Pages\ViewStudent;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'School Users';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'user.name';

    public static function form(Schema $schema): Schema
    {
        return StudentForm::configure($schema, [
            'schoolIds' => SchoolAdminScopes::schoolIds(),
            'passwordRequired' => false,
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StudentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'view' => ViewStudent::route('/{record}'),
            'edit' => EditStudent::route('/{record}/edit'),
        ];
    }

    /**
     * School admins only see students enrolled in at least one of their schools.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('enrollments', fn (Builder $query) => $query->whereIn('school_id', SchoolAdminScopes::schoolIds()));
    }

    /**
     * Create enrollments (and class links) for a student within the admin's schools,
     * skipping anything that already exists.
     *
     * @param  array{assign_school?: bool, schools?: array<int>, grades?: array<int>, classes?: array<int>, academic_year?: string|null, status?: string|null}  $data
     */
    public static function syncEnrollments(Student $student, array $data): void
    {
        if (empty($data['assign_school']) || empty($data['schools'])) {
            return;
        }

        $adminSchoolIds = SchoolAdminScopes::schoolIds();

        foreach (array_intersect($data['schools'], $adminSchoolIds) as $schoolId) {
            $grades = Grade::whereIn('id', $data['grades'] ?? [])
                ->where('school_id', $schoolId)
                ->get();

            foreach ($grades as $grade) {
                $enrollmentId = $student->enrollments()
                    ->where('school_id', $schoolId)
                    ->where('grade_id', $grade->id)
                    ->where('academic_year', $data['academic_year'] ?? date('Y'))
                    ->value('id');

                if (! $enrollmentId) {
                    $enrollment = StudentEnrollment::create([
                        'student_id' => $student->id,
                        'school_id' => $schoolId,
                        'grade_id' => $grade->id,
                        'academic_year' => $data['academic_year'] ?? date('Y'),
                        'status' => $data['status'] ?? 'active',
                    ]);

                    $enrollmentId = $enrollment->id;
                }

                foreach ($data['classes'] ?? [] as $classId) {
                    $classExists = LearningClass::where('id', $classId)
                        ->where('grade_id', $grade->id)
                        ->exists();

                    $alreadyAttached = $student->classes()
                        ->wherePivot('learning_class_id', $classId)
                        ->wherePivot('student_enrollment_id', $enrollmentId)
                        ->exists();

                    if ($classExists && ! $alreadyAttached) {
                        $student->classes()->attach($classId, [
                            'student_enrollment_id' => $enrollmentId,
                        ]);
                    }
                }
            }
        }
    }
}
