<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $defaultPassword = Hash::make('12345678');

            $this->command->info('1. Creating Roles...');
            $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
            $schoolAdminRole = Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
            $teacherRole = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
            $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

            /*
            |--------------------------------------------------------------------------
            | 1. Super Admins
            |--------------------------------------------------------------------------
            */
            $this->command->info('2. Creating Super Admins...');
            $superAdminsData = [
                [
                    'name' => 'System Administrator',
                    'email' => 'admin1@example.com',
                ],
                [
                    'name' => 'Victoria Sterling',
                    'email' => 'admin2@example.com',
                ],
            ];

            foreach ($superAdminsData as $adminData) {
                $admin = User::updateOrCreate(
                    ['email' => $adminData['email']],
                    [
                        'name' => $adminData['name'],
                        'password' => $defaultPassword,
                        'must_change_password' => false,
                        'email_verified_at' => now(),
                    ]
                );
                $admin->syncRoles([$superAdminRole]);
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Schools
            |--------------------------------------------------------------------------
            */
            $this->command->info('3. Creating Schools...');
            $school1 = School::updateOrCreate(
                ['code' => 'HIA-2026'],
                [
                    'name' => 'Horizon International Academy',
                    'logo' => null,
                    'address' => '100 Innovation Boulevard, Colombo 07',
                    'phone' => '+94 11 234 5678',
                    'email' => 'info@horizonacademy.edu',
                    'is_active' => true,
                ]
            );

            $school2 = School::updateOrCreate(
                ['code' => 'OSC-2026'],
                [
                    'name' => 'Oakridge STEM College',
                    'logo' => null,
                    'address' => '250 Science Park Drive, Kandy',
                    'phone' => '+94 81 234 9876',
                    'email' => 'contact@oakridgestem.edu',
                    'is_active' => true,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 3. School Admins
            |--------------------------------------------------------------------------
            */
            $this->command->info('4. Creating School Admins...');
            $schoolAdminsData = [
                [
                    'name' => 'Marcus Vance',
                    'email' => 'schooladmin1@example.com',
                    'phone' => '+94 77 123 4001',
                    'address' => '12 Palm Grove, Colombo 03',
                    'schools' => [$school1->id],
                ],
                [
                    'name' => 'Elena Rostova',
                    'email' => 'schooladmin2@example.com',
                    'phone' => '+94 77 123 4002',
                    'address' => '45 Lakeside Terrace, Kandy',
                    'schools' => [$school2->id],
                ],
                [
                    'name' => 'Jonathan Hayes',
                    'email' => 'schooladmin3@example.com',
                    'phone' => '+94 77 123 4003',
                    'address' => '78 Central Avenue, Colombo 07',
                    'schools' => [$school1->id, $school2->id],
                ],
            ];

            foreach ($schoolAdminsData as $adminData) {
                $user = User::updateOrCreate(
                    ['email' => $adminData['email']],
                    [
                        'name' => $adminData['name'],
                        'password' => $defaultPassword,
                        'must_change_password' => false,
                        'email_verified_at' => now(),
                    ]
                );
                $user->syncRoles([$schoolAdminRole]);

                SchoolAdmin::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'phone' => $adminData['phone'],
                        'address' => $adminData['address'],
                    ]
                );

                $user->schools()->sync($adminData['schools']);
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Grades
            |--------------------------------------------------------------------------
            */
            $this->command->info('5. Creating Grades...');
            $grade10_S1 = Grade::firstOrCreate(
                ['school_id' => $school1->id, 'name' => 'Grade 10'],
                ['is_active' => true]
            );
            $grade11_S1 = Grade::firstOrCreate(
                ['school_id' => $school1->id, 'name' => 'Grade 11'],
                ['is_active' => true]
            );

            $grade8_S2 = Grade::firstOrCreate(
                ['school_id' => $school2->id, 'name' => 'Grade 8'],
                ['is_active' => true]
            );
            $grade9_S2 = Grade::firstOrCreate(
                ['school_id' => $school2->id, 'name' => 'Grade 9'],
                ['is_active' => true]
            );

            /*
            |--------------------------------------------------------------------------
            | 5. Learning Classes
            |--------------------------------------------------------------------------
            */
            $this->command->info('6. Creating Learning Classes...');
            // School 1 Classes
            $class1 = LearningClass::firstOrCreate(
                ['grade_id' => $grade10_S1->id, 'name' => '10-A Mathematics'],
                ['medium' => 'English', 'is_active' => true]
            );
            $class2 = LearningClass::firstOrCreate(
                ['grade_id' => $grade10_S1->id, 'name' => '10-B Science & Physics'],
                ['medium' => 'English', 'is_active' => true]
            );
            $class3 = LearningClass::firstOrCreate(
                ['grade_id' => $grade11_S1->id, 'name' => '11-A ICT & Computing'],
                ['medium' => 'English', 'is_active' => true]
            );
            $class4 = LearningClass::firstOrCreate(
                ['grade_id' => $grade11_S1->id, 'name' => '11-B Combined Science'],
                ['medium' => 'Sinhala', 'is_active' => true]
            );

            // School 2 Classes
            $class5 = LearningClass::firstOrCreate(
                ['grade_id' => $grade8_S2->id, 'name' => '8-A General Science'],
                ['medium' => 'English', 'is_active' => true]
            );
            $class6 = LearningClass::firstOrCreate(
                ['grade_id' => $grade8_S2->id, 'name' => '8-B English Literature'],
                ['medium' => 'English', 'is_active' => true]
            );
            $class7 = LearningClass::firstOrCreate(
                ['grade_id' => $grade9_S2->id, 'name' => '9-A Biology & Ecology'],
                ['medium' => 'English', 'is_active' => true]
            );
            $class8 = LearningClass::firstOrCreate(
                ['grade_id' => $grade9_S2->id, 'name' => '9-B Computer Studies'],
                ['medium' => 'Tamil', 'is_active' => true]
            );

            /*
            |--------------------------------------------------------------------------
            | 6. Teachers
            |--------------------------------------------------------------------------
            */
            $this->command->info('7. Creating Teachers...');
            $teachersData = [
                [
                    'name' => 'Dr. Robert Langdon',
                    'email' => 'teacher1@example.com',
                    'employee_no' => 'EMP-T-1001',
                    'phone' => '+94 77 234 5001',
                    'address' => '15 University Crescent, Colombo 03',
                    'schools' => [$school1->id],
                    'classes' => [$class2->id, $class4->id],
                ],
                [
                    'name' => 'Sarah Connor',
                    'email' => 'teacher2@example.com',
                    'employee_no' => 'EMP-T-1002',
                    'phone' => '+94 77 234 5002',
                    'address' => '88 Maple Street, Colombo 05',
                    'schools' => [$school1->id],
                    'classes' => [$class1->id],
                ],
                [
                    'name' => 'David Attenborough',
                    'email' => 'teacher3@example.com',
                    'employee_no' => 'EMP-T-1003',
                    'phone' => '+94 77 234 5003',
                    'address' => '34 Greenwood Lane, Kandy',
                    'schools' => [$school2->id],
                    'classes' => [$class5->id, $class7->id],
                ],
                [
                    'name' => 'Emily Dickinson',
                    'email' => 'teacher4@example.com',
                    'employee_no' => 'EMP-T-1004',
                    'phone' => '+94 77 234 5004',
                    'address' => '92 Poet\'s Corner, Kandy',
                    'schools' => [$school2->id],
                    'classes' => [$class6->id],
                ],
                [
                    'name' => 'Alan Turing',
                    'email' => 'teacher5@example.com',
                    'employee_no' => 'EMP-T-1005',
                    'phone' => '+94 77 234 5005',
                    'address' => '42 Cyber Road, Colombo 07',
                    'schools' => [$school1->id, $school2->id],
                    'classes' => [$class3->id, $class8->id],
                ],
            ];

            $createdTeachers = [];
            foreach ($teachersData as $tData) {
                $user = User::updateOrCreate(
                    ['email' => $tData['email']],
                    [
                        'name' => $tData['name'],
                        'password' => $defaultPassword,
                        'must_change_password' => false,
                        'email_verified_at' => now(),
                    ]
                );
                $user->syncRoles([$teacherRole]);

                $teacher = Teacher::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'employee_no' => $tData['employee_no'],
                        'phone' => $tData['phone'],
                        'address' => $tData['address'],
                    ]
                );

                $teacher->schools()->sync($tData['schools']);
                $teacher->classes()->sync($tData['classes']);

                $createdTeachers[$tData['email']] = $teacher;
            }

            /*
            |--------------------------------------------------------------------------
            | 7. Students & Enrollments
            |--------------------------------------------------------------------------
            */
            $this->command->info('8. Creating Students & Enrollments...');
            $studentsData = [
                // Grade 10 - School 1 (Age ~16)
                [
                    'name' => 'Liam Ethan Miller',
                    'email' => 'student1@example.com',
                    'gender' => 'male',
                    'dob' => '2010-04-15',
                    'admission_no' => 'ADM-2026-001',
                    'phone' => '+94 77 345 6001',
                    'address' => '23 Victoria Park, Colombo 07',
                    'parent_name' => 'Michael Miller',
                    'parent_phone' => '+94 77 456 7001',
                    'school_id' => $school1->id,
                    'grade_id' => $grade10_S1->id,
                    'classes' => [$class1->id, $class2->id],
                ],
                [
                    'name' => 'Sophia Grace Williams',
                    'email' => 'student2@example.com',
                    'gender' => 'female',
                    'dob' => '2010-08-22',
                    'admission_no' => 'ADM-2026-002',
                    'phone' => '+94 77 345 6002',
                    'address' => '45 Ocean Breeze Way, Colombo 03',
                    'parent_name' => 'Elizabeth Williams',
                    'parent_phone' => '+94 77 456 7002',
                    'school_id' => $school1->id,
                    'grade_id' => $grade10_S1->id,
                    'classes' => [$class1->id, $class2->id],
                ],
                // Grade 11 - School 1 (Age ~17)
                [
                    'name' => 'Noah Benjamin Davis',
                    'email' => 'student3@example.com',
                    'gender' => 'male',
                    'dob' => '2009-02-10',
                    'admission_no' => 'ADM-2026-003',
                    'phone' => '+94 77 345 6003',
                    'address' => '77 High Street, Colombo 05',
                    'parent_name' => 'James Davis',
                    'parent_phone' => '+94 77 456 7003',
                    'school_id' => $school1->id,
                    'grade_id' => $grade11_S1->id,
                    'classes' => [$class3->id, $class4->id],
                ],
                [
                    'name' => 'Olivia Rose Martinez',
                    'email' => 'student4@example.com',
                    'gender' => 'female',
                    'dob' => '2009-11-05',
                    'admission_no' => 'ADM-2026-004',
                    'phone' => '+94 77 345 6004',
                    'address' => '12 Pine Crest, Colombo 06',
                    'parent_name' => 'Carlos Martinez',
                    'parent_phone' => '+94 77 456 7004',
                    'school_id' => $school1->id,
                    'grade_id' => $grade11_S1->id,
                    'classes' => [$class3->id, $class4->id],
                ],
                // Grade 8 - School 2 (Age ~14)
                [
                    'name' => 'Ethan Alexander Brown',
                    'email' => 'student5@example.com',
                    'gender' => 'male',
                    'dob' => '2012-06-18',
                    'admission_no' => 'ADM-2026-005',
                    'phone' => '+94 77 345 6005',
                    'address' => '89 Mountain View Road, Kandy',
                    'parent_name' => 'Robert Brown',
                    'parent_phone' => '+94 77 456 7005',
                    'school_id' => $school2->id,
                    'grade_id' => $grade8_S2->id,
                    'classes' => [$class5->id, $class6->id],
                ],
                [
                    'name' => 'Emma Charlotte Taylor',
                    'email' => 'student6@example.com',
                    'gender' => 'female',
                    'dob' => '2012-09-30',
                    'admission_no' => 'ADM-2026-006',
                    'phone' => '+94 77 345 6006',
                    'address' => '104 Riverbank Lane, Kandy',
                    'parent_name' => 'Patricia Taylor',
                    'parent_phone' => '+94 77 456 7006',
                    'school_id' => $school2->id,
                    'grade_id' => $grade8_S2->id,
                    'classes' => [$class5->id, $class6->id],
                ],
                // Grade 9 - School 2 (Age ~15)
                [
                    'name' => 'Lucas Daniel Anderson',
                    'email' => 'student7@example.com',
                    'gender' => 'male',
                    'dob' => '2011-03-14',
                    'admission_no' => 'ADM-2026-007',
                    'phone' => '+94 77 345 6007',
                    'address' => '31 Valley Heights, Kandy',
                    'parent_name' => 'Richard Anderson',
                    'parent_phone' => '+94 77 456 7007',
                    'school_id' => $school2->id,
                    'grade_id' => $grade9_S2->id,
                    'classes' => [$class7->id, $class8->id],
                ],
                [
                    'name' => 'Ava Isabella Thomas',
                    'email' => 'student8@example.com',
                    'gender' => 'female',
                    'dob' => '2011-12-08',
                    'admission_no' => 'ADM-2026-008',
                    'phone' => '+94 77 345 6008',
                    'address' => '58 Hillside Drive, Kandy',
                    'parent_name' => 'Jennifer Thomas',
                    'parent_phone' => '+94 77 456 7008',
                    'school_id' => $school2->id,
                    'grade_id' => $grade9_S2->id,
                    'classes' => [$class7->id, $class8->id],
                ],
                // Grade 4 - School 1 (Age ~9 - Kids Tier)
                [
                    'name' => 'Leo Mason',
                    'email' => 'student9@example.com',
                    'gender' => 'male',
                    'dob' => '2017-05-12',
                    'admission_no' => 'ADM-2026-009',
                    'phone' => '+94 77 345 6009',
                    'address' => '14 Sunset Avenue, Colombo 04',
                    'parent_name' => 'Arthur Mason',
                    'parent_phone' => '+94 77 456 7009',
                    'school_id' => $school1->id,
                    'grade_id' => $grade10_S1->id,
                    'classes' => [$class1->id],
                ],
            ];

            $createdStudents = [];
            foreach ($studentsData as $sData) {
                $user = User::updateOrCreate(
                    ['email' => $sData['email']],
                    [
                        'name' => $sData['name'],
                        'password' => $defaultPassword,
                        'must_change_password' => false,
                        'email_verified_at' => now(),
                    ]
                );
                $user->syncRoles([$studentRole]);

                $student = Student::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'admission_no' => $sData['admission_no'],
                        'date_of_birth' => $sData['dob'],
                        'gender' => $sData['gender'],
                        'phone' => $sData['phone'],
                        'address' => $sData['address'],
                        'parent_name' => $sData['parent_name'],
                        'parent_phone' => $sData['parent_phone'],
                    ]
                );

                $enrollment = StudentEnrollment::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'school_id' => $sData['school_id'],
                        'grade_id' => $sData['grade_id'],
                        'academic_year' => 2026,
                    ],
                    [
                        'status' => 'active',
                    ]
                );

                foreach ($sData['classes'] as $classId) {
                    $student->classes()->syncWithPivotValues(
                        [$classId],
                        ['student_enrollment_id' => $enrollment->id],
                        false
                    );
                }

                $createdStudents[$sData['email']] = $student;
            }

            /*
            |--------------------------------------------------------------------------
            | 8. Lessons
            |--------------------------------------------------------------------------
            */
            $this->command->info('9. Creating Lessons...');
            $teacherLangdon = $createdTeachers['teacher1@example.com'];
            $teacherSarah = $createdTeachers['teacher2@example.com'];
            $teacherDavid = $createdTeachers['teacher3@example.com'];
            $teacherEmily = $createdTeachers['teacher4@example.com'];
            $teacherTuring = $createdTeachers['teacher5@example.com'];

            $lessonsData = [
                [
                    'learning_class_id' => $class1->id,
                    'teacher_id' => $teacherSarah->id,
                    'title' => 'Quadratic Equations & Parabolic Functions',
                    'description' => 'Master algebraic solutions and graphical interpretations of quadratic functions.',
                    'content' => '<h2>Introduction to Quadratic Equations</h2><p>A quadratic equation is in the standard form <code>ax² + bx + c = 0</code> where <code>a ≠ 0</code>.</p><h3>Key Formulas</h3><ul><li>Quadratic Formula: <code>x = (-b ± √(b² - 4ac)) / (2a)</code></li><li>Discriminant: <code>Δ = b² - 4ac</code></li></ul>',
                    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'sort_order' => 1,
                    'is_published' => true,
                ],
                [
                    'learning_class_id' => $class1->id,
                    'teacher_id' => $teacherSarah->id,
                    'title' => 'Coordinate Geometry & Trigonometric Ratios',
                    'description' => 'Calculations of slope, distance, midpoint, and sine/cosine/tangent applications.',
                    'content' => '<h2>Coordinate Geometry Principles</h2><p>Understanding cartesian planes and lines: slope <code>m = (y2 - y1) / (x2 - x1)</code>.</p>',
                    'video_url' => null,
                    'sort_order' => 2,
                    'is_published' => true,
                ],
                [
                    'learning_class_id' => $class2->id,
                    'teacher_id' => $teacherLangdon->id,
                    'title' => 'Newton\'s Laws of Motion & Dynamics',
                    'description' => 'In-depth exploration of inertia, force, acceleration, and action-reaction pairs.',
                    'content' => '<h2>Newtonian Mechanics</h2><p><strong>First Law:</strong> An object will remain at rest or in uniform motion unless acted upon by a net external force.</p><p><strong>Second Law:</strong> <code>F = ma</code></p><p><strong>Third Law:</strong> For every action, there is an equal and opposite reaction.</p>',
                    'video_url' => 'https://www.youtube.com/watch?v=kKKM8Y-u7ds',
                    'sort_order' => 1,
                    'is_published' => true,
                ],
                [
                    'learning_class_id' => $class3->id,
                    'teacher_id' => $teacherTuring->id,
                    'title' => 'Relational Database Design & Normalization',
                    'description' => 'Entity-relationship diagrams, primary/foreign keys, and 1NF to 3NF normalization.',
                    'content' => '<h2>Relational Database Concepts</h2><p>Database normalization is the process of structuring a relational database in accordance with a series of normal forms to reduce data redundancy.</p>',
                    'video_url' => null,
                    'sort_order' => 1,
                    'is_published' => true,
                ],
                [
                    'learning_class_id' => $class5->id,
                    'teacher_id' => $teacherDavid->id,
                    'title' => 'Ecosystem Dynamics & Energy Pyramids',
                    'description' => 'Trophic levels, food webs, and the 10% energy transfer rule in nature.',
                    'content' => '<h2>Ecosystems & Energy Flow</h2><p>Sunlight is the primary source of energy. Autotrophs convert solar energy into chemical energy through photosynthesis.</p>',
                    'video_url' => null,
                    'sort_order' => 1,
                    'is_published' => true,
                ],
                [
                    'learning_class_id' => $class6->id,
                    'teacher_id' => $teacherEmily->id,
                    'title' => 'Shakespearean Tragedy: Analysis of Macbeth',
                    'description' => 'Themes of ambition, fate, guilt, and dramatic irony in Act 1 and Act 2.',
                    'content' => '<h2>Themes in Macbeth</h2><p>Explore Shakespeare\'s depiction of unbridled ambition and its psychological toll on Lord and Lady Macbeth.</p>',
                    'video_url' => null,
                    'sort_order' => 1,
                    'is_published' => true,
                ],
            ];

            foreach ($lessonsData as $lData) {
                Lesson::firstOrCreate(
                    [
                        'learning_class_id' => $lData['learning_class_id'],
                        'title' => $lData['title'],
                    ],
                    $lData
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 9. Assignments
            |--------------------------------------------------------------------------
            */
            $this->command->info('10. Creating Assignments...');
            $assignmentsData = [
                [
                    'learning_class_id' => $class1->id,
                    'teacher_id' => $teacherSarah->id,
                    'title' => 'Quadratic Problem Set & Vertex Form Applications',
                    'description' => 'Complete the 10 quadratic word problems and plot the parabolic curves.',
                    'instructions' => '<p>Please complete all questions in the workbook pages 45-48. Show all working steps including factoring and discriminant calculations. Upload as PDF or Word document.</p>',
                    'max_score' => 100,
                    'availability_type' => 'immediate',
                    'start_at' => now()->subDays(5),
                    'end_at' => now()->addDays(5),
                    'allow_late_submissions' => true,
                    'late_submission_value' => 2,
                    'late_submission_unit' => 'days',
                    'allowed_submission_types' => ['pdf', 'docx'],
                    'is_published' => true,
                ],
                [
                    'learning_class_id' => $class2->id,
                    'teacher_id' => $teacherLangdon->id,
                    'title' => 'Physics Lab Report: Measuring Acceleration (F = ma)',
                    'description' => 'Document experimental data from the dynamic trolley friction experiment.',
                    'instructions' => '<p>Include hypothesis, apparatus list, data tables with uncertainties, graphical acceleration vs force curve, and conclusion.</p>',
                    'max_score' => 50,
                    'availability_type' => 'immediate',
                    'start_at' => now()->subDays(7),
                    'end_at' => now()->addDays(3),
                    'allow_late_submissions' => false,
                    'late_submission_value' => null,
                    'late_submission_unit' => null,
                    'allowed_submission_types' => ['pdf', 'docx', 'zip'],
                    'is_published' => true,
                ],
                [
                    'learning_class_id' => $class3->id,
                    'teacher_id' => $teacherTuring->id,
                    'title' => 'E-Commerce Database Schema & SQL Queries',
                    'description' => 'Design an ERD and write DDL/DML queries for an online bookstore.',
                    'instructions' => '<p>Submit your MySQL database creation script, sample seed data, and 5 analytical queries (JOINs, GROUP BY, subqueries).</p>',
                    'max_score' => 100,
                    'availability_type' => 'immediate',
                    'start_at' => now()->subDays(3),
                    'end_at' => now()->addDays(10),
                    'allow_late_submissions' => true,
                    'late_submission_value' => 3,
                    'late_submission_unit' => 'days',
                    'allowed_submission_types' => ['pdf', 'sql', 'zip', 'txt'],
                    'is_published' => true,
                ],
                [
                    'learning_class_id' => $class6->id,
                    'teacher_id' => $teacherEmily->id,
                    'title' => 'Macbeth: Literary Essay on Moral Corruption',
                    'description' => 'Write a 1,000-word analytical essay discussing Lady Macbeth\'s descent into madness.',
                    'instructions' => '<p>Cite textual evidence directly from the play using MLA formatting. Structure essay with clear thesis, body paragraphs, and conclusion.</p>',
                    'max_score' => 50,
                    'availability_type' => 'immediate',
                    'start_at' => now()->subDays(6),
                    'end_at' => now()->addDays(2),
                    'allow_late_submissions' => true,
                    'late_submission_value' => 1,
                    'late_submission_unit' => 'days',
                    'allowed_submission_types' => ['pdf', 'docx'],
                    'is_published' => true,
                ],
                [
                    'learning_class_id' => $class5->id,
                    'teacher_id' => $teacherDavid->id,
                    'title' => 'Ecosystem Field Study: Local Flora and Fauna Report',
                    'description' => 'Conduct biodiversity assessment in a designated natural habitat quadrant.',
                    'instructions' => '<p>Observe and catalog at least 8 distinct plant/animal species. Document ecological interactions and food web positioning.</p>',
                    'max_score' => 75,
                    'availability_type' => 'immediate',
                    'start_at' => now()->subDays(4),
                    'end_at' => now()->addDays(6),
                    'allow_late_submissions' => false,
                    'late_submission_value' => null,
                    'late_submission_unit' => null,
                    'allowed_submission_types' => ['pdf', 'docx', 'zip'],
                    'is_published' => true,
                ],
            ];

            $createdAssignments = [];
            foreach ($assignmentsData as $aData) {
                $assignment = Assignment::firstOrCreate(
                    [
                        'learning_class_id' => $aData['learning_class_id'],
                        'title' => $aData['title'],
                    ],
                    $aData
                );
                $createdAssignments[] = $assignment;
            }

            /*
            |--------------------------------------------------------------------------
            | 10. Assignment Submissions
            |--------------------------------------------------------------------------
            */
            $this->command->info('11. Creating Sample Submissions...');
            $student1 = $createdStudents['student1@example.com'];
            $student2 = $createdStudents['student2@example.com'];
            $student3 = $createdStudents['student3@example.com'];
            $student5 = $createdStudents['student5@example.com'];
            $student6 = $createdStudents['student6@example.com'];

            $assignment1 = $createdAssignments[0]; // Math
            $assignment2 = $createdAssignments[1]; // Physics
            $assignment3 = $createdAssignments[2]; // ICT
            $assignment4 = $createdAssignments[3]; // Macbeth
            $assignment5 = $createdAssignments[4]; // Bio

            // Submission 1: Graded
            AssignmentSubmission::updateOrCreate(
                [
                    'assignment_id' => $assignment1->id,
                    'student_id' => $student1->id,
                ],
                [
                    'content' => '<p>Here is my completed quadratic problem set. All 10 questions solved step-by-step with plotted parabolas.</p>',
                    'submitted_at' => now()->subDays(2),
                    'is_late' => false,
                    'score' => 92.00,
                    'feedback' => '<p>Excellent work Liam! Clear algebraic steps and accurate vertex coordinate calculations.</p>',
                    'graded_by' => $teacherSarah->id,
                    'graded_at' => now()->subDays(1),
                    'status' => 'graded',
                ]
            );

            // Submission 2: Submitted, pending grading
            AssignmentSubmission::updateOrCreate(
                [
                    'assignment_id' => $assignment1->id,
                    'student_id' => $student2->id,
                ],
                [
                    'content' => '<p>Attached is my worksheet submission for the quadratic equations assignment.</p>',
                    'submitted_at' => now()->subDay(),
                    'is_late' => false,
                    'score' => null,
                    'feedback' => null,
                    'graded_by' => null,
                    'graded_at' => null,
                    'status' => 'submitted',
                ]
            );

            // Submission 3: Graded Physics Lab
            AssignmentSubmission::updateOrCreate(
                [
                    'assignment_id' => $assignment2->id,
                    'student_id' => $student1->id,
                ],
                [
                    'content' => '<p>Physics acceleration lab report attached with raw data tables and friction analysis chart.</p>',
                    'submitted_at' => now()->subDays(3),
                    'is_late' => false,
                    'score' => 47.50,
                    'feedback' => '<p>Great methodology and error analysis. Well presented lab report.</p>',
                    'graded_by' => $teacherLangdon->id,
                    'graded_at' => now()->subDays(2),
                    'status' => 'graded',
                ]
            );

            // Submission 4: ICT Submission
            AssignmentSubmission::updateOrCreate(
                [
                    'assignment_id' => $assignment3->id,
                    'student_id' => $student3->id,
                ],
                [
                    'content' => '<p>Here is the SQL schema script and ER diagram for the bookstore relational database.</p>',
                    'submitted_at' => now()->subHours(12),
                    'is_late' => false,
                    'score' => null,
                    'feedback' => null,
                    'graded_by' => null,
                    'graded_at' => null,
                    'status' => 'submitted',
                ]
            );

            // Submission 5: English Macbeth essay (Graded)
            AssignmentSubmission::updateOrCreate(
                [
                    'assignment_id' => $assignment4->id,
                    'student_id' => $student5->id,
                ],
                [
                    'content' => '<p>Essay: The Disintegration of Conscience in Shakespeare\'s Macbeth. Analyzes Act 1-5 transitions.</p>',
                    'submitted_at' => now()->subDays(2),
                    'is_late' => false,
                    'score' => 45.00,
                    'feedback' => '<p>Insightful literary analysis Ethan! Excellent integration of quotes throughout.</p>',
                    'graded_by' => $teacherEmily->id,
                    'graded_at' => now()->subDay(),
                    'status' => 'graded',
                ]
            );

            // Submission 6: Biology Field Study (Submitted)
            AssignmentSubmission::updateOrCreate(
                [
                    'assignment_id' => $assignment5->id,
                    'student_id' => $student6->id,
                ],
                [
                    'content' => '<p>Quadrant field report submitted with photo catalog and food web diagram.</p>',
                    'submitted_at' => now()->subHours(6),
                    'is_late' => false,
                    'score' => null,
                    'feedback' => null,
                    'graded_by' => null,
                    'graded_at' => null,
                    'status' => 'submitted',
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 11. Quizzes, Questions & Multiple Choice Options
            |--------------------------------------------------------------------------
            */
            $this->command->info('12. Creating Quizzes & Questions...');

            // Quiz 1: Mathematics
            $quiz1 = Quiz::firstOrCreate(
                [
                    'learning_class_id' => $class1->id,
                    'title' => 'Quadratic Functions & Algebra Mastery Quiz',
                ],
                [
                    'teacher_id' => $teacherSarah->id,
                    'description' => 'Test your understanding of quadratic formulas, discriminants, and parabolic graphs.',
                    'instructions' => '<p>Answer all 4 questions. You have 30 minutes. Each question has 4-5 options and exactly one correct answer.</p>',
                    'time_limit_minutes' => 30,
                    'max_attempts' => 2,
                    'passing_percentage' => 60,
                    'total_points' => 0,
                    'show_correct_answers_after_submission' => true,
                    'shuffle_questions' => false,
                    'shuffle_options' => true,
                    'availability_type' => 'immediate',
                    'start_at' => now()->subDays(5),
                    'end_at' => now()->addDays(10),
                    'is_published' => true,
                ]
            );

            $quiz1Questions = [
                [
                    'question_text' => 'What is the discriminant (Δ) formula for the standard quadratic equation ax² + bx + c = 0?',
                    'explanation' => 'The discriminant is given by Δ = b² - 4ac, which determines the nature of the roots.',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'b² - 4ac', 'is_correct' => true],
                        ['option_text' => 'b² + 4ac', 'is_correct' => false],
                        ['option_text' => '-b ± √(b² - 4ac)', 'is_correct' => false],
                        ['option_text' => '4ac - b²', 'is_correct' => false],
                        ['option_text' => '2a / (-b)', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => 'What are the roots of the quadratic equation x² - 5x + 6 = 0?',
                    'explanation' => 'Factoring (x - 2)(x - 3) = 0 yields x = 2 and x = 3.',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'x = 2 and x = 3', 'is_correct' => true],
                        ['option_text' => 'x = -2 and x = -3', 'is_correct' => false],
                        ['option_text' => 'x = 1 and x = 6', 'is_correct' => false],
                        ['option_text' => 'x = -1 and x = -6', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => 'If the discriminant Δ < 0, what is the nature of the roots?',
                    'explanation' => 'A negative discriminant indicates that there are no real roots (two complex conjugate roots).',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'The equation has no real roots', 'is_correct' => true],
                        ['option_text' => 'The equation has two equal real roots', 'is_correct' => false],
                        ['option_text' => 'The equation has two distinct real roots', 'is_correct' => false],
                        ['option_text' => 'One root is zero', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => 'What are the coordinates of the vertex for the parabola given by y = (x - 3)² + 4?',
                    'explanation' => 'In vertex form y = a(x - h)² + k, the vertex is (h, k), which is (3, 4).',
                    'points' => 1,
                    'options' => [
                        ['option_text' => '(3, 4)', 'is_correct' => true],
                        ['option_text' => '(-3, 4)', 'is_correct' => false],
                        ['option_text' => '(3, -4)', 'is_correct' => false],
                        ['option_text' => '(-3, -4)', 'is_correct' => false],
                        ['option_text' => '(0, 13)', 'is_correct' => false],
                    ],
                ],
            ];

            $totalPts1 = 0;
            foreach ($quiz1Questions as $qIdx => $qData) {
                $q = QuizQuestion::firstOrCreate(
                    [
                        'quiz_id' => $quiz1->id,
                        'question_text' => $qData['question_text'],
                    ],
                    [
                        'explanation' => $qData['explanation'],
                        'points' => $qData['points'],
                        'sort_order' => $qIdx + 1,
                    ]
                );
                $totalPts1 += $qData['points'];

                foreach ($qData['options'] as $oIdx => $oData) {
                    QuizQuestionOption::firstOrCreate(
                        [
                            'quiz_question_id' => $q->id,
                            'option_text' => $oData['option_text'],
                        ],
                        [
                            'is_correct' => $oData['is_correct'],
                            'sort_order' => $oIdx + 1,
                        ]
                    );
                }
            }
            $quiz1->updateQuietly(['total_points' => $totalPts1]);

            // Quiz 2: Physics
            $quiz2 = Quiz::firstOrCreate(
                [
                    'learning_class_id' => $class2->id,
                    'title' => 'Newton\'s Laws of Motion Assessment',
                ],
                [
                    'teacher_id' => $teacherLangdon->id,
                    'description' => 'Conceptual and quantitative problems on classical mechanics and Newton\'s laws.',
                    'instructions' => '<p>Read each scenario carefully. Select the best answer among the options provided.</p>',
                    'time_limit_minutes' => 20,
                    'max_attempts' => 1,
                    'passing_percentage' => 50,
                    'total_points' => 0,
                    'show_correct_answers_after_submission' => true,
                    'shuffle_questions' => false,
                    'shuffle_options' => true,
                    'availability_type' => 'immediate',
                    'start_at' => now()->subDays(3),
                    'end_at' => now()->addDays(7),
                    'is_published' => true,
                ]
            );

            $quiz2Questions = [
                [
                    'question_text' => 'What is the SI unit of force?',
                    'explanation' => 'Force is measured in Newtons (N), where 1 N = 1 kg·m/s².',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'Newton (N)', 'is_correct' => true],
                        ['option_text' => 'Joule (J)', 'is_correct' => false],
                        ['option_text' => 'Pascal (Pa)', 'is_correct' => false],
                        ['option_text' => 'Watt (W)', 'is_correct' => false],
                        ['option_text' => 'Kilogram (kg)', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => 'Which law explains why passengers lean forward when a bus applies sudden brakes?',
                    'explanation' => 'Newton\'s First Law of Motion (Law of Inertia) states objects in motion tend to stay in motion.',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'Newton\'s First Law (Law of Inertia)', 'is_correct' => true],
                        ['option_text' => 'Newton\'s Second Law (F = ma)', 'is_correct' => false],
                        ['option_text' => 'Newton\'s Third Law (Action-Reaction)', 'is_correct' => false],
                        ['option_text' => 'Law of Conservation of Energy', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => 'A 5 kg trolley accelerates at 4 m/s². What is the net horizontal force applied?',
                    'explanation' => 'Using F = ma: F = 5 kg * 4 m/s² = 20 N.',
                    'points' => 1,
                    'options' => [
                        ['option_text' => '20 N', 'is_correct' => true],
                        ['option_text' => '1.25 N', 'is_correct' => false],
                        ['option_text' => '9 N', 'is_correct' => false],
                        ['option_text' => '0.8 N', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => 'When swimming, pushing water backward propels the swimmer forward. This illustrates:',
                    'explanation' => 'Newton\'s Third Law states every action force has an equal and opposite reaction force.',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'Newton\'s Third Law', 'is_correct' => true],
                        ['option_text' => 'Newton\'s First Law', 'is_correct' => false],
                        ['option_text' => 'Hooke\'s Law', 'is_correct' => false],
                        ['option_text' => 'Bernoulli\'s Principle', 'is_correct' => false],
                    ],
                ],
            ];

            $totalPts2 = 0;
            foreach ($quiz2Questions as $qIdx => $qData) {
                $q = QuizQuestion::firstOrCreate(
                    [
                        'quiz_id' => $quiz2->id,
                        'question_text' => $qData['question_text'],
                    ],
                    [
                        'explanation' => $qData['explanation'],
                        'points' => $qData['points'],
                        'sort_order' => $qIdx + 1,
                    ]
                );
                $totalPts2 += $qData['points'];

                foreach ($qData['options'] as $oIdx => $oData) {
                    QuizQuestionOption::firstOrCreate(
                        [
                            'quiz_question_id' => $q->id,
                            'option_text' => $oData['option_text'],
                        ],
                        [
                            'is_correct' => $oData['is_correct'],
                            'sort_order' => $oIdx + 1,
                        ]
                    );
                }
            }
            $quiz2->updateQuietly(['total_points' => $totalPts2]);

            // Quiz 3: ICT & Database
            $quiz3 = Quiz::firstOrCreate(
                [
                    'learning_class_id' => $class3->id,
                    'title' => 'Relational Database Concepts & SQL Quiz',
                ],
                [
                    'teacher_id' => $teacherTuring->id,
                    'description' => 'Comprehensive check on relational schema design, keys, and SQL queries.',
                    'instructions' => '<p>Answer all questions. Untimed quiz. Up to 3 attempts allowed.</p>',
                    'time_limit_minutes' => null, // Untimed
                    'max_attempts' => 3,
                    'passing_percentage' => 70,
                    'total_points' => 0,
                    'show_correct_answers_after_submission' => true,
                    'shuffle_questions' => true,
                    'shuffle_options' => true,
                    'availability_type' => 'immediate',
                    'start_at' => now()->subDays(2),
                    'end_at' => now()->addDays(14),
                    'is_published' => true,
                ]
            );

            $quiz3Questions = [
                [
                    'question_text' => 'Which SQL clause is used to filter rows returned by a SELECT query?',
                    'explanation' => 'The WHERE clause specifies search conditions for rows returned by a query.',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'WHERE', 'is_correct' => true],
                        ['option_text' => 'ORDER BY', 'is_correct' => false],
                        ['option_text' => 'GROUP BY', 'is_correct' => false],
                        ['option_text' => 'HAVING', 'is_correct' => false],
                        ['option_text' => 'LIMIT', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => 'What constraint uniquely identifies each record in a database table?',
                    'explanation' => 'A PRIMARY KEY uniquely identifies each record and does not allow null values.',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'PRIMARY KEY', 'is_correct' => true],
                        ['option_text' => 'FOREIGN KEY', 'is_correct' => false],
                        ['option_text' => 'CHECK CONSTRAINT', 'is_correct' => false],
                        ['option_text' => 'DEFAULT CONSTRAINT', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => 'Which Normal Form requires eliminating transitive functional dependencies?',
                    'explanation' => 'Third Normal Form (3NF) requires 2NF and no non-prime attribute to be transitively dependent on the primary key.',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'Third Normal Form (3NF)', 'is_correct' => true],
                        ['option_text' => 'First Normal Form (1NF)', 'is_correct' => false],
                        ['option_text' => 'Second Normal Form (2NF)', 'is_correct' => false],
                        ['option_text' => 'Boyce-Codd Normal Form (BCNF)', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => 'Which SQL statement is used to insert new records into a table?',
                    'explanation' => 'The INSERT INTO statement is used to insert new rows into a database table.',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'INSERT INTO', 'is_correct' => true],
                        ['option_text' => 'UPDATE', 'is_correct' => false],
                        ['option_text' => 'ALTER TABLE', 'is_correct' => false],
                        ['option_text' => 'ADD ROW', 'is_correct' => false],
                    ],
                ],
            ];

            $totalPts3 = 0;
            foreach ($quiz3Questions as $qIdx => $qData) {
                $q = QuizQuestion::firstOrCreate(
                    [
                        'quiz_id' => $quiz3->id,
                        'question_text' => $qData['question_text'],
                    ],
                    [
                        'explanation' => $qData['explanation'],
                        'points' => $qData['points'],
                        'sort_order' => $qIdx + 1,
                    ]
                );
                $totalPts3 += $qData['points'];

                foreach ($qData['options'] as $oIdx => $oData) {
                    QuizQuestionOption::firstOrCreate(
                        [
                            'quiz_question_id' => $q->id,
                            'option_text' => $oData['option_text'],
                        ],
                        [
                            'is_correct' => $oData['is_correct'],
                            'sort_order' => $oIdx + 1,
                        ]
                    );
                }
            }
            $quiz3->updateQuietly(['total_points' => $totalPts3]);

            /*
            |--------------------------------------------------------------------------
            | 12. Student Quiz Attempts & Submissions
            |--------------------------------------------------------------------------
            */
            $this->command->info('13. Creating Student Quiz Attempts...');

            // Attempt 1: Liam (student1) - Completed Math Quiz (4/4, 100%, Passed)
            $attempt1 = QuizAttempt::updateOrCreate(
                [
                    'quiz_id' => $quiz1->id,
                    'student_id' => $student1->id,
                    'attempt_number' => 1,
                ],
                [
                    'started_at' => now()->subHours(3),
                    'expires_at' => now()->subHours(3)->addMinutes(30),
                    'completed_at' => now()->subHours(2)->subMinutes(40),
                    'score' => 4.00,
                    'percentage' => 100.00,
                    'is_passed' => true,
                    'status' => 'submitted',
                ]
            );

            // Record all correct answers for attempt 1
            $quiz1->load('questions.options');
            foreach ($quiz1->questions as $q) {
                $correctOpt = $q->options->firstWhere('is_correct', true);
                QuizAttemptAnswer::updateOrCreate(
                    [
                        'quiz_attempt_id' => $attempt1->id,
                        'quiz_question_id' => $q->id,
                    ],
                    [
                        'quiz_question_option_id' => $correctOpt?->id,
                        'is_correct' => true,
                        'points_awarded' => $q->points,
                    ]
                );
            }

            // Attempt 2: Sophia (student2) - In Progress Math Quiz (Has 2 answers saved, timer still active)
            $attempt2 = QuizAttempt::updateOrCreate(
                [
                    'quiz_id' => $quiz1->id,
                    'student_id' => $student2->id,
                    'attempt_number' => 1,
                ],
                [
                    'started_at' => now()->subMinutes(10),
                    'expires_at' => now()->addMinutes(20), // 20 mins remaining!
                    'completed_at' => null,
                    'score' => 0.00,
                    'percentage' => 0.00,
                    'is_passed' => false,
                    'status' => 'in_progress',
                ]
            );

            // Save first 2 answers for Sophia
            $firstTwoQuestions = $quiz1->questions->take(2);
            foreach ($firstTwoQuestions as $q) {
                $correctOpt = $q->options->firstWhere('is_correct', true);
                QuizAttemptAnswer::updateOrCreate(
                    [
                        'quiz_attempt_id' => $attempt2->id,
                        'quiz_question_id' => $q->id,
                    ],
                    [
                        'quiz_question_option_id' => $correctOpt?->id,
                        'is_correct' => true,
                        'points_awarded' => $q->points,
                    ]
                );
            }

            // Attempt 3: Noah (student3) - Completed ICT Quiz (3/4, 75%, Passed)
            $attempt3 = QuizAttempt::updateOrCreate(
                [
                    'quiz_id' => $quiz3->id,
                    'student_id' => $student3->id,
                    'attempt_number' => 1,
                ],
                [
                    'started_at' => now()->subDay(),
                    'expires_at' => null, // untimed
                    'completed_at' => now()->subDay()->addMinutes(25),
                    'score' => 3.00,
                    'percentage' => 75.00,
                    'is_passed' => true,
                    'status' => 'submitted',
                ]
            );

            $quiz3->load('questions.options');
            foreach ($quiz3->questions as $idx => $q) {
                $opt = ($idx === 2)
                    ? $q->options->firstWhere('is_correct', false) // 1 wrong answer
                    : $q->options->firstWhere('is_correct', true);

                $isCorrect = ($idx !== 2);
                QuizAttemptAnswer::updateOrCreate(
                    [
                        'quiz_attempt_id' => $attempt3->id,
                        'quiz_question_id' => $q->id,
                    ],
                    [
                        'quiz_question_option_id' => $opt?->id,
                        'is_correct' => $isCorrect,
                        'points_awarded' => $isCorrect ? $q->points : 0,
                    ]
                );
            }

            // Attempt 4: Ethan (student5) - Completed Physics Quiz (2/4, 50%, Passed)
            $attempt4 = QuizAttempt::updateOrCreate(
                [
                    'quiz_id' => $quiz2->id,
                    'student_id' => $student5->id,
                    'attempt_number' => 1,
                ],
                [
                    'started_at' => now()->subDays(2),
                    'expires_at' => now()->subDays(2)->addMinutes(20),
                    'completed_at' => now()->subDays(2)->addMinutes(18),
                    'score' => 2.00,
                    'percentage' => 50.00,
                    'is_passed' => true,
                    'status' => 'submitted',
                ]
            );

            $quiz2->load('questions.options');
            foreach ($quiz2->questions as $idx => $q) {
                $isCorrect = ($idx < 2);
                $opt = $isCorrect
                    ? $q->options->firstWhere('is_correct', true)
                    : $q->options->firstWhere('is_correct', false);

                QuizAttemptAnswer::updateOrCreate(
                    [
                        'quiz_attempt_id' => $attempt4->id,
                        'quiz_question_id' => $q->id,
                    ],
                    [
                        'quiz_question_option_id' => $opt?->id,
                        'is_correct' => $isCorrect,
                        'points_awarded' => $isCorrect ? $q->points : 0,
                    ]
                );
            }

            $this->command->info('✓ Platform seeding completed successfully!');
        });
    }
}

