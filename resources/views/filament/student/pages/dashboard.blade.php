@php
    use Illuminate\Support\Carbon;
    use App\Models\Assignment;
    use App\Models\AssignmentSubmission;
    use App\Models\Lesson;
    use App\Models\Quiz;

    $activeClasses    = $activeContext ? $activeContext['classes'] : collect();
    $activeSchoolName = $activeContext ? ($activeContext['school']->name ?? 'School') : 'School';
    $activeGradeName  = $activeContext ? ($activeContext['grade']->name  ?? 'Grade')  : 'Grade';

    // All enrolled classes across EVERY school + grade (categorized for the My Classes tab / filters)
    $allClassGroups = collect();
    $allClasses     = collect();
    $classSchoolMap = [];
    if ($allContexts) {
        foreach ($allContexts as $schoolGroup) {
            $gradeGroups = [];
            foreach ($schoolGroup['contexts'] as $ctx) {
                $ctxClasses = $ctx['classes'] ?? collect();
                if ($ctxClasses->isNotEmpty()) {
                    $gradeGroups[] = ['grade' => $ctx['grade'], 'classes' => $ctxClasses];
                    foreach ($ctxClasses as $cls) {
                        $allClasses[] = $cls;
                        $classSchoolMap[$cls->id] = $schoolGroup['school']->name ?? '';
                    }
                }
            }
            if ($gradeGroups) {
                $allClassGroups->push(['school' => $schoolGroup['school'], 'groups' => collect($gradeGroups)]);
            }
        }
        $allClasses = $allClasses->keyBy('id')->values();
    }

    // Class tiles / rows / search index read $class->teachers — load once (1 query).
    // $activeContext/$allContexts hydrate to separate model instances on re-render.
    if ($allClasses->isNotEmpty()) {
        \Illuminate\Database\Eloquent\Collection::make($allClasses->all())->loadMissing('teachers.user');
    }
    if ($activeClasses->isNotEmpty()) {
        \Illuminate\Database\Eloquent\Collection::make($activeClasses->all())->loadMissing('teachers.user');
    }

    // Only fetch the heavy data groups the active tab actually renders.
    $needQuizStats   = in_array($activeTab, ['dashboard', 'quizzes'], true);
    $needAssignments = in_array($activeTab, ['dashboard', 'assignments', 'classes', 'calendar'], true);
    $needLessons     = in_array($activeTab, ['lessons', 'classes'], true);
    $needQuizzes     = in_array($activeTab, ['quizzes', 'classes', 'calendar'], true);
    $needGrades      = $activeTab === 'grades';
    $classIds        = $allClasses->pluck('id')->all();

    // Quiz stats
    $quizAttempts   = collect();
    $quizAvgPct     = 0;
    $quizPassed     = 0;
    $recentQuizzes  = collect();
    $chartData      = [];
    if ($needQuizStats && $student) {
        $quizAttempts  = $student->quizAttempts()->where('status', 'submitted')->with('quiz')->get();
        $quizAvgPct    = $quizAttempts->count() ? round($quizAttempts->avg('percentage'), 1) : 0;
        $quizPassed    = $quizAttempts->where('is_passed', true)->count();
        $recentQuizzes = $quizAttempts->sortByDesc('completed_at')->take(6);

        // Chart data: quiz scores by week (last 8 weeks)
        for ($w = 7; $w >= 0; $w--) {
            $weekStart = Carbon::now()->startOfWeek()->subWeeks($w);
            $weekEnd   = $weekStart->copy()->endOfWeek();
            $weekAttempts = $quizAttempts->filter(
                fn ($a) => $a->completed_at && $a->completed_at->between($weekStart, $weekEnd)
            );
            $chartData[] = [
                'label' => $weekStart->format('M j'),
                'value' => $weekAttempts->count() ? round($weekAttempts->avg('percentage'), 1) : 0,
            ];
        }
    }

    // Current class filter (set via class-card buttons or the filter dropdown)
    $classFilterId = ! blank($activeClassFilterId) ? (int) $activeClassFilterId : null;
    $filteredClass = $classFilterId ? $allClasses->firstWhere('id', $classFilterId) : null;

    // Assignments (one batched query across all enrolled classes)
    $allAssignments = collect();
    $assignmentsByClassId = [];
    $pendingAssignments = collect();
    $studentSubmissions = collect();
    $studentSubmissionMap = [];
    $submittedCount = 0;
    if ($needAssignments && $classIds) {
        $byClass = Assignment::query()
            ->whereIn('learning_class_id', $classIds)
            ->where('is_published', true)
            ->with(['learningClass', 'teacher.user', 'attachments'])
            ->get()
            ->groupBy('learning_class_id');

        foreach ($allClasses as $class) {
            $assignmentsByClassId[$class->id] = ($byClass[$class->id] ?? collect())
                ->sortBy(fn ($a) => $a->end_at ? $a->end_at->timestamp : PHP_INT_MAX)
                ->values();
        }
        $allAssignments = collect($assignmentsByClassId)->flatten(1)
            ->sortBy(fn ($a) => $a->end_at ? $a->end_at->timestamp : PHP_INT_MAX)
            ->values();

        if ($student && $allAssignments->isNotEmpty()) {
            $studentSubmissions = AssignmentSubmission::where('student_id', $student->id)
                ->whereIn('assignment_id', $allAssignments->pluck('id'))
                ->with(['attachments', 'grader.user', 'assignment'])
                ->get();
            foreach ($studentSubmissions as $sub) {
                $studentSubmissionMap[$sub->assignment_id] = $sub;
            }
            $submittedCount = count($studentSubmissionMap);
        }

        // Pending = published assignments the student has not submitted (derived in-memory)
        $pendingAssignments = $allAssignments
            ->filter(fn ($a) => ! isset($studentSubmissionMap[$a->id]))
            ->sortBy('end_at')
            ->values();
    }
    $classHasAssignments = [];
    foreach ($allClasses as $class) {
        $classHasAssignments[$class->id] = ($classFilterId === null || (int) $class->id === $classFilterId)
            && ($assignmentsByClassId[$class->id] ?? collect())->isNotEmpty();
    }

    // Lessons across all enrolled classes (published only), kept per class for grouped render
    $allLessons = collect();
    $lessonsByClassId = [];
    if ($needLessons && $classIds) {
        $byClass = Lesson::query()
            ->whereIn('learning_class_id', $classIds)
            ->where('is_published', true)
            ->with(['attachments', 'learningClass', 'teacher.user'])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('learning_class_id');

        foreach ($allClasses as $class) {
            $lessonsByClassId[$class->id] = ($byClass[$class->id] ?? collect())->values();
        }
        $allLessons = collect($lessonsByClassId)->flatten(1)->sortBy('sort_order')->values();
        if ($classFilterId) {
            $allLessons = $allLessons->where('learning_class_id', $classFilterId)->values();
        }
    }
    $classHasLessons = [];
    foreach ($allClasses as $class) {
        $classHasLessons[$class->id] = ($classFilterId === null || (int) $class->id === $classFilterId)
            && ($lessonsByClassId[$class->id] ?? collect())->isNotEmpty();
    }

    // Assignments / quiz attempts honouring the active class filter
    $filteredAssignments = $classFilterId
        ? $allAssignments->where('learning_class_id', $classFilterId)->values()
        : $allAssignments;
    $filteredQuizAttempts = $classFilterId
        ? $quizAttempts->filter(fn ($a) => $a->quiz && (int) $a->quiz->learning_class_id === $classFilterId)
        : $quizAttempts;

    // Quizzes across all enrolled classes (published only), enriched with the student's attempts
    $allQuizzes = collect();
    $quizzesByClassId = [];
    $myQuizAttemptsById = collect();
    $quizStatusById = [];
    $quizStateById = [];
    $classHasQuizzes = [];
    if ($needQuizzes && $classIds) {
        $byClass = Quiz::query()
            ->whereIn('learning_class_id', $classIds)
            ->where('is_published', true)
            ->withCount('questions')
            ->with(['learningClass', 'teacher.user'])
            ->get()
            ->groupBy('learning_class_id');

        foreach ($allClasses as $class) {
            $quizzesByClassId[$class->id] = ($byClass[$class->id] ?? collect())
                ->sortByDesc('created_at')
                ->values();
        }
        $allQuizzes = collect($quizzesByClassId)->flatten(1)->values();

        if ($student && $allQuizzes->isNotEmpty()) {
            $myQuizAttemptsById = $student->quizAttempts()
                ->whereIn('quiz_id', $allQuizzes->pluck('id'))
                ->with('quiz')
                ->get()
                ->groupBy('quiz_id');
        }
        foreach ($allQuizzes as $quiz) {
            $attempts = ($myQuizAttemptsById[$quiz->id] ?? collect())->values()
                ->sortByDesc('completed_at')
                ->values();
            $finished = $attempts->filter(fn ($a) => in_array($a->status, ['submitted', 'time_expired'], true));
            $latest = $attempts->first();
            $best = $finished->max('percentage');

            // Same logic as Quiz::canStudentAttempt()/getRemainingAttempts(), in-memory
            $maxAttempts = (int) ($quiz->max_attempts ?? 0);
            $finishedCount = $finished->count();
            $canAttempt = $quiz->isAvailable() && ! $quiz->isExpired()
                && ($maxAttempts === 0 || $finishedCount < $maxAttempts);
            $remaining = $maxAttempts === 0 ? null : max(0, $maxAttempts - $finishedCount);

            if (! $quiz->isAvailable()) {
                $state = 'locked';
            } elseif ($quiz->isExpired()) {
                $state = 'closed';
            } elseif (! $canAttempt) {
                $state = 'complete';
            } elseif ($finished->isEmpty()) {
                $state = 'available';
            } else {
                $state = ($latest && $latest->is_passed) ? 'retry_passed' : 'retry';
            }

            $quizStatusById[$quiz->id] = [
                'attempts' => $attempts,
                'finished_count' => $finishedCount,
                'latest' => $latest,
                'best' => $best !== null ? (int) round((float) $best) : null,
                'can_attempt' => $canAttempt,
                'remaining' => $remaining,
            ];
            $quizStateById[$quiz->id] = $state;
        }
    }
    foreach ($allClasses as $class) {
        $classHasQuizzes[$class->id] = ($classFilterId === null || (int) $class->id === $classFilterId)
            && ($quizzesByClassId[$class->id] ?? collect())->isNotEmpty();
    }
    $filteredQuizzes = $classFilterId
        ? $allQuizzes->where('learning_class_id', $classFilterId)->values()
        : $allQuizzes;

    // ═══════════════════════════════════════════════════════════════════
    // GRADES TAB DATA — all quiz attempts + assignment submissions
    // ═══════════════════════════════════════════════════════════════════
    $allGrades = collect();
    $gradesByClassId = [];
    $quizGradeCount = 0;
    $assignmentGradeCount = 0;
    $quizAvg = 0;
    $assignmentAvg = 0;
    $overallAvg = 0;
    $gradedSubmissionsCount = 0;
    $pendingGradesCount = 0;
    $allFeedbacks = collect();

    if ($needGrades && $student) {
        // Quiz results (all finished attempts with scores)
        $quizResults = $student->quizAttempts()
            ->with(['quiz.learningClass.grade.school', 'quiz.teacher.user'])
            ->whereIn('status', ['submitted', 'time_expired'])
            ->orderByDesc('completed_at')
            ->get();

        // Assignment results (all submitted or graded)
        $assignmentResults = $student->assignmentSubmissions()
            ->with(['assignment.learningClass.grade.school', 'assignment.teacher.user', 'grader.user'])
            ->whereIn('status', ['submitted', 'graded'])
            ->orderByDesc('submitted_at')
            ->get();

        // Merge into unified feed
        foreach ($quizResults as $qr) {
            $classId = $qr->quiz?->learning_class_id;
            $className = $qr->quiz?->learningClass?->name ?? 'Unknown Class';
            $teacherName = $qr->quiz?->teacher?->user?->name ?? 'Teacher';
            $schoolName = $qr->quiz?->learningClass?->grade?->school?->name ?? 'School';
            $gradeName = $qr->quiz?->learningClass?->grade?->name ?? 'Grade';

            $item = [
                'type' => 'quiz',
                'title' => $qr->quiz?->title ?? 'Quiz',
                'class_name' => $className,
                'class_id' => $classId,
                'teacher' => $teacherName,
                'school' => $schoolName,
                'grade_level' => $gradeName,
                'score' => (float) $qr->percentage,
                'points' => $qr->score,
                'max_points' => null,
                'is_passed' => $qr->is_passed,
                'status' => $qr->status === 'time_expired' ? 'timed_out' : 'submitted',
                'status_label' => $qr->status === 'time_expired' ? '⏰ Timed Out' : '✅ Completed',
                'date' => $qr->completed_at,
                'attempt_number' => $qr->attempt_number,
                'has_feedback' => false,
                'feedback' => null,
                'grader_name' => null,
                'model_id' => $qr->id,
                'quiz_id' => $qr->quiz_id,
                'only_shows_score' => (bool) ($qr->quiz?->onlyShowsScore() ?? false),
            ];
            $allGrades->push($item);

            if ($classId) {
                $gradesByClassId[$classId][] = $item;
            }
            $quizGradeCount++;
        }

        foreach ($assignmentResults as $ar) {
            $classId = $ar->assignment?->learning_class_id;
            $className = $ar->assignment?->learningClass?->name ?? 'Unknown Class';
            $teacherName = $ar->grader?->user?->name ?? $ar->assignment?->teacher?->user?->name ?? 'Teacher';
            $schoolName = $ar->assignment?->learningClass?->grade?->school?->name ?? 'School';
            $gradeName = $ar->assignment?->learningClass?->grade?->name ?? 'Grade';
            $maxScore = $ar->assignment?->max_score ?? 100;
            $scoreVal = $ar->score !== null ? (float) $ar->score : null;
            $pct = $scoreVal !== null ? round(($scoreVal / $maxScore) * 100, 1) : null;

            $item = [
                'type' => 'assignment',
                'title' => $ar->assignment?->title ?? 'Assignment',
                'class_name' => $className,
                'class_id' => $classId,
                'teacher' => $teacherName,
                'school' => $schoolName,
                'grade_level' => $gradeName,
                'score' => $pct,
                'points' => $scoreVal,
                'max_points' => $maxScore,
                'is_passed' => $pct !== null && $pct >= 50,
                'status' => $ar->status === 'graded' ? 'graded' : 'pending',
                'status_label' => $ar->status === 'graded' ? '✅ Graded' : '⏳ Pending Review',
                'date' => $ar->graded_at ?? $ar->submitted_at,
                'attempt_number' => null,
                'has_feedback' => (bool) $ar->feedback,
                'feedback' => $ar->feedback,
                'grader_name' => $ar->grader?->user?->name ?? null,
                'model_id' => $ar->id,
                'assignment_id' => $ar->assignment_id,
            ];
            $allGrades->push($item);

            if ($classId) {
                $gradesByClassId[$classId][] = $item;
            }
            $assignmentGradeCount++;

            if ($ar->status === 'graded') {
                $gradedSubmissionsCount++;
            } else {
                $pendingGradesCount++;
            }

            if ($ar->feedback) {
                $allFeedbacks->push($item);
            }
        }

        // Sort combined feed by date
        $allGrades = $allGrades->sortByDesc('date')->values();
        foreach ($gradesByClassId as &$items) {
            usort($items, fn ($a, $b) => ($b['date']?->timestamp ?? 0) <=> ($a['date']?->timestamp ?? 0));
        }

        // Summary stats
        $quizAvg = $quizResults->count() > 0
            ? round($quizResults->avg('percentage'), 1)
            : 0;
        $assignmentScores = $assignmentResults->where('status', 'graded')->filter(fn ($s) => $s->percentage() !== null);
        $assignmentAvg = $assignmentScores->count() > 0
            ? round($assignmentScores->avg(fn ($s) => $s->percentage()), 1)
            : 0;
        $totalGraded = $quizResults->count() + $assignmentScores->count();
        $totalScore = $quizResults->sum('percentage') + $assignmentScores->sum(fn ($s) => $s->percentage());
        $overallAvg = $totalGraded > 0 ? round($totalScore / $totalGraded, 1) : 0;
    }

    // Class filter for grades (reuses $classFilterId from above)
    $filteredGrades = $classFilterId
        ? ($gradesByClassId[$classFilterId] ?? [])
        : $allGrades->toArray();

    $classHasGrades = [];
    foreach ($allClasses as $class) {
        $classHasGrades[$class->id] = ! empty($gradesByClassId[$class->id]);
    }

    // Group quiz attempts so one quiz expands into all of its attempts.
    $gradeDisplayItems = collect($filteredGrades)
        ->groupBy(fn ($grade) => $grade['type'] === 'quiz'
            ? 'quiz-'.$grade['quiz_id']
            : 'assignment-'.$grade['model_id'])
        ->map(function ($items) {
            $first = $items->first();

            if (($first['type'] ?? null) !== 'quiz') {
                return $first;
            }

            $attempts = $items
                ->sortByDesc(fn ($attempt) => $attempt['date']?->timestamp ?? 0)
                ->values();
            $latest = $attempts->first();
            $best = $attempts
                ->sortByDesc(fn ($attempt) => (float) ($attempt['score'] ?? 0))
                ->first();

            return [
                'type' => 'quiz',
                'title' => $first['title'],
                'class_name' => $first['class_name'],
                'class_id' => $first['class_id'],
                'teacher' => $first['teacher'],
                'school' => $first['school'],
                'grade_level' => $first['grade_level'],
                'score' => (float) ($best['score'] ?? 0),
                'latest_score' => (float) ($latest['score'] ?? 0),
                'is_passed' => (bool) ($latest['is_passed'] ?? false),
                'status' => $latest['status'] ?? 'submitted',
                'status_label' => $latest['status_label'] ?? '✅ Completed',
                'date' => $latest['date'] ?? null,
                'attempt_count' => $attempts->count(),
                'attempts' => $attempts->all(),
                'quiz_id' => $first['quiz_id'],
                'only_shows_score' => (bool) ($first['only_shows_score'] ?? false),
                'has_feedback' => false,
                'feedback' => null,
                'grader_name' => null,
            ];
        })
        ->sortByDesc(fn ($item) => $item['date']?->timestamp ?? 0)
        ->values();
@endphp

<div x-data="{ openClassId: null, openAssignmentId: null, openGradeQuizId: null, mobileMenuOpen: false }" @keydown.escape.window="mobileMenuOpen = false">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    /* Reset Page Container - Modern Light Theme */
    html, body, .fi-layout, .fi-main {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100vh !important;
        height: 100dvh !important;
        max-height: 100vh !important;
        max-height: 100dvh !important;
        overflow: hidden !important;
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        color: #334155;
    }

    .custom-app-container {
        display: flex;
        height: 100vh;
        height: 100dvh;
        max-height: 100vh;
        max-height: 100dvh;
        width: 100%;
        background: #f8fafc;
        overflow: hidden;
    }

    /* ── CUSTOM LIGHT SIDEBAR (Fixed Left Navigation) ── */
    .custom-sidebar {
        width: 260px;
        height: 100vh;
        height: 100dvh;
        max-height: 100vh;
        max-height: 100dvh;
        background: #ffffff;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 1.5rem 1.15rem;
        flex-shrink: 0;
        z-index: 20;
        box-sizing: border-box;
    }

    .sidebar-scrollable-content {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 0.25rem;
        scrollbar-width: thin;
        scrollbar-color: #c4b5fd transparent;
    }
    .sidebar-scrollable-content::-webkit-scrollbar { width: 5px; }
    .sidebar-scrollable-content::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scrollable-content::-webkit-scrollbar-thumb { background: rgba(124, 58, 237, 0.35); border-radius: 999px; }

    .brand-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .brand-icon {
        width: 2.75rem;
        height: 2.75rem;
        background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
        border-radius: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
    }
    .brand-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.01em;
    }
    .brand-sub {
        font-size: 0.72rem;
        color: #4f46e5;
        font-weight: 700;
    }

    /* Nav Section */
    .nav-group-label {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
        margin: 1.5rem 0 0.6rem 0.5rem;
    }

    .nav-menu {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.75rem 1rem;
        border-radius: 0.85rem;
        color: #64748b;
        font-weight: 600;
        font-size: 0.92rem;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: 1px solid transparent;
        background: transparent;
        width: 100%;
        text-align: left;
    }

    .nav-link:hover {
        color: #0f172a;
        background: #f1f5f9;
        border-color: #e2e8f0;
    }

    .nav-link.active {
        color: #4f46e5;
        background: #eef2ff;
        border-color: #e0e7ff;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.06);
    }

    .nav-icon {
        font-size: 1.15rem;
    }

    /* School Context Switcher List */
    .ctx-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: 0.25rem;
    }
    .ctx-school-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .ctx-school-name {
        font-size: 0.78rem;
        font-weight: 800;
        color: #475569;
        padding: 0 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .ctx-grades {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .ctx-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.55rem 0.85rem 0.55rem 1.5rem;
        border-radius: 0.75rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.82rem;
        color: #475569;
    }
    .ctx-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    .ctx-item.active {
        background: #eef2ff;
        border-color: #e0e7ff;
        color: #4f46e5;
        font-weight: 700;
    }

    /* User Card Bottom Left */
    .sidebar-user-card {
        padding: 0.9rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        margin-top: 1rem;
        flex-shrink: 0;
    }
    .user-profile-row {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }
    .user-avatar {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #ffffff;
        font-size: 0.85rem;
        box-shadow: 0 4px 10px rgba(124, 58, 237, 0.25);
        flex-shrink: 0;
    }
    .user-avatar-img {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ffffff;
        box-shadow: 0 4px 10px rgba(124, 58, 237, 0.3);
        flex-shrink: 0;
    }
    .user-info {
        flex: 1;
        min-width: 0;
    }
    .user-name {
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .user-role {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 600;
    }

    .btn-sidebar-signout {
        width: 100%;
        padding: 0.5rem 0.75rem;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #ef4444;
        border-radius: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 700;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-sidebar-signout:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }

    /* ── MAIN LIGHT WORKSPACE (Scrollable Right Main Workspace) ── */
    .custom-workspace {
        flex: 1;
        height: 100vh;
        max-height: 100vh;
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow-y: auto;
        overflow-x: hidden;
        background: #f8fafc;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .custom-workspace::-webkit-scrollbar { width: 6px; }
    .custom-workspace::-webkit-scrollbar-track { background: transparent; }
    .custom-workspace::-webkit-scrollbar-thumb { background: rgba(100, 116, 139, 0.35); border-radius: 999px; }

    /* Topbar Header */
    .custom-topbar {
        padding: 1.5rem 2.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        gap: 1.5rem;
    }
    .topbar-title h1 {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.02em;
    }
    .topbar-title p {
        font-size: 0.88rem;
        color: #64748b;
        margin: 0.2rem 0 0;
        font-weight: 500;
    }

    .topbar-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .active-context-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1.1rem;
        background: #eef2ff;
        border: 1px solid #e0e7ff;
        border-radius: 9999px;
        color: #4f46e5;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .btn-signout-top {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 1.15rem;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 9999px;
        color: #dc2626;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-signout-top:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);
    }

    /* Content Container */
    .workspace-content {
        padding: 2rem 2.5rem;
        display: flex;
        flex-direction: column;
        gap: 2rem;
        width: 100%;
        box-sizing: border-box;
    }

    /* Self-contained junior/senior dashboard: hide the shared header and
       remove outer padding so the dashboard's own header spans full width */
    .custom-topbar.self-contained-hide { display: none; }
    .workspace-content.self-contained-content {
        padding: 0;
        max-width: none;
        gap: 0;
    }

    /* 4 Light KPI Cards Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
    }
    @media (max-width: 1100px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px)  { .kpi-grid { grid-template-columns: 1fr; } }

    .kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        padding: 1.35rem 1.5rem;
        position: relative;
        overflow: hidden;
        transition: all 0.25s ease;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.03);
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        border-color: #cbd5e1;
        box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.1);
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        border-radius: 1.25rem 1.25rem 0 0;
    }
    .kpi-card-purple::before { background: linear-gradient(90deg, #7c3aed, #c084fc); }
    .kpi-card-blue::before   { background: linear-gradient(90deg, #2563eb, #38bdf8); }
    .kpi-card-emerald::before{ background: linear-gradient(90deg, #059669, #34d399); }
    .kpi-card-amber::before  { background: linear-gradient(90deg, #d97706, #fbbf24); }

    .kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }
    .kpi-label {
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
    }
    .kpi-icon {
        font-size: 1.25rem;
    }
    .kpi-value {
        font-size: 2.2rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
        margin-bottom: 0.4rem;
    }
    .kpi-sub {
        font-size: 0.83rem;
        color: #64748b;
        font-weight: 600;
    }

    /* 2-Column Split Workspace Layout */
    .grid-two-col {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 1.75rem;
    }
    @media (max-width: 1000px) { .grid-two-col { grid-template-columns: 1fr; } }

    .glass-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 1.25rem;
        padding: 1.75rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }
    .glass-card-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    /* Performance Trend Chart Widget */
    .chart-bars-wrap {
        height: 160px;
        display: flex;
        align-items: flex-end;
        gap: 0.85rem;
        padding-top: 1rem;
    }
    .chart-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    .bar-track {
        width: 100%;
        background: #f1f5f9;
        border-radius: 0.5rem;
        height: 100%;
        display: flex;
        align-items: flex-end;
        overflow: hidden;
        position: relative;
    }
    .bar-fill {
        width: 100%;
        background: linear-gradient(180deg, #818cf8 0%, #4f46e5 100%);
        border-radius: 0.5rem 0.5rem 0 0;
        transition: height 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .bar-fill:hover {
        background: linear-gradient(180deg, #a5b4fc 0%, #4338ca 100%);
    }
    .chart-label {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 700;
    }

    /* My Classes Grid Cards */
    .classes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr));
        gap: 1.25rem;
    }
    .class-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 1.1rem;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
        transition: all 0.25s ease;
    }
    .class-card:hover {
        transform: translateY(-2px);
        border-color: #c7d2fe;
        box-shadow: 0 8px 18px rgba(79, 70, 229, 0.1);
    }
    .class-card-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.9rem;
        background: #eef2ff;
        border: 1px solid #e0e7ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    /* My Classes Table */
    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }
    .custom-table th {
        text-align: left;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .custom-table td {
        padding: 1rem 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.92rem;
    }
    .custom-table tr:last-child td {
        border-bottom: none;
    }
    .class-title {
        font-weight: 700;
        color: #0f172a;
    }
    .teacher-name {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .status-pill {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .status-ok { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .status-pending { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
    .status-warn { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .status-bad { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

    /* Deadline Timeline Checklist */
    .deadline-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .deadline-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1rem 1.15rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        transition: all 0.2s;
    }
    .deadline-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .deadline-info h4 {
        font-size: 0.93rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 0.25rem 0;
    }
    .deadline-info p {
        font-size: 0.8rem;
        color: #64748b;
        margin: 0;
        font-weight: 500;
    }
    .btn-action-start {
        padding: 0.45rem 0.95rem;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        border: none;
        border-radius: 0.65rem;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        white-space: nowrap;
    }
    .btn-action-start:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(79, 70, 229, 0.35);
    }

    /* Class Card Action Buttons (Open Lessons / Assignments / Quizzes) */
    .class-stats {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        font-size: 0.82rem;
        color: #475569;
        font-weight: 700;
        background: #f8fafc;
        padding: 0.6rem 0.85rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
    }
    .class-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.6rem;
    }
    .class-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        padding: 0.55rem 0.35rem;
        border-radius: 1rem;
        font-weight: 800;
        border: 2px solid;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .class-action-emoji {
        font-size: 1.05rem;
        line-height: 1;
    }
    .class-action-label {
        font-size: 0.72rem;
        font-weight: 800;
        line-height: 1.25;
        text-align: center;
    }
    .class-action-lesson { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .class-action-lesson:hover { background: #dbeafe; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(37, 99, 235, 0.2); }
    .class-action-assign { background: #fefce8; border-color: #fde68a; color: #a16207; }
    .class-action-assign:hover { background: #fef9c3; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(217, 119, 6, 0.2); }
    .class-action-quiz { background: #eef2ff; border-color: #c7d2fe; color: #4f46e5; }
    .class-action-quiz:hover { background: #e0e7ff; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.2); }
    .class-action-none {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        color: #94a3b8;
        font-weight: 700;
        text-align: center;
        padding: 0.55rem 0.3rem;
        border: 2px dashed #e2e8f0;
        border-radius: 1rem;
    }

    /* All Classes grouped by school + grade */
    .school-class-block {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }
    .school-class-block + .school-class-block {
        padding-top: 1.75rem;
        border-top: 2px dashed #e2e8f0;
    }
    .school-class-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 1.2rem;
        font-weight: 800;
        color: #0f172a;
        flex-wrap: wrap;
    }
    .school-class-count {
        font-size: 0.75rem;
        font-weight: 800;
        background: #eef2ff;
        color: #4f46e5;
        border: 1px solid #e0e7ff;
        border-radius: 999px;
        padding: 0.15rem 0.7rem;
    }
    .grade-class-block {
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
    }
    .grade-class-header {
        font-size: 0.92rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* Tab Filter Bar (Lessons / Assignments / Quizzes) */
    .tab-filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .tab-filter-label {
        font-size: 0.85rem;
        font-weight: 800;
        color: #475569;
    }
    .tab-filter-select {
        padding: 0.6rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.85rem;
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        background: #f8fafc;
        cursor: pointer;
        outline: none;
        min-width: 220px;
        transition: all 0.2s;
    }
    .tab-filter-select:focus {
        border-color: #4f46e5;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }
    .tab-filter-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 600;
    }

    /* Lessons Tab Cards */
    .lesson-list {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .lesson-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .lesson-item-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .lesson-class-badge {
        font-size: 0.75rem;
        font-weight: 800;
        background: #eef2ff;
        color: #4f46e5;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .lesson-date {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
    }
    .lesson-item h3 {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .lesson-desc {
        font-size: 0.9rem;
        color: #475569;
        margin: 0;
        line-height: 1.5;
        font-weight: 500;
    }
    .lesson-content {
        font-size: 0.9rem;
        color: #334155;
        background: #ffffff;
        padding: 0.85rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        line-height: 1.6;
    }
    .lesson-attachments {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        margin-top: 0.25rem;
    }
    .lesson-attachments .attach-label {
        font-size: 0.78rem;
        font-weight: 800;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .attach-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .attach-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 0.4rem 0.75rem;
        border-radius: 0.65rem;
        text-decoration: none;
    }
    .lesson-video-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #2563eb;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 0.45rem 0.85rem;
        border-radius: 0.65rem;
        text-decoration: none;
        align-self: flex-start;
    }
    .lesson-class-group {
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
        background: #fbfbff;
        border: 1px solid #e0e7ff;
        border-radius: 1.1rem;
        padding: 1.1rem;
    }
    .lesson-class-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .lesson-class-title {
        font-size: 1rem;
        font-weight: 800;
        color: #3730a3;
        letter-spacing: 0.01em;
    }
    .lesson-class-meta {
        font-size: 0.75rem;
        font-weight: 800;
        background: #eef2ff;
        color: #4f46e5;
        padding: 0.25rem 0.7rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .lesson-open-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        align-self: flex-start;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: #ffffff;
        font-weight: 800;
        font-size: 0.84rem;
        padding: 0.55rem 1.1rem;
        border-radius: 9999px;
        text-decoration: none;
        box-shadow: 0 6px 16px -6px rgba(79, 70, 229, 0.6);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .lesson-open-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px -6px rgba(79, 70, 229, 0.7);
        color: #ffffff;
    }
    .assignment-class-group {
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
        background: #fffbfa;
        border: 1px dashed #fecaca;
        border-radius: 1.1rem;
        padding: 1.1rem;
    }
    .assignment-class-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .assignment-class-title {
        font-size: 1rem;
        font-weight: 800;
        color: #9f1239;
        letter-spacing: 0.01em;
    }
    .assignment-class-meta {
        font-size: 0.75rem;
        font-weight: 800;
        background: #ffe4e6;
        color: #be123c;
        padding: 0.25rem 0.7rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .assignment-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .assignment-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .assignment-item-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .assignment-status-pill {
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.3rem 0.75rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .assignment-date {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
    }
    .assignment-item h3 {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .assignment-desc {
        font-size: 0.9rem;
        color: #475569;
        margin: 0;
        line-height: 1.5;
        font-weight: 500;
    }
    .assignment-meta-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .assignment-meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: #ffe4e6;
        border: 1px solid #fecdd3;
        color: #9f1239;
        font-weight: 700;
        font-size: 0.75rem;
        padding: 0.3rem 0.7rem;
        border-radius: 9999px;
    }
    .assignment-open-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        align-self: flex-start;
        background: linear-gradient(135deg, #e11d48 0%, #f43f5e 100%);
        color: #ffffff;
        font-weight: 800;
        font-size: 0.84rem;
        padding: 0.55rem 1.1rem;
        border-radius: 9999px;
        text-decoration: none;
        box-shadow: 0 6px 16px -6px rgba(225, 29, 72, 0.55);
        transition: transform 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
    }
    .assignment-open-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px -6px rgba(225, 29, 72, 0.65);
        color: #ffffff;
    }

    /* ── Assignment status colors (cards, pills, buttons) ── */
    .assignment-item {
        position: relative;
        border-left: 6px solid #e2e8f0;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .assignment-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px -12px rgba(15, 23, 42, 0.18);
    }
    .asg-graded { background: #ecfdf5; border-color: #a7f3d0; border-left-color: #059669; }
    .asg-submitted { background: #eff6ff; border-color: #bfdbfe; border-left-color: #2563eb; }
    .asg-returned { background: #fffbeb; border-color: #fde68a; border-left-color: #d97706; }
    .asg-pending { background: #f5f3ff; border-color: #ddd6fe; border-left-color: #7c3aed; }
    .asg-notstarted { background: #f8fafc; border-color: #e2e8f0; border-left-color: #64748b; }
    .asg-late { background: #fff7ed; border-color: #fed7aa; border-left-color: #ea580c; }
    .asg-closed { background: #fef2f2; border-color: #fecaca; border-left-color: #dc2626; }

    .assignment-status-pill.asg-pill-graded { background: #a7f3d0; color: #065f46; }
    .assignment-status-pill.asg-pill-submitted { background: #bfdbfe; color: #1e40af; }
    .assignment-status-pill.asg-pill-returned { background: #fde68a; color: #92400e; }
    .assignment-status-pill.asg-pill-pending { background: #ddd6fe; color: #5b21b6; }
    .assignment-status-pill.asg-pill-notstarted { background: #e2e8f0; color: #475569; }
    .assignment-status-pill.asg-pill-late { background: #fed7aa; color: #9a3412; }
    .assignment-status-pill.asg-pill-closed { background: #fecaca; color: #991b1b; }

    .assignment-open-btn.asg-btn-graded { background: linear-gradient(135deg, #059669, #10b981); box-shadow: 0 6px 16px -6px rgba(5, 150, 105, 0.55); }
    .assignment-open-btn.asg-btn-graded:hover { box-shadow: 0 10px 20px -6px rgba(5, 150, 105, 0.7); }
    .assignment-open-btn.asg-btn-submitted { background: linear-gradient(135deg, #2563eb, #3b82f6); box-shadow: 0 6px 16px -6px rgba(37, 99, 235, 0.55); }
    .assignment-open-btn.asg-btn-submitted:hover { box-shadow: 0 10px 20px -6px rgba(37, 99, 235, 0.7); }
    .assignment-open-btn.asg-btn-returned { background: linear-gradient(135deg, #d97706, #f59e0b); box-shadow: 0 6px 16px -6px rgba(217, 119, 6, 0.55); }
    .assignment-open-btn.asg-btn-returned:hover { box-shadow: 0 10px 20px -6px rgba(217, 119, 6, 0.7); }
    .assignment-open-btn.asg-btn-late { background: linear-gradient(135deg, #ea580c, #f97316); box-shadow: 0 6px 16px -6px rgba(234, 88, 12, 0.55); }
    .assignment-open-btn.asg-btn-late:hover { box-shadow: 0 10px 20px -6px rgba(234, 88, 12, 0.7); }
    .assignment-open-btn.asg-btn-closed { background: linear-gradient(135deg, #64748b, #94a3b8); box-shadow: 0 6px 16px -6px rgba(100, 116, 139, 0.55); }
    .assignment-open-btn.asg-btn-closed:hover { box-shadow: 0 10px 20px -6px rgba(100, 116, 139, 0.7); }
    .assignment-open-btn.asg-btn-notstarted { background: linear-gradient(135deg, #64748b, #94a3b8); box-shadow: 0 6px 16px -6px rgba(100, 116, 139, 0.55); }
    .assignment-open-btn.asg-btn-notstarted:hover { box-shadow: 0 10px 20px -6px rgba(100, 116, 139, 0.7); }

    /* ── Quiz cards (kids quiz tab) ── */
    .quiz-class-group {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        border: 4px solid #e9d5ff;
        border-radius: 1.75rem;
        background: #ffffff;
        padding: 1.4rem 1.5rem 1.1rem;
        box-shadow: 0 10px 30px rgba(124, 58, 237, 0.12);
        margin-bottom: 1.7rem;
    }
    .quiz-class-group.kidc-red { border-top: 10px solid #ef4444; }
    .quiz-class-group.kidc-blue { border-top: 10px solid #3b82f6; }
    .quiz-class-group.kidc-purple { border-top: 10px solid #a855f7; }
    .quiz-class-group.kidc-green { border-top: 10px solid #22c55e; }
    .quiz-class-group.kidc-orange { border-top: 10px solid #f97316; }
    .quiz-class-group.kidc-pink { border-top: 10px solid #ec4899; }
    .quiz-class-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .quiz-class-title {
        font-size: 1.5rem;
        font-weight: 900;
        color: #4c1d95;
    }
    .quiz-class-meta {
        font-size: 0.8rem;
        font-weight: 800;
        background: #ede9fe;
        color: #7c3aed;
        padding: 0.3rem 0.8rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .quiz-list {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .quiz-item {
        background: #faf5ff;
        border: 3px solid #ede9fe;
        border-radius: 1.5rem;
        padding: 1.5rem 1.5rem 1.7rem;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        box-shadow: 0 6px 18px rgba(139, 92, 246, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .quiz-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 26px rgba(139, 92, 246, 0.15);
    }
    .quiz-item-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .quiz-state-pill {
        font-weight: 900;
        font-size: 0.85rem;
        padding: 0.4rem 1rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .quiz-score-pill {
        font-weight: 900;
        font-size: 1rem;
        padding: 0.35rem 0.9rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .quiz-score-pass { background: #a7f3d0; color: #065f46; }
    .quiz-score-fail { background: #fecaca; color: #991b1b; }
    .quiz-item h3 {
        font-size: 1.4rem;
        font-weight: 800;
        color: #2e1065;
        margin: 0;
        line-height: 1.3;
    }
    .quiz-meta-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .quiz-meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: #ede9fe;
        color: #6d28d9;
        font-weight: 800;
        font-size: 0.8rem;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
    }
    .quiz-desc {
        font-size: 0.92rem;
        color: #574b6d;
        margin: 0;
        line-height: 1.5;
        font-weight: 600;
    }
    .quiz-open-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        align-self: flex-start;
        color: #ffffff;
        font-size: 1.05rem;
        font-weight: 900;
        padding: 0.8rem 1.9rem;
        border-radius: 9999px;
        text-decoration: none;
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        box-shadow: 0 10px 22px -8px rgba(124, 58, 237, 0.6);
        transition: transform 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
    }
    .quiz-open-btn:hover {
        transform: translateY(-3px) scale(1.03);
        color: #ffffff !important;
        background: linear-gradient(135deg, #6d28d9 0%, #9333ea 100%) !important;
    }
    .quiz-open-disabled {
        background: #cbd5e1 !important;
        box-shadow: none !important;
        cursor: not-allowed;
    }
    .quiz-open-disabled:hover { transform: none; }
    .qsp-available { background: #ddd6fe; color: #5b21b6; }
    .qsp-retry { background: #fde68a; color: #92400e; }
    .qsp-retry_passed { background: #a7f3d0; color: #065f46; }
    .qsp-complete { background: #a7f3d0; color: #065f46; }
    .qsp-locked { background: #e2e8f0; color: #475569; }
    .qsp-closed { background: #fecaca; color: #991b1b; }
    .qc-available { border-color: #ddd6fe; }
    .qc-retry { border-color: #fde68a; }
    .qc-retry_passed { border-color: #a7f3d0; }
    .qc-retry_passed .quiz-open-btn,
    .qc-retry_passed .quiz-open-btn:hover {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        color: #ffffff !important;
    }
    .qc-complete { border-color: #a7f3d0; }
    .qc-complete .quiz-open-btn,
    .qc-complete .quiz-open-btn:hover {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        color: #ffffff !important;
    }
    .qc-retry .quiz-open-btn,
    .qc-retry .quiz-open-btn:hover {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
        color: #ffffff !important;
    }
    .qc-locked { border-color: #e2e8f0; }
    .qc-closed { border-color: #fecaca; }

    /* Kids quiz tab: flatten school/grade levels, bold friendly colors */
    .kids-quizzes-panel .school-class-header,
    .kids-quizzes-panel .grade-class-header,
    .kids-quizzes-panel .quiz-class-meta { display: none; }
    .kids-quizzes-panel .school-class-block,
    .kids-quizzes-panel .grade-class-block { gap: 1.5rem; }

    /* Junior / Teens quiz tab: Modern Bento Grid styling with indigo/violet accents */
    .junior-quizzes-panel .quiz-class-group {
        border: 1px solid #e2e8f0;
        border-left: 5px solid #4f46e5;
        border-radius: 1.25rem;
        background: #ffffff;
        padding: 1.25rem 1.4rem;
        box-shadow: 0 4px 20px -6px rgba(79, 70, 229, 0.08);
        margin-bottom: 1.5rem;
    }
    .junior-quizzes-panel .quiz-class-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: #1e1b4b;
    }
    .junior-quizzes-panel .quiz-class-meta {
        background: #eef2ff;
        color: #4f46e5;
        font-weight: 800;
        font-size: 0.78rem;
    }
    .junior-quizzes-panel .quiz-item {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.15rem 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .junior-quizzes-panel .quiz-item:hover {
        transform: translateY(-2px);
        border-color: #c7d2fe;
        box-shadow: 0 8px 24px -6px rgba(79, 70, 229, 0.15);
    }
    .junior-quizzes-panel .quiz-item h3 {
        font-size: 1.12rem;
        font-weight: 800;
        color: #0f172a;
    }
    .junior-quizzes-panel .quiz-open-btn {
        font-size: 0.92rem;
        padding: 0.65rem 1.5rem;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: #ffffff !important;
        box-shadow: 0 6px 16px -6px rgba(79, 70, 229, 0.5);
    }
    .junior-quizzes-panel .quiz-open-btn:hover {
        background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%) !important;
        color: #ffffff !important;
    }
    .junior-quizzes-panel .quiz-meta-chip,
    .junior-quizzes-panel .quiz-state-pill,
    .junior-quizzes-panel .quiz-score-pill {
        font-size: 0.78rem;
        padding: 0.32rem 0.8rem;
    }
    .junior-quizzes-panel .quiz-desc { font-size: 0.86rem; color: #64748b; }

    /* Senior quiz tab: Clean, modern academic / executive assessment layout */
    .senior-quizzes-panel .quiz-class-group {
        border: 1px solid #e2e8f0;
        border-left: 4px solid #3b82f6;
        border-radius: 0.9rem;
        background: #ffffff;
        padding: 1.2rem 1.4rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 1.4rem;
    }
    .senior-quizzes-panel .quiz-class-title {
        font-size: 1.12rem;
        font-weight: 800;
        color: #0f172a;
    }
    .senior-quizzes-panel .quiz-class-meta {
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: 0.75rem;
        border-radius: 0.4rem;
    }
    .senior-quizzes-panel .quiz-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 1.1rem 1.25rem;
        box-shadow: none;
        transition: all 0.15s ease;
    }
    .senior-quizzes-panel .quiz-item:hover {
        transform: translateY(-1px);
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .senior-quizzes-panel .quiz-item h3 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
    }
    .senior-quizzes-panel .quiz-open-btn {
        font-size: 0.88rem;
        padding: 0.55rem 1.3rem;
        border-radius: 0.5rem;
        background: #1e293b;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }
    .senior-quizzes-panel .quiz-open-btn:hover {
        background: #0f172a !important;
        color: #ffffff !important;
        transform: translateY(-1px);
    }
    .senior-quizzes-panel .quiz-meta-chip,
    .senior-quizzes-panel .quiz-state-pill,
    .senior-quizzes-panel .quiz-score-pill {
        font-size: 0.75rem;
        padding: 0.28rem 0.7rem;
        border-radius: 0.4rem;
    }
    .senior-quizzes-panel .quiz-desc { font-size: 0.84rem; color: #64748b; }


    /* ═══ GRADES TAB ═══ */
    .kids-grades-panel .school-class-header,
    .kids-grades-panel .grade-class-header { display: none; }

    .kids-grade-card {
        background: #ffffff; border: 3px solid #ede9fe; border-radius: 1.35rem;
        padding: 1.4rem 1.6rem; margin-bottom: 1rem;
        box-shadow: 0 6px 18px -8px rgba(76,29,149,0.18);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .kids-grade-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px -8px rgba(76,29,149,0.25); }

    /* kidc-* top border colors */
    .kids-grade-card.kidc-red { border-top: 5px solid #f43f5e; }
    .kids-grade-card.kidc-blue { border-top: 5px solid #3b82f6; }
    .kids-grade-card.kidc-purple { border-top: 5px solid #8b5cf6; }
    .kids-grade-card.kidc-green { border-top: 5px solid #22c55e; }
    .kids-grade-card.kidc-orange { border-top: 5px solid #f97316; }
    .kids-grade-card.kidc-pink { border-top: 5px solid #ec4899; }

    .kids-grade-top {
        display: flex; align-items: center; justify-content: space-between; gap: 0.7rem;
        margin-bottom: 0.75rem; flex-wrap: wrap;
    }
    .kids-grade-type {
        font-size: 0.78rem; font-weight: 800; padding: 0.3rem 0.85rem;
        border-radius: 9999px; letter-spacing: 0.02em;
    }
    .kids-grade-type.kgc-quiz { background: #ede9fe; color: #6d28d9; }
    .kids-grade-type.kgc-assignment { background: #dbeafe; color: #1d4ed8; }

    .kids-grade-score {
        font-size: 1.3rem; font-weight: 900; padding: 0.3rem 1rem;
        border-radius: 9999px; background: #f0fdf4;
    }

    .kids-grade-title {
        font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 0.6rem;
        line-height: 1.3;
    }

    .kids-grade-meta {
        display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.7rem;
    }
    .kids-grade-meta span {
        font-size: 0.82rem; font-weight: 700; color: #64748b;
        background: #f8fafc; border: 1px solid #e2e8f0;
        padding: 0.3rem 0.75rem; border-radius: 9999px;
    }

    .kids-grade-status {
        display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;
    }
    .kids-grade-status-pill {
        font-size: 0.82rem; font-weight: 800; padding: 0.35rem 0.9rem;
        border-radius: 9999px;
    }
    .kgsp-pass { background: #dcfce7; color: #15803d; }
    .kgsp-fail { background: #fee2e2; color: #991b1b; }

    .kids-grade-points {
        font-size: 0.85rem; font-weight: 700; color: #64748b;
    }

    .kids-grade-feedback {
        margin-top: 1rem; padding: 1rem 1.15rem;
        background: #f5f3ff; border: 1px solid #ede9fe;
        border-radius: 1rem;
    }
    .kids-grade-feedback-head {
        font-size: 0.82rem; font-weight: 800; color: #6d28d9;
        margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;
    }
    .kids-grade-feedback-teacher { color: #8b5cf6; font-weight: 600; }
    .kids-grade-feedback-text {
        font-size: 0.95rem; line-height: 1.7; color: #475569; font-weight: 600;
    }

    /* Junior/Senior grades table feedback badge */
    .grades-feedback-section { margin-top: 1rem; }
    .grades-feedback-card {
        background: #ffffff; border: 1px solid #ede9fe; border-radius: 1rem;
        padding: 1.15rem 1.35rem; margin-bottom: 0.85rem;
    }
    .grades-feedback-head {
        display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;
        margin-bottom: 0.65rem;
    }
    .grades-feedback-type {
        font-size: 0.78rem; font-weight: 800; padding: 0.25rem 0.7rem;
        border-radius: 9999px; background: #ede9fe; color: #6d28d9;
    }
    .grades-feedback-title {
        font-weight: 800; color: #0f172a; font-size: 0.95rem;
    }
    .grades-feedback-class {
        font-size: 0.82rem; font-weight: 700; color: #64748b;
    }
    .grades-feedback-body { }
    .grades-feedback-text {
        font-size: 0.92rem; line-height: 1.7; color: #475569; font-weight: 600;
        margin: 0 0 0.5rem;
    }
    .grades-feedback-meta {
        font-size: 0.82rem; font-weight: 700; color: #94a3b8;
    }

    /* ═══ GRADES: EXPANDABLE QUIZ ATTEMPTS + DETAIL MODAL ═══ */
    [x-cloak] { display: none !important; }

    .grade-quiz-toggle {
        display: inline-flex; align-items: center; gap: 0.5rem;
        background: transparent; border: 0; padding: 0; cursor: pointer;
        color: #0f172a; font: inherit; font-weight: 800; text-align: left;
    }
    .grade-quiz-toggle:hover { color: #4f46e5; }
    .grade-quiz-toggle-icon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.25rem; height: 1.25rem; border-radius: 9999px;
        background: #eef2ff; color: #4f46e5; font-size: 0.75rem; font-weight: 900;
        transition: transform 0.15s ease;
    }
    .grade-quiz-toggle[aria-expanded="true"] .grade-quiz-toggle-icon { transform: rotate(90deg); }
    .grade-quiz-attempt-count {
        display: inline-flex; align-items: center; margin-left: 0.35rem;
        padding: 0.12rem 0.5rem; border-radius: 9999px;
        background: #f1f5f9; color: #64748b; font-size: 0.7rem; font-weight: 800;
    }
    .grade-attempt-sub {
        display: block; margin-top: 0.2rem;
        color: #64748b; font-size: 0.74rem; font-weight: 700;
    }
    .grade-attempts-row > td {
        padding: 0 0 0.9rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .grade-attempts-panel {
        margin-top: 0.65rem; padding: 0.75rem;
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.95rem;
    }
    .kids-grade-card .grade-attempts-panel { margin-top: 0.9rem; }
    .grade-attempt-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: 0.75rem 1rem; flex-wrap: wrap;
        padding: 0.7rem 0.85rem;
        background: #ffffff; border: 1px solid #eef2ff; border-radius: 0.8rem;
    }
    .grade-attempt-row + .grade-attempt-row { margin-top: 0.55rem; }
    .grade-attempt-info { display: flex; flex-direction: column; gap: 0.15rem; }
    .grade-attempt-info strong { color: #0f172a; font-size: 0.86rem; font-weight: 800; }
    .grade-attempt-meta { color: #64748b; font-size: 0.76rem; font-weight: 700; }
    .grade-attempt-side { display: flex; align-items: center; gap: 0.55rem; flex-wrap: wrap; }
    .grade-attempt-score { color: #4f46e5; font-size: 1rem; font-weight: 900; }
    .grade-attempt-status {
        padding: 0.22rem 0.6rem; border-radius: 9999px;
        font-size: 0.7rem; font-weight: 800;
    }
    .grade-attempt-status.is-pass { background: #dcfce7; color: #15803d; }
    .grade-attempt-status.is-fail { background: #fee2e2; color: #991b1b; }
    .grade-attempt-view {
        border: 0; border-radius: 9999px; cursor: pointer;
        background: #4f46e5; color: #ffffff;
        padding: 0.42rem 0.85rem; font-size: 0.74rem; font-weight: 800;
        transition: background 0.15s ease, transform 0.15s ease;
    }
    .grade-attempt-view:hover { background: #4338ca; transform: translateY(-1px); }
    .grade-attempt-hidden { color: #94a3b8; font-size: 0.74rem; font-weight: 700; }

    .grade-detail-overlay {
        position: fixed; inset: 0; z-index: 3000;
        display: flex; align-items: center; justify-content: center;
        padding: 1.25rem;
        background: rgba(15, 23, 42, 0.58);
        backdrop-filter: blur(5px);
    }
    .grade-detail-modal {
        width: min(920px, 100%); max-height: 90vh; overflow-y: auto;
        background: #ffffff; border-radius: 1.35rem;
        padding: 1.5rem;
        box-shadow: 0 28px 70px -18px rgba(15, 23, 42, 0.55);
    }
    .grade-detail-header {
        display: flex; align-items: flex-start; justify-content: space-between;
        gap: 1rem; margin-bottom: 1rem;
    }
    .grade-detail-kicker {
        display: inline-flex; align-items: center; gap: 0.35rem;
        color: #6d28d9; background: #f5f3ff; border: 1px solid #ede9fe;
        border-radius: 9999px; padding: 0.2rem 0.6rem;
        font-size: 0.72rem; font-weight: 800; margin-bottom: 0.4rem;
    }
    .grade-detail-header h2 { margin: 0 0 0.3rem; color: #0f172a; font-size: 1.25rem; font-weight: 900; }
    .grade-detail-header p { margin: 0; color: #64748b; font-size: 0.86rem; font-weight: 700; }
    .grade-detail-close {
        width: 2.25rem; height: 2.25rem; flex-shrink: 0;
        border: 1px solid #e2e8f0; border-radius: 9999px;
        background: #ffffff; color: #475569; cursor: pointer;
        font-size: 1.15rem; font-weight: 800; line-height: 1;
    }
    .grade-detail-close:hover { background: #f8fafc; color: #0f172a; }
    .grade-detail-ai {
        margin-bottom: 1rem; padding: 1rem 1.1rem;
        border: 1px solid #ddd6fe; border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #faf8ff 100%);
    }
    .grade-detail-ai h3 { margin: 0 0 0.5rem; color: #0f172a; font-size: 0.95rem; font-weight: 900; }
    .grade-detail-ai-summary {
        margin: 0 0 0.8rem; padding: 0.75rem 0.9rem;
        border-radius: 0.8rem; background: #f5f3ff; border: 1px solid #e9d5ff;
        color: #4c1d95; font-size: 0.86rem; line-height: 1.6; font-weight: 600;
    }
    .grade-detail-ai-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; }
    .grade-detail-ai-panel { padding: 0.75rem 0.85rem; border-radius: 0.8rem; background: #ffffff; border: 1px solid #e2e8f0; }
    .grade-detail-ai-panel.is-strength { border-color: #a7f3d0; background: #f0fdf4; }
    .grade-detail-ai-panel.is-focus { border-color: #fed7aa; background: #fff7ed; }
    .grade-detail-ai-panel h4 { margin: 0 0 0.45rem; color: #0f172a; font-size: 0.8rem; font-weight: 900; }
    .grade-detail-ai-area { padding-top: 0.4rem; margin-top: 0.4rem; border-top: 1px dashed #e2e8f0; }
    .grade-detail-ai-area:first-of-type { padding-top: 0; margin-top: 0; border-top: 0; }
    .grade-detail-ai-area strong { display: block; color: #1e293b; font-size: 0.8rem; }
    .grade-detail-ai-area span { color: #4f46e5; font-size: 0.72rem; font-weight: 900; }
    .grade-detail-ai-area p { margin: 0.2rem 0 0; color: #475569; font-size: 0.78rem; line-height: 1.5; font-weight: 600; }
    .grade-detail-next { margin-top: 0.75rem; padding: 0.75rem 0.9rem; border-radius: 0.8rem; background: #f8fafc; border: 1px solid #e2e8f0; }
    .grade-detail-next h4 { margin: 0 0 0.35rem; color: #0f172a; font-size: 0.8rem; font-weight: 900; }
    .grade-detail-next ul { margin: 0; padding-left: 1rem; color: #475569; font-size: 0.78rem; font-weight: 600; line-height: 1.5; }
    .grade-detail-ai-empty { margin: 0; color: #64748b; font-size: 0.82rem; font-weight: 600; }
    .grade-detail-questions { display: flex; flex-direction: column; gap: 0.8rem; }
    .grade-detail-question { padding: 0.95rem 1rem; border: 1px solid #e2e8f0; border-radius: 1rem; background: #ffffff; }
    .grade-detail-question.is-correct { border-color: #bbf7d0; background: #f0fdf4; }
    .grade-detail-question.is-wrong { border-color: #fecaca; background: #fef2f2; }
    .grade-detail-question.is-skipped { background: #f8fafc; }
    .grade-detail-question-head { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.5rem; }
    .grade-detail-question-num { color: #64748b; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; }
    .grade-detail-question-badge { padding: 0.16rem 0.55rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 800; }
    .grade-detail-question-badge.is-correct { background: #dcfce7; color: #15803d; }
    .grade-detail-question-badge.is-wrong { background: #fee2e2; color: #991b1b; }
    .grade-detail-question-badge.is-skipped { background: #e2e8f0; color: #64748b; }
    .grade-detail-question-points { margin-left: auto; color: #64748b; font-size: 0.72rem; font-weight: 800; }
    .grade-detail-question-text { margin: 0 0 0.55rem; color: #0f172a; font-size: 0.9rem; line-height: 1.55; font-weight: 700; }
    .grade-detail-media { margin: 0 0 0.6rem; border-radius: 0.75rem; overflow: hidden; background: #f1f5f9; }
    .grade-detail-media img { display: block; width: 100%; max-height: 260px; object-fit: contain; }
    .grade-detail-media video { display: block; width: 100%; max-height: 260px; }
    .grade-detail-options { display: flex; flex-direction: column; gap: 0.35rem; }
    .grade-detail-option {
        display: flex; align-items: center; justify-content: space-between; gap: 0.65rem;
        padding: 0.42rem 0.65rem; border-radius: 0.6rem;
        color: #475569; font-size: 0.82rem; font-weight: 600;
    }
    .grade-detail-option.is-correct { background: #dcfce7; color: #166534; }
    .grade-detail-option.is-selected:not(.is-correct) { background: #fee2e2; color: #991b1b; }
    .grade-detail-option em { font-size: 0.7rem; font-style: normal; font-weight: 800; white-space: nowrap; }
    .grade-detail-explanation {
        margin-top: 0.55rem; padding: 0.6rem 0.75rem; border-radius: 0.7rem;
        background: #fffbeb; border: 1px solid #fde68a;
        color: #92400e; font-size: 0.8rem; line-height: 1.55; font-weight: 600;
    }

    @media (max-width: 768px) {
        .custom-table tr.grade-attempts-row { padding: 0; background: transparent; border: 0; }
        .custom-table tr.grade-attempts-row > td.grade-attempts-cell { display: block; padding: 0; border: 0; text-align: left; }
        .custom-table tr.grade-attempts-row > td.grade-attempts-cell::before { content: none; }
    }
    @media (max-width: 640px) {
        .grade-detail-overlay { padding: 0; align-items: flex-end; }
        .grade-detail-modal { max-height: 94vh; border-radius: 1.25rem 1.25rem 0 0; padding: 1rem; }
        .grade-detail-ai-grid { grid-template-columns: 1fr; }
        .grade-attempt-side { width: 100%; justify-content: space-between; }
    }

    /* ── Assignment filter buttons (simple single-row strip) ── */
    .asg-filter-bar {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
        gap: 0.45rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        padding: 0.5rem 0.85rem;
        margin: 0 0 0.65rem 0;
        overflow-x: auto;
        box-shadow: 0 3px 12px rgba(99, 102, 241, 0.05);
    }
    .asg-filter-bar::-webkit-scrollbar { height: 5px; }
    .asg-filter-bar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
    .asg-filter-chip {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.15s ease;
    }
    .asg-filter-chip:hover {
        border-color: #a5b4fc;
        background: #eef2ff;
        color: #4338ca;
    }
    .asg-filter-chip-active {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-color: transparent;
        color: #ffffff;
        box-shadow: 0 6px 14px -6px rgba(99, 102, 241, 0.6);
    }
    .asg-filter-chip-active:hover {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #ffffff;
    }
    .asg-filter-chip-count {
        font-size: 0.68rem;
        font-weight: 800;
        background: #e2e8f0;
        color: #475569;
        padding: 0.06rem 0.42rem;
        border-radius: 999px;
    }
    .asg-filter-chip-active .asg-filter-chip-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    .asg-filter-active-note {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.76rem;
        font-weight: 800;
        color: #4338ca;
        background: #eef2ff;
        padding: 0.32rem 0.75rem;
        border-radius: 999px;
        white-space: nowrap;
    }

    /* ── Assignment status legend (slim single-row) ── */
    .asg-legend {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 0.9rem;
        margin-bottom: 1.05rem;
        padding: 0.45rem 0.85rem;
        background: #ffffff;
        border: 1px dashed #cbd5e1;
        border-radius: 999px;
        overflow-x: auto;
    }
    .asg-legend::-webkit-scrollbar { height: 5px; }
    .asg-legend::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
    .asg-legend-title {
        flex-shrink: 0;
        font-size: 0.9rem;
        line-height: 1;
    }
    .asg-legend-item {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 800;
        color: #475569;
        white-space: nowrap;
    }
    .asg-legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }
    .asg-legend-graded { background: #059669; }
    .asg-legend-submitted { background: #2563eb; }
    .asg-legend-returned { background: #d97706; }
    .asg-legend-pending { background: #7c3aed; }
    .asg-legend-notstarted { background: #64748b; }
    .asg-legend-late { background: #ea580c; }
    .asg-legend-closed { background: #dc2626; }

    /* ── Profile ── */
    .profile-wrap {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .profile-alert {
        padding: 1rem 1.25rem;
        border-radius: 1rem;
        font-weight: 700;
        font-size: 0.95rem;
        border: 1px solid;
    }
    .profile-alert-success { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
    .profile-alert-error { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    @media (max-width: 850px) { .profile-grid { grid-template-columns: 1fr; } }
    .profile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1.5rem;
        padding: 1.75rem;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.04);
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .profile-card-head {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .profile-avatar {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #7c3aed, #6366f1);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.4rem;
        flex-shrink: 0;
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
    }
    .profile-avatar-lock { background: linear-gradient(135deg, #d97706, #f59e0b); box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25); }
    .profile-card-head h3 { margin: 0; font-size: 1.15rem; font-weight: 800; color: #0f172a; }
    .profile-card-head p { margin: 0.2rem 0 0; font-size: 0.85rem; color: #64748b; font-weight: 500; }
    .profile-card form { display: flex; flex-direction: column; gap: 1rem; }
    .profile-form { display: flex; flex-direction: column; gap: 1rem; }
    .profile-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 600px) { .profile-field-row { grid-template-columns: 1fr; } }
    .profile-field { display: flex; flex-direction: column; gap: 0.45rem; }
    .profile-field span { font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #475569; }
    .profile-field input,
    .profile-field select {
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        font-size: 0.95rem;
        background: #f8fafc;
        color: #0f172a;
        outline: none;
        transition: all 0.2s;
    }
    .profile-field input:focus,
    .profile-field select:focus { border-color: #7c3aed; background: #ffffff; box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12); }
    .profile-error { color: #dc2626; font-size: 0.78rem; font-weight: 600; }
    .profile-photo-btn {
        display: inline-block;
        padding: 0.7rem 1.4rem;
        background: #f5f3ff;
        border: 2px dashed #c084fc;
        color: #7c3aed;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        align-self: flex-start;
    }
    .profile-photo-btn:hover { background: #ede9fe; transform: scale(1.02); }
    .profile-upload-progress { font-size: 0.8rem; color: #7c3aed; font-weight: 700; }
    .profile-grid-3 {
        display: grid;
        grid-template-columns: 1.2fr 2fr;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 850px) { .profile-grid-3 { grid-template-columns: 1fr; } }
    .profile-save-btn {
        align-self: flex-start;
        padding: 0.7rem 1.5rem;
        background: linear-gradient(135deg, #7c3aed, #6366f1);
        color: #ffffff;
        border: none;
        border-radius: 0.85rem;
        font-weight: 800;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 6px 18px rgba(124, 58, 237, 0.3);
    }
    .profile-save-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(124, 58, 237, 0.4); }
    .profile-note {
        padding: 1rem 1.25rem;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 1rem;
        color: #92400e;
        font-size: 0.88rem;
        font-weight: 600;
        line-height: 1.5;
    }

    /* Kids profile overrides */
    .kids-mode .profile-card { border: 3px solid #e9d5ff; border-radius: 1.75rem; }
    .kids-mode .profile-card-head h3 { font-size: 1.3rem; }
    .kids-mode .profile-field input { border-radius: 1rem; border-width: 2px; font-size: 1rem; }
    .kids-mode .profile-save-btn { border-radius: 999px; font-size: 1rem; padding: 0.75rem 1.75rem; }

    /* ── Kids: My Classes — big playful class cards ── */
    .kids-mode .classes-grid {
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
        gap: 1.75rem;
    }
    .kids-mode .class-card {
        border-radius: 1.9rem;
        border: 4px solid #e9d5ff;
        padding: 1.5rem 1.5rem 1.8rem;
        gap: 1.15rem;
        box-shadow: 0 10px 30px rgba(124, 58, 237, 0.12);
    }
    .kids-mode .class-card:hover {
        transform: translateY(-6px) scale(1.02);
        box-shadow: 0 18px 45px rgba(124, 58, 237, 0.24);
    }
    .kids-mode .kidc-red { border-top: 10px solid #ef4444; }
    .kids-mode .kidc-blue { border-top: 10px solid #3b82f6; }
    .kids-mode .kidc-purple { border-top: 10px solid #a855f7; }
    .kids-mode .kidc-green { border-top: 10px solid #22c55e; }
    .kids-mode .kidc-orange { border-top: 10px solid #f97316; }
    .kids-mode .kidc-pink { border-top: 10px solid #ec4899; }
    .kids-mode .class-card-icon {
        width: 4rem;
        height: 4rem;
        border-radius: 50%;
        border: none;
        font-size: 1.8rem;
        color: #ffffff;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
    }
    .kids-mode .kidc-red .class-card-icon { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .kids-mode .kidc-blue .class-card-icon { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .kids-mode .kidc-purple .class-card-icon { background: linear-gradient(135deg, #a855f7, #7c3aed); }
    .kids-mode .kidc-green .class-card-icon { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .kids-mode .kidc-orange .class-card-icon { background: linear-gradient(135deg, #f97316, #ea580c); }
    .kids-mode .kidc-pink .class-card-icon { background: linear-gradient(135deg, #ec4899, #db2777); }
    .kids-mode .class-card .status-pill {
        font-size: 0.85rem;
        font-weight: 800;
        padding: 0.45rem 1.1rem;
        background: #dcfce7;
        color: #15803d;
        border: 2px solid #86efac;
        border-radius: 999px;
    }
    .kids-mode .class-card h3 { font-size: 1.4rem !important; margin-bottom: 0.5rem !important; }
    .kids-mode .class-card p { font-size: 1rem !important; color: #7c6ba6 !important; }
    .kids-mode .class-stats {
        background: #faf5ff;
        border: 3px solid #ede9fe;
        border-radius: 1.25rem;
        padding: 0.9rem 1rem;
        font-size: 0.95rem;
        color: #4c1d95;
        justify-content: center;
        gap: 0.75rem;
    }
    .kids-mode .class-stats span {
        background: #ffffff;
        border-radius: 999px;
        padding: 0.45rem 0.95rem;
        border: 2px solid #e9d5ff;
        box-shadow: 0 3px 8px rgba(139, 92, 246, 0.1);
    }
    .kids-mode .class-actions { gap: 0.9rem; }
    .kids-mode .class-action-btn {
        border-radius: 1.4rem;
        padding: 1.05rem 0.35rem;
        gap: 0.5rem;
        border: 3px solid #ffffff;
        color: #ffffff;
        box-shadow: 0 10px 22px -8px rgba(0, 0, 0, 0.35);
    }
    .kids-mode .class-action-btn:hover { transform: translateY(-4px) scale(1.04); }
    .kids-mode .class-action-lesson { background: linear-gradient(160deg, #3b82f6, #2563eb); }
    .kids-mode .class-action-lesson:hover { background: linear-gradient(160deg, #60a5fa, #3b82f6); }
    .kids-mode .class-action-assign { background: linear-gradient(160deg, #fbbf24, #f59e0b); color: #78350f; }
    .kids-mode .class-action-assign:hover { background: linear-gradient(160deg, #fcd34d, #fbbf24); color: #78350f; }
    .kids-mode .class-action-quiz { background: linear-gradient(160deg, #a855f7, #7c3aed); }
    .kids-mode .class-action-quiz:hover { background: linear-gradient(160deg, #c084fc, #9333ea); }
    .kids-mode .class-action-emoji { font-size: 2rem; }
    .kids-mode .class-action-label {
        font-size: 0.7rem;
        font-weight: 900;
        line-height: 1.15;
        letter-spacing: -0.02em;
        min-width: 0;
        max-width: 100%;
        padding: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kids-mode .class-action-btn:hover .class-action-emoji { animation: kidc-wiggle 0.5s ease infinite; }
    @keyframes kidc-wiggle {
        0%, 100% { transform: rotate(-8deg); }
        50% { transform: rotate(8deg); }
    }
    .kids-mode .class-action-none {
        border-radius: 1.4rem;
        padding: 1.05rem 0.35rem;
        gap: 0.5rem;
        border: 3px dashed #c4b5fd;
        background: #faf5ff;
        color: #a78bfa;
        font-size: 0.85rem;
    }
    .kids-mode .class-action-none .class-action-emoji { font-size: 2rem; }
    .kids-mode .school-class-header { font-size: 1.35rem; color: #6b21a8; }
    .kids-mode .grade-class-header { color: #a855f7; font-size: 1rem; }
    .kids-mode .lesson-item {
        border: 3px solid #e9d5ff;
        border-radius: 1.25rem;
    }
    .kids-mode .lesson-item h3 { font-size: 1.2rem; }

    /* ── Kids: Lessons tab — simple & playful (panel-scoped) ── */
    .kids-lessons-panel .school-class-header,
    .kids-lessons-panel .grade-class-header,
    .kids-lessons-panel .lesson-class-meta { display: none; }
    .kids-lessons-panel .school-class-block,
    .kids-lessons-panel .grade-class-block { gap: 1.5rem; }
    .kids-lessons-panel .lesson-class-group {
        border: 4px solid #e9d5ff;
        border-radius: 1.75rem;
        background: #ffffff;
        padding: 1.4rem 1.5rem 1.1rem;
        box-shadow: 0 10px 30px rgba(124, 58, 237, 0.12);
        margin-bottom: 1.7rem;
    }
    .kids-lessons-panel .lesson-class-group.kidc-red { border-top: 10px solid #ef4444; }
    .kids-lessons-panel .lesson-class-group.kidc-blue { border-top: 10px solid #3b82f6; }
    .kids-lessons-panel .lesson-class-group.kidc-purple { border-top: 10px solid #a855f7; }
    .kids-lessons-panel .lesson-class-group.kidc-green { border-top: 10px solid #22c55e; }
    .kids-lessons-panel .lesson-class-group.kidc-orange { border-top: 10px solid #f97316; }
    .kids-lessons-panel .lesson-class-group.kidc-pink { border-top: 10px solid #ec4899; }
    .kids-lessons-panel .lesson-class-title {
        font-size: 1.5rem;
        font-weight: 900;
        color: #4c1d95;
    }
    .kids-lessons-panel .lesson-item {
        border: 3px solid #ede9fe;
        border-radius: 1.5rem;
        background: #faf5ff;
        padding: 1.5rem 1.5rem 1.7rem;
        box-shadow: 0 6px 18px rgba(139, 92, 246, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kids-lessons-panel .lesson-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 26px rgba(139, 92, 246, 0.15);
    }
    .kids-lessons-panel .lesson-item h3 { font-size: 1.4rem; color: #2e1065; }
    .kids-lessons-panel .lesson-class-badge {
        font-size: 0.9rem;
        padding: 0.35rem 0.9rem;
        background: #ede9fe;
        color: #6d28d9;
    }
    .kids-lessons-panel .lesson-date { font-size: 0.85rem; color: #7c6ba6; font-weight: 700; }
    .kids-lessons-panel .lesson-desc { font-size: 0.95rem; color: #574b6d; line-height: 1.6; }
    .kids-lessons-panel .lesson-open-btn {
        font-size: 1.05rem;
        padding: 0.8rem 1.9rem;
        color: #ffffff;
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        box-shadow: 0 10px 22px -8px rgba(124, 58, 237, 0.6);
        border-radius: 9999px;
    }
    .kids-lessons-panel .lesson-open-btn:hover {
        transform: translateY(-3px) scale(1.03);
        color: #ffffff;
    }

    /* ── Kids: Assignments tab — flat class cards, big friendly items ── */
    .kids-assignments-panel .school-class-header,
    .kids-assignments-panel .grade-class-header,
    .kids-assignments-panel .assignment-class-meta { display: none; }
    .kids-assignments-panel .school-class-block,
    .kids-assignments-panel .grade-class-block { gap: 1.5rem; }
    .kids-assignments-panel .assignment-class-group {
        border: 4px solid #e9d5ff;
        border-radius: 1.75rem;
        background: #ffffff;
        padding: 1.4rem 1.5rem 1.1rem;
        box-shadow: 0 10px 30px rgba(124, 58, 237, 0.12);
        margin-bottom: 1.7rem;
    }
    .kids-assignments-panel .assignment-class-group.kidc-red { border-top: 10px solid #ef4444; }
    .kids-assignments-panel .assignment-class-group.kidc-blue { border-top: 10px solid #3b82f6; }
    .kids-assignments-panel .assignment-class-group.kidc-purple { border-top: 10px solid #a855f7; }
    .kids-assignments-panel .assignment-class-group.kidc-green { border-top: 10px solid #22c55e; }
    .kids-assignments-panel .assignment-class-group.kidc-orange { border-top: 10px solid #f97316; }
    .kids-assignments-panel .assignment-class-group.kidc-pink { border-top: 10px solid #ec4899; }
    .kids-assignments-panel .assignment-class-title {
        font-size: 1.5rem;
        font-weight: 900;
        color: #4c1d95;
    }
    .kids-assignments-panel .assignment-item {
        border: 3px solid #ede9fe;
        border-radius: 1.5rem;
        padding: 1.5rem 1.5rem 1.7rem;
        box-shadow: 0 6px 18px rgba(139, 92, 246, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kids-assignments-panel .assignment-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 26px rgba(139, 92, 246, 0.15);
    }
    .kids-assignments-panel .assignment-item h3 { font-size: 1.4rem; color: #2e1065; }
    .kids-assignments-panel .assignment-status-pill { font-size: 0.85rem; padding: 0.4rem 1rem; }
    .kids-assignments-panel .assignment-date { font-size: 0.85rem; color: #7c6ba6; font-weight: 700; }
    .kids-assignments-panel .assignment-desc { font-size: 0.95rem; color: #574b6d; line-height: 1.6; }
    .kids-assignments-panel .assignment-meta-chip { font-size: 0.8rem; padding: 0.35rem 0.8rem; }
    .kids-assignments-panel .assignment-open-btn { font-size: 1.05rem; padding: 0.8rem 1.9rem; border-radius: 9999px; }
    .kids-assignments-panel .assignment-open-btn:hover { transform: translateY(-3px) scale(1.03); color: #ffffff; }

    /* Kids assignment filter strip: bigger, bouncy, purple & purple-lavender */
    .kids-mode .asg-filter-bar {
        background: #ffffff;
        border: 3px solid #e9d5ff;
        border-radius: 999px;
        padding: 0.55rem 0.8rem;
        box-shadow: 0 6px 20px rgba(168, 85, 247, 0.15);
    }
    .kids-mode .asg-filter-bar::-webkit-scrollbar-thumb { background: #c4b5fd; border-radius: 99px; }
    .kids-mode .asg-filter-chip {
        font-size: 0.95rem;
        padding: 0.5rem 1.15rem;
        border: 2px solid #e9d5ff;
        background: #fdf4ff;
        color: #581c87;
        border-radius: 999px;
        box-shadow: 0 3px 8px rgba(139, 92, 246, 0.12);
    }
    .kids-mode .asg-filter-chip:hover {
        border-color: #c084fc;
        background: #f5e9ff;
        color: #581c87;
        transform: translateY(-2px);
    }
    .kids-mode .asg-filter-chip-active {
        background: linear-gradient(135deg, #9333ea, #a855f7);
        border-color: transparent;
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(124, 58, 237, 0.45);
    }
    .kids-mode .asg-filter-chip-active:hover {
        background: linear-gradient(135deg, #9333ea, #a855f7);
        border-color: transparent;
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(124, 58, 237, 0.45);
        transform: none;
    }
    .kids-mode .asg-filter-chip-count {
        background: #ede9fe;
        color: #6d28d9;
        font-size: 0.72rem;
        padding: 0.1rem 0.5rem;
    }
    .kids-mode .asg-filter-chip-active .asg-filter-chip-count { background: rgba(255, 255, 255, 0.28); color: #ffffff; }
    .kids-mode .asg-filter-active-note { background: #fef3c7; color: #92400e; }
    .kids-mode .asg-legend {
        border: 2px dashed #e9d5ff;
        background: #ffffff;
        padding: 0.5rem 0.85rem;
    }
    .kids-mode .asg-legend-item { font-size: 0.8rem; color: #574b6d; }
    .kids-mode .asg-legend-title { font-size: 1.05rem; }
    .kids-mode .asg-legend-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 1.5px rgba(168, 85, 247, 0.25);
    }

    /* ══════════════════════════════════════════════════════════════
       KIDS MODE (Age 5-10): Big, bold, playful, colorful
       ══════════════════════════════════════════════════════════════ */

    /* Kids Sidebar */
    .kids-mode .custom-sidebar {
        width: 250px;
        background: linear-gradient(180deg, #faf5ff 0%, #f0e7ff 40%, #fce7f3 100%);
        border-right: 3px solid #e9d5ff;
        padding: 1.25rem 0.5rem;
    }
    /* Room inside the scroll clip box so hovered/active tabs can scale
       and translate without being cropped at the edges (overflow-x: hidden
       clips at the padding box — padding gives the transforms headroom). */
    .kids-mode .sidebar-scrollable-content {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    .kids-mode .brand-header {
        border-bottom: 3px dashed #d8b4fe;
        padding-bottom: 1rem;
        margin-bottom: 0.35rem;
    }
    .kids-mode .brand-icon {
        width: 3.1rem;
        height: 3.1rem;
        background: linear-gradient(135deg, #f472b6, #c084fc, #60a5fa);
        border-radius: 1.1rem;
        font-size: 1.6rem;
        box-shadow: 0 6px 20px rgba(244, 114, 182, 0.35);
        animation: kids-wiggle 3s ease-in-out infinite;
    }
    @keyframes kids-wiggle {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-5deg); }
        75% { transform: rotate(5deg); }
    }
    .kids-mode .brand-title {
        font-size: 1.2rem;
        font-weight: 900;
        background: linear-gradient(135deg, #7c3aed, #db2777);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .kids-mode .brand-sub {
        font-size: 0.78rem;
        color: #a855f7;
    }

    .kids-mode .nav-group-label {
        font-size: 0.85rem;
        font-weight: 900;
        color: #7c3aed;
        margin: 1.25rem 0 0.6rem 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .kids-mode .nav-menu {
        gap: 0.4rem;
    }
    .kids-mode .nav-link {
        padding: 0.8rem 1rem;
        border-radius: 0.9rem;
        font-size: 1rem;
        font-weight: 800;
        gap: 0.75rem;
        border: 2px solid transparent;
        background: rgba(255, 255, 255, 0.7);
        color: #6b21a8;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .kids-mode .nav-link:hover {
        background: #fff;
        border-color: #c084fc;
        color: #7c3aed;
        transform: translateX(4px) scale(1.02);
        box-shadow: 0 4px 16px rgba(168, 85, 247, 0.15);
    }
    .kids-mode .nav-link.active {
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        color: #fff;
        border-color: #7c3aed;
        box-shadow: 0 6px 24px rgba(124, 58, 237, 0.3);
        transform: scale(1.03);
    }
    .kids-mode .nav-link.active .nav-icon {
        transform: scale(1.2);
    }
    .kids-mode .nav-icon {
        font-size: 1.35rem;
        width: 1.7rem;
        text-align: center;
    }

    .kids-mode .ctx-school-name {
        font-size: 0.9rem;
        color: #7c3aed;
        font-weight: 900;
    }
    .kids-mode .ctx-item {
        padding: 0.55rem 0.85rem 0.55rem 1.4rem;
        border-radius: 0.8rem;
        font-size: 0.9rem;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.6);
        border: 2px solid #e9d5ff;
        color: #6b21a8;
    }
    .kids-mode .ctx-item:hover {
        background: #f5f3ff;
        border-color: #c084fc;
        transform: translateX(3px);
    }
    .kids-mode .ctx-item.active {
        background: linear-gradient(135deg, #ede9fe, #fce7f3);
        border-color: #a855f7;
        color: #7c3aed;
        font-weight: 900;
        box-shadow: 0 2px 12px rgba(168, 85, 247, 0.15);
    }

    /* Kids User Card */
    .kids-mode .sidebar-user-card {
        background: linear-gradient(135deg, #fdf4ff, #fce7f3);
        border: 2px solid #e9d5ff;
        border-radius: 1.25rem;
        padding: 1rem;
    }
    .kids-mode .user-avatar {
        width: 2.6rem;
        height: 2.6rem;
        font-size: 1rem;
        background: linear-gradient(135deg, #f472b6, #c084fc);
        box-shadow: 0 4px 14px rgba(244, 114, 182, 0.3);
    }
    .kids-mode .user-avatar-img {
        width: 2.6rem;
        height: 2.6rem;
    }
    .kids-mode .user-name {
        font-size: 0.95rem;
        font-weight: 900;
        color: #7c3aed;
    }
    .kids-mode .user-role {
        font-size: 0.82rem;
        color: #a855f7;
        font-weight: 700;
    }
    .kids-mode .btn-logout-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.85rem;
        font-size: 1.2rem;
        border: 2px solid #fda4af;
        background: #fff1f2;
    }

    /* Kids Topbar */
    .kids-mode .custom-topbar {
        background: linear-gradient(135deg, #faf5ff 0%, #fdf2f8 50%, #eff6ff 100%);
        border-bottom: 3px solid #e9d5ff;
        padding: 1.75rem 2.5rem;
    }
    .kids-mode .topbar-title h1 {
        font-size: 2rem;
        font-weight: 900;
        background: linear-gradient(135deg, #7c3aed, #db2777);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .kids-mode .topbar-title p {
        font-size: 1.05rem;
        color: #7c3aed;
        font-weight: 600;
    }
    .kids-mode .active-context-badge {
        background: linear-gradient(135deg, #ede9fe, #fce7f3);
        border: 2px solid #c084fc;
        color: #7c3aed;
        font-size: 0.95rem;
        font-weight: 800;
        padding: 0.65rem 1.3rem;
    }
    .kids-mode .btn-signout-top {
        font-size: 1rem;
        font-weight: 800;
        padding: 0.65rem 1.5rem;
        border-radius: 999px;
        border: 2px solid #fda4af;
    }

    /* Kids workspace content — hide topbar, fill edge-to-edge */
    .kids-mode .workspace-content {
        padding: 0;
        max-width: none;
    }
    .kids-mode .workspace-content.kids-tab-space {
        padding: 2.25rem 2.5rem 3.5rem;
    }
    .kids-mode .custom-topbar {
        display: none;
    }

    /* ── Mobile Navigation Shell (hidden on desktop) ── */
    .mobile-nav-header,
    .mobile-tab-strip,
    .mobile-sidebar-backdrop,
    .sidebar-close-btn {
        display: none;
    }

    /* ── Responsive: keep the layout on the screen ── */
    @media (max-width: 1180px) {
        .custom-sidebar, .kids-mode .custom-sidebar { width: 230px; }
    }
    @media (max-width: 860px) {
        html, body, .fi-layout, .fi-main {
            height: auto !important;
            max-height: none !important;
            min-height: 100vh !important;
            min-height: 100dvh !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
        }
        .custom-app-container {
            flex-direction: column;
            height: auto;
            max-height: none;
            min-height: 100vh;
            min-height: 100dvh;
            overflow: visible;
        }
        .mobile-nav-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.7rem 1rem;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 90;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        }
        .kids-mode .mobile-nav-header {
            background: linear-gradient(90deg, #faf5ff, #fdf2f8);
            border-bottom: 3px solid #e9d5ff;
        }
        .mobile-brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-width: 0;
        }
        .mobile-brand-logo {
            width: 2.15rem;
            height: 2.15rem;
            border-radius: 0.6rem;
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(124, 58, 237, 0.2);
        }
        .mobile-brand-text {
            min-width: 0;
        }
        .mobile-brand-title {
            font-size: 0.92rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .kids-mode .mobile-brand-title {
            color: #6b21a8;
            font-weight: 900;
        }
        .mobile-brand-ctx {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .mobile-menu-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.8rem;
            border-radius: 0.65rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #0f172a;
            font-family: inherit;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
        .mobile-menu-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        .kids-mode .mobile-menu-btn {
            background: linear-gradient(135deg, #7c3aed, #db2777);
            color: #ffffff;
            border: 2px solid #ffffff;
            border-radius: 999px;
            font-weight: 900;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
        }
        .mobile-tab-strip {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.85rem;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .mobile-tab-strip::-webkit-scrollbar { display: none; }
        .kids-mode .mobile-tab-strip {
            background: #faf5ff;
            border-bottom: 2px dashed #e9d5ff;
            padding: 0.55rem 0.85rem;
        }
        .mobile-tab-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.8rem;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-family: inherit;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
        .mobile-tab-pill.active {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(79, 70, 229, 0.25);
        }
        .kids-mode .mobile-tab-pill {
            border: 2px solid #e9d5ff;
            background: #ffffff;
            color: #6b21a8;
            font-weight: 800;
            font-size: 0.82rem;
        }
        .kids-mode .mobile-tab-pill.active {
            background: linear-gradient(135deg, #7c3aed, #db2777);
            border-color: #ffffff;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.35);
        }
        .mobile-sidebar-backdrop {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(2px);
            z-index: 190;
        }
        .custom-sidebar, .kids-mode .custom-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: min(290px, 85vw);
            height: 100vh;
            height: 100dvh;
            max-height: 100dvh;
            z-index: 200;
            padding: 1.25rem 1rem;
            border-right: 1px solid #e2e8f0;
            border-bottom: none;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
            transform: translateX(-105%);
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .custom-sidebar.mobile-open, .kids-mode .custom-sidebar.mobile-open {
            transform: translateX(0);
        }
        .sidebar-close-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-size: 0.9rem;
            font-weight: 800;
            cursor: pointer;
            flex-shrink: 0;
        }
        .sidebar-scrollable-content {
            width: 100%;
            flex-direction: column;
        }
        .custom-workspace {
            height: auto;
            max-height: none;
            overflow-y: visible;
        }
        .custom-topbar { padding: 1rem 1.25rem; }
        .workspace-content { padding: 1.25rem; gap: 1.25rem; }
        .kids-mode .workspace-content { padding: 0; }
        .kids-mode .workspace-content.kids-tab-space { padding: 1.5rem 1.25rem 2.5rem; }
        .topbar-title h1 { font-size: 1.3rem; }
    }

    @media (max-width: 768px) {
        .custom-table thead { display: none; }
        .custom-table, .custom-table tbody { display: block; width: 100%; }
        .custom-table tr {
            display: block;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.9rem;
            padding: 0.85rem 1rem;
            margin-bottom: 0.75rem;
        }
        .custom-table tr:last-child { margin-bottom: 0; }
        .custom-table td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.45rem 0;
            border-bottom: 1px dashed #e2e8f0;
            text-align: right;
        }
        .custom-table td:last-child { border-bottom: none; padding-bottom: 0; }
        .custom-table td:first-child { padding-top: 0; }
        .custom-table td::before {
            content: attr(data-label);
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            text-align: left;
            flex-shrink: 0;
        }
    }

    @media (max-width: 640px) {
        .custom-topbar { padding: 0.9rem 1rem; gap: 0.65rem; }
        .topbar-title { width: 100%; }
        .topbar-title h1 { font-size: 1.15rem; }
        .topbar-title p { font-size: 0.8rem; }
        .topbar-actions { width: 100%; justify-content: space-between; }
        .active-context-badge {
            max-width: calc(100% - 3.5rem);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .workspace-content { padding: 1rem; gap: 1rem; }
        .kids-mode .workspace-content.kids-tab-space { padding: 1rem 0.85rem 2rem; }
        .glass-card, .kids-mode .glass-card { padding: 1rem; border-radius: 1rem; }
        .glass-card-title { font-size: 1rem; margin-bottom: 1rem; padding-bottom: 0.65rem; }
        .kids-mode .glass-card-title { font-size: 1.2rem; }
        .classes-grid, .kids-mode .classes-grid { grid-template-columns: 1fr; gap: 1rem; }
        .class-card, .kids-mode .class-card { padding: 1.15rem; border-radius: 1.15rem; }
        .lesson-class-group, .assignment-class-group, .quiz-class-group,
        .kids-mode .lesson-class-group, .kids-mode .assignment-class-group, .kids-mode .quiz-class-group {
            padding: 1rem;
            border-radius: 1rem;
            margin-bottom: 1rem;
        }
        .lesson-item, .assignment-item, .quiz-item,
        .kids-mode .lesson-item, .kids-mode .assignment-item, .kids-mode .quiz-item {
            padding: 0.9rem;
            gap: 0.75rem;
        }
        .lesson-icon, .assignment-icon, .quiz-icon,
        .kids-mode .lesson-icon, .kids-mode .assignment-icon, .kids-mode .quiz-icon {
            width: 2.5rem;
            height: 2.5rem;
            font-size: 1.25rem;
            border-radius: 0.75rem;
        }
        .lesson-title, .assignment-title, .quiz-title,
        .kids-mode .lesson-title, .kids-mode .assignment-title, .kids-mode .quiz-title {
            font-size: 0.98rem;
        }
        .lesson-filter-bar { gap: 0.4rem; }
        .lesson-filter-pill, .kids-mode .lesson-filter-pill {
            padding: 0.38rem 0.75rem;
            font-size: 0.76rem;
        }
        .grades-summary-grid { grid-template-columns: 1fr; gap: 0.75rem; }
        .grades-stat-card, .kids-mode .grades-stat-card { padding: 1rem; gap: 0.75rem; }
        .grades-stat-value, .kids-mode .grades-stat-value { font-size: 1.35rem; }
        .kids-grade-card { padding: 1rem; gap: 0.75rem; }
        .kids-grade-score { margin-left: 0; width: 100%; justify-content: space-between; display: flex; align-items: center; }
        .profile-card { padding: 1.15rem; border-radius: 1rem; }
        .profile-avatar-row { flex-direction: column; align-items: flex-start; gap: 1rem; }
        .profile-save-btn { width: 100%; justify-content: center; }
    }

    @media (max-width: 480px) {
        .lesson-open-btn, .assignment-open-btn, .quiz-open-btn,
        .kids-mode .lesson-open-btn, .kids-mode .assignment-open-btn, .kids-mode .quiz-open-btn {
            width: 100%;
            justify-content: center;
            margin-left: 0;
        }
    }

    /* ── Tab switch loading feedback (kids / junior / senior) ── */
    @keyframes student-tab-spin {
        to { transform: rotate(360deg); }
    }
    @keyframes student-tab-progress-slide {
        0% { left: -45%; }
        100% { left: 100%; }
    }

    .student-tab-progress {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        z-index: 10000;
        overflow: hidden;
        pointer-events: none;
    }
    .student-tab-progress::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: -45%;
        width: 45%;
        border-radius: 999px;
        background: linear-gradient(90deg, #6366f1, #a855f7, #ec4899);
        animation: student-tab-progress-slide 1s ease-in-out infinite;
    }

    .student-tab-loading-pill {
        position: fixed;
        left: 50%;
        bottom: 1.5rem;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.55rem 1rem;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid #e0e7ff;
        border-radius: 999px;
        color: #4338ca;
        font-size: 0.82rem;
        font-weight: 800;
        box-shadow: 0 12px 30px -12px rgba(79, 70, 229, 0.5);
        z-index: 10000;
        pointer-events: none;
        backdrop-filter: blur(6px);
    }
    .kids-mode .student-tab-loading-pill {
        color: #7c3aed;
        border-color: #e9d5ff;
        box-shadow: 0 12px 30px -12px rgba(168, 85, 247, 0.55);
    }

    .student-tab-spinner {
        display: inline-block;
        width: 15px;
        height: 15px;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: student-tab-spin 0.65s linear infinite;
        flex-shrink: 0;
    }

    /* Show a spinner on the exact tab/button that was clicked */
    .nav-link[data-loading="true"]::after,
    .mobile-tab-pill[data-loading="true"]::after,
    .class-action-btn[data-loading="true"]::after {
        content: '';
        display: inline-block;
        width: 13px;
        height: 13px;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: student-tab-spin 0.65s linear infinite;
        flex-shrink: 0;
        opacity: 0.85;
    }
    .nav-link[data-loading="true"]::after {
        margin-left: auto;
    }
    .mobile-tab-pill[data-loading="true"]::after {
        width: 10px;
        height: 10px;
        border-width: 1.5px;
        margin-left: 0.1rem;
    }
    .class-action-btn {
        position: relative;
    }
    .class-action-btn[data-loading="true"] {
        pointer-events: none;
        opacity: 0.8;
    }
    .class-action-btn[data-loading="true"]::after {
        position: absolute;
        top: 0.4rem;
        right: 0.4rem;
        width: 11px;
        height: 11px;
        display: block;
    }

    /* Prevent double-clicks while a tab request is in flight */
    .nav-menu.tabs-busy .nav-link,
    .mobile-tab-strip.tabs-busy .mobile-tab-pill {
        pointer-events: none;
        opacity: 0.72;
    }

    /* Gently dim the outgoing tab content so it is obvious a new tab is loading */
    .workspace-content {
        transition: opacity 0.15s ease;
    }
    .workspace-content.tab-content-loading {
        opacity: 0.6;
        pointer-events: none;
    }

    @media (prefers-reduced-motion: reduce) {
        .student-tab-progress::before,
        .student-tab-spinner,
        .nav-link[data-loading="true"]::after,
        .mobile-tab-pill[data-loading="true"]::after,
        .class-action-btn[data-loading="true"]::after {
            animation-duration: 2.4s;
        }
    }

    /* ── Notifications are styled in Livewire NotificationPanel and NotificationsView components ── */
    </style>

    <div class="custom-app-container {{ $tier === 'kids' ? 'kids-mode' : '' }}">

        {{-- Tab-switch loading feedback (shared by kids, junior and senior dashboards) --}}
        <div class="student-tab-progress"
             wire:loading.delay.shortest
             wire:target="setTab,openTab,openTabFromEvent"
             aria-hidden="true"></div>
        <div class="student-tab-loading-pill"
             wire:loading.delay.short
             wire:target="setTab,openTab,openTabFromEvent"
             role="status"
             aria-live="polite">
            <span class="student-tab-spinner" aria-hidden="true"></span>
            <span>Loading…</span>
        </div>

        <!-- ── MOBILE TOP NAVIGATION BAR & QUICK TAB STRIP (<= 860px) ── -->
        <div class="mobile-nav-header">
            <div class="mobile-brand">
                <img src="{{ \App\Services\PlatformSettings::logoUrl() }}" alt="{{ \App\Services\PlatformSettings::name() }}" class="mobile-brand-logo">
                <div class="mobile-brand-text">
                    <div class="mobile-brand-title">Student Portal</div>
                    <div class="mobile-brand-ctx">{{ $activeSchoolName }} · {{ $activeGradeName }}</div>
                </div>
            </div>
            <button type="button" class="mobile-menu-btn" @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Toggle navigation menu">
                <span>☰</span>
                <span>Menu</span>
            </button>
        </div>

        <nav class="mobile-tab-strip"
             aria-label="Quick navigation"
             wire:loading.class="tabs-busy"
             wire:target="setTab,openTab,openTabFromEvent">
            <button wire:click="setTab('dashboard')" type="button" class="mobile-tab-pill {{ $activeTab === 'dashboard' ? 'active' : '' }}">
                <span>📊</span><span>Dashboard</span>
            </button>
            <button wire:click="setTab('classes')" type="button" class="mobile-tab-pill {{ $activeTab === 'classes' ? 'active' : '' }}">
                <span>📖</span><span>Classes</span>
            </button>
            <button wire:click="setTab('lessons')" type="button" class="mobile-tab-pill {{ $activeTab === 'lessons' ? 'active' : '' }}">
                <span>📚</span><span>Lessons</span>
            </button>
            <button wire:click="setTab('assignments')" type="button" class="mobile-tab-pill {{ $activeTab === 'assignments' ? 'active' : '' }}">
                <span>📋</span><span>Assignments</span>
            </button>
            <button wire:click="setTab('quizzes')" type="button" class="mobile-tab-pill {{ $activeTab === 'quizzes' ? 'active' : '' }}">
                <span>🧠</span><span>Quizzes</span>
            </button>
            <button wire:click="setTab('grades')" type="button" class="mobile-tab-pill {{ $activeTab === 'grades' ? 'active' : '' }}">
                <span>🏆</span><span>Grades</span>
            </button>
            <button wire:click="setTab('calendar')" type="button" class="mobile-tab-pill {{ $activeTab === 'calendar' ? 'active' : '' }}">
                <span>📅</span><span>Calendar</span>
            </button>
            <button wire:click="setTab('notifications')" type="button" class="mobile-tab-pill {{ $activeTab === 'notifications' ? 'active' : '' }}">
                <span>🔔</span><span>Alerts</span>
            </button>
            <button wire:click="setTab('profile')" type="button" class="mobile-tab-pill {{ $activeTab === 'profile' ? 'active' : '' }}">
                <span>👤</span><span>Profile</span>
            </button>
        </nav>

        <div class="mobile-sidebar-backdrop" x-show="mobileMenuOpen" x-cloak @click="mobileMenuOpen = false"></div>
        
        <!-- ── CUSTOM LIGHT SIDEBAR ── -->
        <aside class="custom-sidebar" :class="{ 'mobile-open': mobileMenuOpen }">
            <div class="sidebar-scrollable-content">
                <!-- Brand -->
                <div class="brand-header">
                    <div class="brand-icon">
                        <img src="{{ \App\Services\PlatformSettings::logoUrl() }}" alt="{{ \App\Services\PlatformSettings::name() }}" style="width:100%; height:100%; object-fit:cover; border-radius:inherit;">
                    </div>
                    <div>
                        <div class="brand-title">Student Portal</div>
                        <div class="brand-sub">Academic Workspace</div>
                    </div>
                    <button type="button" class="sidebar-close-btn" @click="mobileMenuOpen = false" aria-label="Close menu">✕</button>
                </div>

                <!-- Navigation Links -->
                <div class="nav-group-label">Navigation</div>
                <nav class="nav-menu"
                     wire:loading.class="tabs-busy"
                     wire:target="setTab,openTab,openTabFromEvent">
                    <button wire:click="setTab('dashboard')" @click="mobileMenuOpen = false" type="button" class="nav-link {{ $activeTab === 'dashboard' ? 'active' : '' }}">
                        <span class="nav-icon">📊</span>
                        <span>Dashboard</span>
                    </button>
                    <button wire:click="setTab('classes')" @click="mobileMenuOpen = false" type="button" class="nav-link {{ $activeTab === 'classes' ? 'active' : '' }}">
                        <span class="nav-icon">📖</span>
                        <span>My Classes</span>
                    </button>
                    <button wire:click="setTab('lessons')" @click="mobileMenuOpen = false" type="button" class="nav-link {{ $activeTab === 'lessons' ? 'active' : '' }}">
                        <span class="nav-icon">📚</span>
                        <span>My Lessons</span>
                    </button>
                    <button wire:click="setTab('assignments')" @click="mobileMenuOpen = false" type="button" class="nav-link {{ $activeTab === 'assignments' ? 'active' : '' }}">
                        <span class="nav-icon">📋</span>
                        <span>Assignments</span>
                    </button>
                    <button wire:click="setTab('quizzes')" @click="mobileMenuOpen = false" type="button" class="nav-link {{ $activeTab === 'quizzes' ? 'active' : '' }}">
                        <span class="nav-icon">🧠</span>
                        <span>Quizzes</span>
                    </button>
                    <button wire:click="setTab('grades')" @click="mobileMenuOpen = false" type="button" class="nav-link {{ $activeTab === 'grades' ? 'active' : '' }}">
                        <span class="nav-icon">🏆</span>
                        <span>Grades</span>
                    </button>
                    <button wire:click="setTab('calendar')" @click="mobileMenuOpen = false" type="button" class="nav-link {{ $activeTab === 'calendar' ? 'active' : '' }}">
                        <span class="nav-icon">📅</span>
                        <span>Calendar</span>
                    </button>
                    <button wire:click="setTab('notifications')" @click="mobileMenuOpen = false" type="button" class="nav-link {{ $activeTab === 'notifications' ? 'active' : '' }}">
                        <span class="nav-icon">🔔</span>
                        <span>Notifications</span>
                    </button>
                    <button wire:click="setTab('profile')" @click="mobileMenuOpen = false" type="button" class="nav-link {{ $activeTab === 'profile' ? 'active' : '' }}">
                        <span class="nav-icon">👤</span>
                        <span>My Profile</span>
                    </button>
                </nav>

                <!-- Active Enrolled Schools -->
                <div class="nav-group-label">Enrolled Schools</div>
                <div class="ctx-list">
                    @if($allContexts)
                        @foreach($allContexts as $schoolGroup)
                            <div class="ctx-school-group">
                                <div class="ctx-school-name">🏫 {{ Str::limit($schoolGroup['school']->name, 20) }}</div>
                                <div class="ctx-grades">
                                    @foreach($schoolGroup['contexts'] as $ctx)
                                        @php $isActive = $activeContext && $activeContext['key'] === $ctx['key']; @endphp
                                        <div wire:click="switchContext('{{ $ctx['key'] }}')" @click="mobileMenuOpen = false" class="ctx-item {{ $isActive ? 'active' : '' }}">
                                            <span>{{ $ctx['grade']->name ?? '' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- User Card & Sign Out at Bottom Left -->
            <div class="sidebar-user-card">
                <div class="user-profile-row">
                    @if($student?->profile_photo_url)
                        <img src="{{ $student->profile_photo_url }}" alt="" class="user-avatar-img">
                    @else
                        <div class="user-avatar">
                            {{ strtoupper(substr($firstName, 0, 1)) }}
                        </div>
                    @endif
                    <div class="user-info">
                        <div class="user-name">{{ $profileName ?: 'Student' }}</div>
                        <div class="user-role">{{ $activeGradeName }} Student</div>
                    </div>
                </div>
                <button wire:click="logout" type="button" class="btn-sidebar-signout" title="Sign Out">
                    Sign Out
                </button>
            </div>
        </aside>

        <!-- ── CUSTOM MAIN LIGHT WORKSPACE ── -->
        <main class="custom-workspace">
            
            <!-- Topbar -->
            <header class="custom-topbar {{ $tier !== 'kids' && $activeTab === 'dashboard' ? 'self-contained-hide' : '' }}">
                <div class="topbar-title">
                    @if($activeTab === 'classes')
                        <h1>My Classes & Learning Modules 📖</h1>
                        <p>Browse your enrolled subjects, teachers, and course materials.</p>
                    @elseif($activeTab === 'lessons')
                        <h1>My Lessons & Materials 📚</h1>
                        <p>Browse reading materials, videos, and downloads for your classes.</p>
                    @elseif($activeTab === 'assignments')
                        <h1>Assignments & Submissions 📋</h1>
                        <p>Track your homework deadlines, view scores, and submit completed tasks.</p>
                    @elseif($activeTab === 'quizzes')
                        <h1>Quizzes & Exam Practice 🧠</h1>
                        <p>Review quiz attempts, test results, and test your subject knowledge.</p>
                    @elseif($activeTab === 'grades')
                        <h1>Grades & Report Card 🏆</h1>
                        <p>Comprehensive summary of your academic progress and subject performance.</p>
                    @elseif($activeTab === 'calendar')
                        @if($tier === 'junior')
                            <h1>Academic Calendar & Planner 📅</h1>
                            <p>Track assignments, test deadlines, and organize your study schedule.</p>
                        @elseif($tier === 'senior')
                            <h1>Academic Schedule & Deadlines 📅</h1>
                            <p>Comprehensive agenda, course evaluations, and milestone timeline.</p>
                        @else
                            <h1>Calendar & Homework 📅</h1>
                            <p>Track your assignments, quizzes, and deadlines at a glance.</p>
                        @endif
                    @elseif($activeTab === 'notifications')
                        <h1>Notifications 🔔</h1>
                        <p>Stay up to date with your classes, assignments, and quizzes.</p>
                    @else
                        <h1>Welcome back, {{ $firstName }}! 👋</h1>
                        <p>{{ now()->format('l, F j, Y') }} · Have a great study session today!</p>
                    @endif
                </div>

                <div class="topbar-actions">
                    <div class="active-context-badge">
                        📍 {{ $activeSchoolName }} › {{ $activeGradeName }}
                    </div>
                    <livewire:notification-panel :tier="$tier" />
                </div>
            </header>

            <!-- Workspace Main Content by Selected Tab -->
            <div class="workspace-content {{ $tier === 'kids' && $activeTab !== 'dashboard' ? 'kids-tab-space' : '' }} {{ $tier !== 'kids' && $activeTab === 'dashboard' ? 'self-contained-content' : '' }}"
                 wire:loading.class="tab-content-loading"
                 wire:target="setTab,openTab,openTabFromEvent">
                
                {{-- ── TAB 1: DASHBOARD OVERVIEW (BY AGE CATEGORY) ── --}}
                @if($activeTab === 'dashboard')
                    @if($tier === 'kids')
                        {{-- Age 5 to 10: Kids Dashboard --}}
                        @include('filament.student.pages.kids-dashboard', [
                            'student'       => $student,
                            'firstName'     => $firstName,
                            'activeContext' => $activeContext,
                            'allContexts'   => $allContexts,
                        ])
                    @elseif($tier === 'junior')
                        {{-- Age 11 to 15: Teens Dashboard --}}
                        @include('filament.student.pages.junior-dashboard', [
                            'student'       => $student,
                            'firstName'     => $firstName,
                            'activeContext' => $activeContext,
                            'allContexts'   => $allContexts,
                        ])
                    @else
                        {{-- Age 16+: Seniors Dashboard --}}
                        @include('filament.student.pages.senior-dashboard', [
                            'student'       => $student,
                            'firstName'     => $firstName,
                            'activeContext' => $activeContext,
                            'allContexts'   => $allContexts,
                        ])
                    @endif
                
                {{-- ── TAB 2: MY CLASSES (all schools, grouped) ── --}}
                @elseif($activeTab === 'classes')
                    <div class="glass-card">
                        <div class="glass-card-title">
                            <span>📖 All My Classes</span>
                            <span style="font-size: 0.85rem; color: #4f46e5; font-weight: 700;">{{ $allClasses->count() }} Active Courses · {{ $allClassGroups->count() }} Schools</span>
                        </div>

                        @if($allClasses->isEmpty())
                            <div style="color: #64748b; font-size: 0.95rem; text-align: center; padding: 3rem;">
                                You are not enrolled in any classes yet. Ask your teacher to add you!
                            </div>
                        @else
                            @php
                                $kidsClassIdx = 0;
                                $kidsCardColors = ['kidc-red', 'kidc-blue', 'kidc-purple', 'kidc-green', 'kidc-orange', 'kidc-pink'];
                                $kidsCardIcons  = ['🔢', '📖', '🎨', '🔬', '🌍', '🎵', '🏃', '✏️', '🖥️', '⚽'];
                            @endphp
                            @foreach($allClassGroups as $schoolGroup)
                                <div class="school-class-block">
                                    <div class="school-class-header">
                                        🏫 {{ $schoolGroup['school']->name ?? 'School' }}
                                        <span class="school-class-count">{{ $schoolGroup['groups']->sum(fn ($g) => $g['classes']->count()) }} classes</span>
                                    </div>

                                    @foreach($schoolGroup['groups'] as $gradeGroup)
                                        <div class="grade-class-block">
                                            <div class="grade-class-header">📗 {{ $gradeGroup['grade']->name ?? 'Class Group' }}</div>
                                            <div class="classes-grid">
                                                @foreach($gradeGroup['classes'] as $class)
                                                    @php
                                                        $lessonCount  = ($lessonsByClassId[$class->id] ?? collect())->count();
                                                        $assignCount  = ($assignmentsByClassId[$class->id] ?? collect())->count();
                                                        $quizCount    = ($quizzesByClassId[$class->id] ?? collect())->count();
                                                        $teacherName  = $class->teachers->first()?->user?->name ?? 'Course Instructor';
                                                        $kidsClassIdx++;
                                                        $kidCardColor = $kidsCardColors[($kidsClassIdx - 1) % 6];
                                                        $kidCardIcon  = $class->emoji ?: $kidsCardIcons[($kidsClassIdx - 1) % count($kidsCardIcons)];
                                                    @endphp
                                                    <div class="class-card {{ $tier === 'kids' ? 'kids-class-card ' . $kidCardColor : '' }}">
                                                        <div style="display:flex; align-items:center; justify-content:space-between;">
                                                            <div class="class-card-icon">{{ $tier === 'kids' ? $kidCardIcon : '📘' }}</div>
                                                            <span class="status-pill status-ok">Active</span>
                                                        </div>
                                                        <div>
                                                            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 0.35rem 0;">{{ $class->name }}</h3>
                                                            <p style="font-size: 0.85rem; color: #64748b; margin: 0; font-weight: 600; line-height: 1.4;">👨‍🏫 Instructor: {{ $teacherName }}</p>
                                                        </div>
                                                        <div class="class-stats">
                                                            <span>📚 {{ $lessonCount }} Lessons</span>
                                                            <span>📋 {{ $assignCount }} Assignments</span>
                                                            <span>🧠 {{ $quizCount }} Quizzes</span>
                                                        </div>
                                                        <div class="class-actions">
                                                            @if($lessonCount > 0)
                                                                <button wire:click="openTab('lessons', {{ $class->id }})" type="button" class="class-action-btn class-action-lesson">
                                                                    <span class="class-action-emoji">📖</span>
                                                                    <span class="class-action-label">Lessons</span>
                                                                </button>
                                                            @else
                                                                <span class="class-action-none">
                                                                    <span class="class-action-emoji">📖</span>
                                                                    <span class="class-action-label">No lessons yet</span>
                                                                </span>
                                                            @endif
                                                            @if($assignCount > 0)
                                                                <button wire:click="openTab('assignments', {{ $class->id }})" type="button" class="class-action-btn class-action-assign">
                                                                    <span class="class-action-emoji">📋</span>
                                                                    <span class="class-action-label">Assignments</span>
                                                                </button>
                                                            @else
                                                                <span class="class-action-none">
                                                                    <span class="class-action-emoji">📋</span>
                                                                    <span class="class-action-label">No assignments</span>
                                                                </span>
                                                            @endif
                                                            @if($quizCount > 0)
                                                                <button wire:click="openTab('quizzes', {{ $class->id }})" type="button" class="class-action-btn class-action-quiz">
                                                                    <span class="class-action-emoji">🧠</span>
                                                                    <span class="class-action-label">Quizzes</span>
                                                                </button>
                                                            @else
                                                                <span class="class-action-none">
                                                                    <span class="class-action-emoji">🧠</span>
                                                                    <span class="class-action-label">No quizzes</span>
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
                    </div>

                {{-- ── TAB 3: LESSONS (grouped by school › grade › class) ── --}}
                @elseif($activeTab === 'lessons')
                    <div class="glass-card {{ $tier === 'kids' ? 'kids-lessons-panel' : '' }}">
                        <div class="glass-card-title">
                            <span>📚 My Lessons & Materials</span>
                            <span style="font-size: 0.85rem; color: #4f46e5; font-weight: 700;">{{ $allLessons->count() }} Lesson(s)</span>
                        </div>

                        @php
                            $kidsLessonIdx = 0;
                            $kidsLessonColors = ['kidc-red', 'kidc-blue', 'kidc-purple', 'kidc-green', 'kidc-orange', 'kidc-pink'];
                            $kidsLessonTotal = 0;
                            foreach ($allClassGroups as $sg2) {
                                foreach ($sg2['groups'] as $g2) {
                                    foreach ($g2['classes'] as $c2) {
                                        $kidsLessonTotal += ($lessonsByClassId[$c2->id] ?? collect())->count();
                                    }
                                }
                            }
                        @endphp

                        @if($allLessons->isNotEmpty() && ($allClasses->count() > 1 || $filteredClass))
                            <div class="asg-filter-bar">
                                <button
                                    wire:click="setTab('lessons')"
                                    type="button"
                                    class="asg-filter-chip {{ $classFilterId === null ? 'asg-filter-chip-active' : '' }}">
                                    🌍 All
                                    <span class="asg-filter-chip-count">{{ $kidsLessonTotal }}</span>
                                </button>

                                @foreach($allClassGroups as $schoolGroup)
                                    @foreach($schoolGroup['groups'] as $gradeGroup)
                                        @foreach($gradeGroup['classes'] as $class)
                                            @if($classHasLessons[$class->id] ?? false)
                                                <button
                                                    wire:click="openTab('lessons', {{ (int) $class->id }})"
                                                    type="button"
                                                    class="asg-filter-chip {{ (int) $class->id === $classFilterId ? 'asg-filter-chip-active' : '' }}">
                                                    📘 {{ $class->name }}
                                                    <span class="asg-filter-chip-count">{{ $lessonsByClassId[$class->id]->count() }}</span>
                                                </button>
                                            @endif
                                        @endforeach
                                    @endforeach
                                @endforeach

                                @if($filteredClass)
                                    <span class="asg-filter-active-note">📍 {{ $filteredClass->name }}</span>
                                @endif
                            </div>
                        @endif

                        @if($allLessons->isEmpty())
                            <div class="tab-filter-empty">
                                @if($filteredClass)
                                    No lessons published yet for <strong>{{ $filteredClass->name }}</strong>. Check back soon!
                                @else
                                    📚 No lessons published yet. Check back soon — your teacher will add study materials here!
                                @endif
                            </div>
                        @else
                            @foreach($allClassGroups as $schoolGroup)
                                @php
                                    $schoolLessonTotal = 0;
                                    foreach ($schoolGroup['groups'] as $sg) {
                                        foreach ($sg['classes'] as $sc) {
                                            if ($classHasLessons[$sc->id] ?? false) {
                                                $schoolLessonTotal += ($lessonsByClassId[$sc->id] ?? collect())->count();
                                            }
                                        }
                                    }
                                @endphp
                                @if($schoolLessonTotal > 0)
                                    <div class="school-class-block">
                                        <div class="school-class-header">
                                            🏫 {{ $schoolGroup['school']->name ?? 'School' }}
                                            <span class="school-class-count">{{ $schoolLessonTotal }} lesson(s)</span>
                                        </div>
                                        @foreach($schoolGroup['groups'] as $gradeGroup)
                                            @php
                                                $gradeLessonTotal = 0;
                                                foreach ($gradeGroup['classes'] as $gc) {
                                                    if ($classHasLessons[$gc->id] ?? false) {
                                                        $gradeLessonTotal += ($lessonsByClassId[$gc->id] ?? collect())->count();
                                                    }
                                                }
                                            @endphp
                                            @if($gradeLessonTotal > 0)
                                                <div class="grade-class-block">
                                                    <div class="grade-class-header">📗 {{ $gradeGroup['grade']->name ?? 'Class Group' }}</div>
                                                    @foreach($gradeGroup['classes'] as $class)
                                                        @if($classHasLessons[$class->id] ?? false)
                                                            @php $kidsLessonIdx++; @endphp
                                                            <div class="lesson-class-group {{ $tier === 'kids' ? 'kids-lesson-group ' . $kidsLessonColors[($kidsLessonIdx - 1) % 6] : '' }}">
                                                                <div class="lesson-class-head">
                                                                    <span class="lesson-class-title">📘 {{ $class->name }}</span>
                                                                    <span class="lesson-class-meta">
                                                                        {{ $lessonsByClassId[$class->id]->count() }} lesson(s)
                                                                    </span>
                                                                </div>
                                                                <div class="lesson-list">
                                                                    @foreach($lessonsByClassId[$class->id] as $lesson)
                                                                        @php
                                                                            $lessonTeacherName = $lesson->teacher?->user?->name ?? $class->teachers->first()?->user?->name ?? 'Course Teacher';
                                                                        @endphp
                                                                        <div class="lesson-item">
                                                                            <div class="lesson-item-top">
                                                                                <span class="lesson-class-badge">📖 Lesson {{ $loop->iteration }}</span>
                                                                                <span class="lesson-date">{{ $lesson->created_at ? $lesson->created_at->format('M j, Y') : '' }}</span>
                                                                            </div>

                                                                            <h3>{{ $lesson->title }}</h3>

                                                                            <p class="lesson-desc">👨🏫 {{ $lessonTeacherName }}</p>

                                                                            @if($lesson->description)
                                                                                <p class="lesson-desc">{{ \Illuminate\Support\Str::limit(strip_tags($lesson->description), 180) }}</p>
                                                                            @endif

                                                                            <a href="{{ \App\Filament\Student\Pages\LessonView::getUrl(['lesson' => $lesson->id]) }}" class="lesson-open-btn">
                                                                                Read Lesson 📖
                                                                            </a>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                {{-- ── TAB 4: ASSIGNMENTS (grouped by school › grade › class) ── --}}
                @elseif($activeTab === 'assignments')
                    <div class="glass-card {{ $tier === 'kids' ? 'kids-assignments-panel' : '' }}">
                        <div class="glass-card-title">
                            <span>📋 Homework & Assignments List</span>
                            <span style="font-size: 0.85rem; color: #dc2626; font-weight: 700;">{{ $filteredAssignments->count() }} Task(s)</span>
                        </div>

                        @if($allAssignments->isNotEmpty())
                            <div class="asg-filter-bar">
                                <button
                                    wire:click="setTab('assignments')"
                                    type="button"
                                    class="asg-filter-chip {{ $classFilterId === null ? 'asg-filter-chip-active' : '' }}">
                                    🌍 All
                                    <span class="asg-filter-chip-count">{{ $allAssignments->count() }}</span>
                                </button>

                                @foreach($allClassGroups as $schoolGroup)
                                    @foreach($schoolGroup['groups'] as $gradeGroup)
                                        @foreach($gradeGroup['classes'] as $class)
                                            @if(($assignmentsByClassId[$class->id] ?? collect())->isNotEmpty())
                                                <button
                                                    wire:click="openTab('assignments', {{ (int) $class->id }})"
                                                    type="button"
                                                    class="asg-filter-chip {{ (int) $class->id === $classFilterId ? 'asg-filter-chip-active' : '' }}">
                                                    📘 {{ $class->name }}
                                                    <span class="asg-filter-chip-count">{{ $assignmentsByClassId[$class->id]->count() }}</span>
                                                </button>
                                            @endif
                                        @endforeach
                                    @endforeach
                                @endforeach

                                @if($filteredClass)
                                    <span class="asg-filter-active-note">📍 {{ $filteredClass->name }}</span>
                                @endif
                            </div>

                            <div class="asg-legend">
                                <span class="asg-legend-title">🎨</span>
                                <span class="asg-legend-item"><i class="asg-legend-dot asg-legend-graded"></i> Graded</span>
                                <span class="asg-legend-item"><i class="asg-legend-dot asg-legend-submitted"></i> Submitted</span>
                                <span class="asg-legend-item"><i class="asg-legend-dot asg-legend-returned"></i> Returned</span>
                                <span class="asg-legend-item"><i class="asg-legend-dot asg-legend-pending"></i> Pending</span>
                                <span class="asg-legend-item"><i class="asg-legend-dot asg-legend-notstarted"></i> Not started</span>
                                <span class="asg-legend-item"><i class="asg-legend-dot asg-legend-late"></i> Late</span>
                                <span class="asg-legend-item"><i class="asg-legend-dot asg-legend-closed"></i> Missed</span>
                            </div>
                        @endif

                        @if($filteredAssignments->isEmpty())
                            <div class="tab-filter-empty">
                                @if($filteredClass)
                                    🎉 No assignments in <strong>{{ $filteredClass->name }}</strong> right now!
                                @else
                                    🎉 You currently have no assignments assigned!
                                @endif
                            </div>
                        @else
                            @php $kidsAssignIdx = 0; $kidsAssignColors = ['kidc-red', 'kidc-blue', 'kidc-purple', 'kidc-green', 'kidc-orange', 'kidc-pink']; @endphp
                            @foreach($allClassGroups as $schoolGroup)
                                @php
                                    $schoolAssignTotal = 0;
                                    foreach ($schoolGroup['groups'] as $sg) {
                                        foreach ($sg['classes'] as $sc) {
                                            if ($classHasAssignments[$sc->id] ?? false) {
                                                $schoolAssignTotal += ($assignmentsByClassId[$sc->id] ?? collect())->count();
                                            }
                                        }
                                    }
                                @endphp
                                @if($schoolAssignTotal > 0)
                                    <div class="school-class-block">
                                        <div class="school-class-header">
                                            🏫 {{ $schoolGroup['school']->name ?? 'School' }}
                                            <span class="school-class-count">{{ $schoolAssignTotal }} task(s)</span>
                                        </div>
                                        @foreach($schoolGroup['groups'] as $gradeGroup)
                                            @php
                                                $gradeAssignTotal = 0;
                                                foreach ($gradeGroup['classes'] as $gc) {
                                                    if ($classHasAssignments[$gc->id] ?? false) {
                                                        $gradeAssignTotal += ($assignmentsByClassId[$gc->id] ?? collect())->count();
                                                    }
                                                }
                                            @endphp
                                            @if($gradeAssignTotal > 0)
                                                <div class="grade-class-block">
                                                    <div class="grade-class-header">📗 {{ $gradeGroup['grade']->name ?? 'Class Group' }}</div>
                                                    @foreach($gradeGroup['classes'] as $class)
                                                        @if($classHasAssignments[$class->id] ?? false)
                                                            @php $kidsAssignIdx++; @endphp
                                                            <div class="assignment-class-group {{ $tier === 'kids' ? 'kids-assignment-group ' . $kidsAssignColors[($kidsAssignIdx - 1) % 6] : '' }}">
                                                                <div class="assignment-class-head">
                                                                    <span class="assignment-class-title">📘 {{ $class->name }}</span>
                                                                    <span class="assignment-class-meta">{{ $assignmentsByClassId[$class->id]->count() }} task(s)</span>
                                                                </div>
                                                                <div class="assignment-list">
                                                                    @foreach($assignmentsByClassId[$class->id] as $assignment)
                                                                        @php
$submission = $studentSubmissionMap[$assignment->id] ?? null;
                                                                    $assignmentTeacher = $assignment->teacher?->user?->name ?? ($class->teachers->first()?->user?->name ?? 'Course Teacher');
                                                                    $assignmentDue = $assignment->end_at;
                                                                    $assignmentStatus = $submission && $submission->isGraded() && $submission->score !== null
                                                                        ? 'graded'
                                                                        : ($submission
                                                                            ? ($submission->status === 'returned' ? 'returned' : 'submitted')
                                                                            : ($assignment->isExpired()
                                                                                ? ($assignment->acceptsLateSubmissions() && $assignment->lateSubmissionDeadline()?->isFuture()
                                                                                    ? 'late'
                                                                                    : 'closed')
                                                                                : ($assignment->isAvailable()
                                                                                    ? 'pending'
                                                                                    : 'not_started')));
                                                                    $assignmentStatusLabel = match ($assignmentStatus) {
                                                                        'graded' => '✅ Graded · ' . round($submission->percentage() ?: 0) . '%',
                                                                        'returned' => '↩️ Returned · Resubmit',
                                                                        'submitted' => '⏳ Submitted · Awaiting grade',
                                                                        'late' => '⚠️ Late — submit now',
                                                                        'closed' => '⛔ Missed deadline',
                                                                        'not_started' => '🔒 Starts ' . ($assignment->start_at ? $assignment->start_at->format('M j, g:i A') : 'Soon'),
                                                                        default => '📝 Pending — to do',
                                                                    };
                                                                    $assignmentStatusClass = 'asg-pill-' . $assignmentStatus;
                                                                    $assignmentCardClass = 'asg-' . $assignmentStatus;
                                                                    $assignmentBtnClass = 'asg-btn-' . $assignmentStatus;
                                                                    $assignmentBtnLabel = match ($assignmentStatus) {
                                                                        'graded' => 'View Grade ✅',
                                                                        'returned' => 'Resubmit Assignment ↩️',
                                                                        'submitted' => 'View Submission 📄',
                                                                        'late' => 'Submit Now ⚠️',
                                                                        'closed' => 'View Details ⛔',
                                                                        'not_started' => 'View Details 🔒',
                                                                        default => 'View & Submit Assignment 📝',
                                                                    };
                                                                        @endphp
<div class="assignment-item {{ $assignmentCardClass }}">
                                                            <div class="assignment-item-top">
                                                                <span class="assignment-status-pill {{ $assignmentStatusClass }}">
                                                                    {{ $assignmentStatusLabel }}
                                                                </span>
                                                                <span class="assignment-date">
                                                                    📅 {{ $assignmentDue ? $assignmentDue->format('M j, Y · g:i A') : 'No deadline' }}
                                                                </span>
                                                            </div>

                                                            <h3>{{ $assignment->title }}</h3>

                                                            <p class="assignment-desc">👨‍🏫 {{ $assignmentTeacher }}</p>

                                                            @if($assignment->description)
                                                                <p class="assignment-desc">{{ \Illuminate\Support\Str::limit(strip_tags($assignment->description), 180) }}</p>
                                                            @endif

                                                            <div class="assignment-meta-row">
                                                                <span class="assignment-meta-chip">🎯 {{ $assignment->max_score }} marks</span>
                                                                @if($assignmentStatus === 'graded')
                                                                    <span class="assignment-meta-chip">🏆 Score {{ $submission->score }} / {{ $assignment->max_score }} · {{ round($submission->percentage() ?: 0) }}%</span>
                                                                @endif
                                                                @if($assignmentStatus === 'submitted' && $submission && $submission->submitted_at)
                                                                    <span class="assignment-meta-chip">🕓 Submitted {{ $submission->submitted_at->format('M j · g:i A') }}</span>
                                                                @endif
                                                                @if($assignmentStatus === 'not_started' && $assignment->start_at)
                                                                    <span class="assignment-meta-chip">🔒 Opens {{ $assignment->start_at->format('M j · g:i A') }}</span>
                                                                @endif
                                                            </div>

                                                            <a href="{{ \App\Filament\Student\Pages\AssignmentView::getUrl(['assignment' => $assignment->id]) }}" class="assignment-open-btn {{ $assignmentBtnClass }}">
                                                                {{ $assignmentBtnLabel }}
                                                            </a>
                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                {{-- ── TAB 5: QUIZZES ── --}}
                @elseif($activeTab === 'quizzes')
                    <div class="glass-card {{ $tier === 'kids' ? 'kids-quizzes-panel' : ($tier === 'junior' ? 'junior-quizzes-panel' : 'senior-quizzes-panel') }}">
                        <div class="glass-card-title">
                            @if($tier === 'kids')
                                <span>🧠 Fun Quizzes 🌟</span>
                                <span style="font-size: 0.88rem; color: #db2777; font-weight: 800;">
                                    {{ $allQuizzes->count() }} Quiz Games
                                    @if($quizAttempts->count() > 0)
                                        · {{ $quizPassed }} Passed 🏆
                                    @endif
                                </span>
                            @elseif($tier === 'junior')
                                <span>🧠 Quizzes & Challenges</span>
                                <span style="font-size: 0.85rem; color: #4f46e5; font-weight: 700;">
                                    {{ $allQuizzes->count() }} Available
                                    @if($quizAttempts->count() > 0)
                                        · Avg {{ $quizAvgPct }}% · {{ $quizPassed }} Passed
                                    @endif
                                </span>
                            @else
                                <span>🎓 Academic Assessments & Quizzes</span>
                                <span style="font-size: 0.85rem; color: #3b82f6; font-weight: 700;">
                                    {{ $allQuizzes->count() }} Assessment(s)
                                    @if($quizAttempts->count() > 0)
                                        · Average: {{ $quizAvgPct }}%
                                    @endif
                                </span>
                            @endif
                        </div>

                        @php
                            $kidsQuizIdx = 0;
                        @endphp

                        @if($allQuizzes->isNotEmpty() && ($allClasses->count() > 1 || $filteredClass))
                            <div class="asg-filter-bar">
                                <button
                                    wire:click="setTab('quizzes')"
                                    type="button"
                                    class="asg-filter-chip {{ $classFilterId === null ? 'asg-filter-chip-active' : '' }}">
                                    🌍 All
                                    <span class="asg-filter-chip-count">{{ $allQuizzes->count() }}</span>
                                </button>

                                @foreach($allClassGroups as $schoolGroup)
                                    @foreach($schoolGroup['groups'] as $gradeGroup)
                                        @foreach($gradeGroup['classes'] as $class)
                                            @if($classHasQuizzes[$class->id] ?? false)
                                                <button
                                                    wire:click="openTab('quizzes', {{ (int) $class->id }})"
                                                    type="button"
                                                    class="asg-filter-chip {{ (int) $class->id === $classFilterId ? 'asg-filter-chip-active' : '' }}">
                                                    📘 {{ $class->name }}
                                                    <span class="asg-filter-chip-count">{{ $quizzesByClassId[$class->id]->count() }}</span>
                                                </button>
                                            @endif
                                        @endforeach
                                    @endforeach
                                @endforeach

                                @if($filteredClass)
                                    <span class="asg-filter-active-note">📍 {{ $filteredClass->name }}</span>
                                @endif
                            </div>
                        @endif

                        @if($allQuizzes->isEmpty())
                            <div class="tab-filter-empty">
                                @if($filteredClass)
                                    No quizzes published yet for <strong>{{ $filteredClass->name }}</strong>. Check back soon!
                                @else
                                    🧠 No quizzes published yet. Check back soon — your teacher will add new quizzes here!
                                @endif
                            </div>
                        @else
                            @foreach($allClassGroups as $schoolGroup)
                                @php
                                    $schoolQuizTotal = 0;
                                    foreach ($schoolGroup['groups'] as $sg) {
                                        foreach ($sg['classes'] as $sc) {
                                            if ($classHasQuizzes[$sc->id] ?? false) {
                                                $schoolQuizTotal += ($quizzesByClassId[$sc->id] ?? collect())->count();
                                            }
                                        }
                                    }
                                @endphp
                                @if($schoolQuizTotal > 0)
                                    <div class="school-class-block">
                                        <div class="school-class-header">
                                            🏫 {{ $schoolGroup['school']->name ?? 'School' }}
                                            <span class="school-class-count">{{ $schoolQuizTotal }} quiz(es)</span>
                                        </div>
                                        @foreach($schoolGroup['groups'] as $gradeGroup)
                                            @php
                                                $gradeQuizTotal = 0;
                                                foreach ($gradeGroup['classes'] as $gc) {
                                                    if ($classHasQuizzes[$gc->id] ?? false) {
                                                        $gradeQuizTotal += ($quizzesByClassId[$gc->id] ?? collect())->count();
                                                    }
                                                }
                                            @endphp
                                            @if($gradeQuizTotal > 0)
                                                <div class="grade-class-block">
                                                    <div class="grade-class-header">📗 {{ $gradeGroup['grade']->name ?? 'Class Group' }}</div>
                                                    @foreach($gradeGroup['classes'] as $class)
                                                        @if($classHasQuizzes[$class->id] ?? false)
                                                            @php $kidsQuizIdx++; @endphp
                                                            <div class="quiz-class-group {{ 'kidc-' . ['red','blue','purple','green','orange','pink'][($kidsQuizIdx - 1) % 6] }}">
                                                                <div class="quiz-class-head">
                                                                    <span class="quiz-class-title">{{ $tier === 'kids' ? '🧩' : ($tier === 'junior' ? '⚡' : '📚') }} {{ $class->name }}</span>
                                                                    <span class="quiz-class-meta">{{ $quizzesByClassId[$class->id]->count() }} {{ $tier === 'senior' ? 'assessment(s)' : 'quiz(es)' }}</span>
                                                                </div>
                                                                <div class="quiz-list">
                                                                    @foreach($quizzesByClassId[$class->id] as $quiz)
                                                                        @php
                                                                            $qStatus = $quizStatusById[$quiz->id] ?? ['attempts' => collect(), 'finished_count' => 0, 'latest' => null, 'best' => null, 'can_attempt' => false, 'remaining' => null];
                                                                            $qState = $quizStateById[$quiz->id] ?? 'closed';
                                                                            
                                                                            if ($tier === 'kids') {
                                                                                $qBtnLabel = match ($qState) {
                                                                                    'available' => '▶️ Play Quiz!',
                                                                                    'retry' => '🔁 Try Again!',
                                                                                    'retry_passed' => '🌟 Play Again!',
                                                                                    'complete' => '👀 View Score',
                                                                                    'locked' => '🔒 Opens Soon',
                                                                                    default => '⛔ Quiz Ended',
                                                                                };
                                                                                $qPillLabel = match ($qState) {
                                                                                    'available' => '▶️ Ready to Play',
                                                                                    'retry' => '💪 Almost There!',
                                                                                    'retry_passed' => '🌟 Super Star! Passed!',
                                                                                    'complete' => '🏆 Finished!',
                                                                                    'locked' => '🔒 Opens ' . ($quiz->start_at?->format('M j') ?? 'Soon'),
                                                                                    default => '⛔ This Quiz Ended',
                                                                                };
                                                                            } elseif ($tier === 'junior') {
                                                                                $qBtnLabel = match ($qState) {
                                                                                    'available' => '▶ Start Quiz',
                                                                                    'retry' => '↻ Try Again',
                                                                                    'retry_passed' => '↻ Retake Quiz',
                                                                                    'complete' => '✓ View Results',
                                                                                    'locked' => '🔒 Locked',
                                                                                    default => '⛔ Closed',
                                                                                };
                                                                                $qPillLabel = match ($qState) {
                                                                                    'available' => '▶ Ready to Take',
                                                                                    'retry' => '💪 Try Again',
                                                                                    'retry_passed' => '✅ Passed',
                                                                                    'complete' => '🏁 Completed',
                                                                                    'locked' => '🔒 Opens ' . ($quiz->start_at?->format('M j') ?? 'Soon'),
                                                                                    default => '⛔ Ended',
                                                                                };
                                                                            } else {
                                                                                $qBtnLabel = match ($qState) {
                                                                                    'available' => 'Begin Assessment',
                                                                                    'retry' => 'Retake Assessment',
                                                                                    'retry_passed' => 'Retake Assessment',
                                                                                    'complete' => 'Review Results',
                                                                                    'locked' => 'Assessment Locked',
                                                                                    default => 'Assessment Closed',
                                                                                };
                                                                                $qPillLabel = match ($qState) {
                                                                                    'available' => 'Available',
                                                                                    'retry' => 'Review Recommended',
                                                                                    'retry_passed' => 'Passed',
                                                                                    'complete' => 'Completed',
                                                                                    'locked' => 'Opens ' . ($quiz->start_at?->format('M j, Y') ?? 'Soon'),
                                                                                    default => 'Closed',
                                                                                };
                                                                            }
                                                                            
                                                                            $qOpenable = in_array($qState, ['available', 'retry', 'retry_passed', 'complete'], true);
                                                                            $qTeacher = $quiz->teacher?->user?->name ?? $class->teachers->first()?->user?->name ?? 'Your Teacher';
                                                                            $qRemaining = $qStatus['remaining'] ?? null;
                                                                        @endphp
                                                                        <div class="quiz-item qc-{{ $qState }}">
                                                                            <div class="quiz-item-top">
                                                                                <span class="quiz-state-pill qsp-{{ $qState }}">{{ $qPillLabel }}</span>
                                                                                @if($qStatus['best'] !== null)
                                                                                    <span class="quiz-score-pill {{ $qState === 'complete' || $qState === 'retry_passed' ? 'quiz-score-pass' : 'quiz-score-fail' }}">
                                                                                        🎯 {{ $qStatus['best'] }}%
                                                                                    </span>
                                                                                @endif
                                                                            </div>

                                                                            <h3>{{ $quiz->title }}</h3>

                                                                            <div class="quiz-meta-row">
                                                                                <span class="quiz-meta-chip">🧩 {{ $quiz->questions_count ?? 0 }} questions</span>
                                                                                @if($quiz->time_limit_minutes)
                                                                                    <span class="quiz-meta-chip">⏱️ {{ $quiz->time_limit_minutes }} min</span>
                                                                                @endif
                                                                                <span class="quiz-meta-chip">🎯 Pass {{ $quiz->passing_percentage }}%</span>
                                                                            </div>

                                                                            @if($qStatus['attempts']->isNotEmpty())
                                                                                <p class="quiz-desc">📅 {{ $qStatus['finished_count'] }} attempt(s) · Last {{ $qStatus['latest']?->completed_at?->format('M j') }}</p>
                                                                            @else
                                                                                <p class="quiz-desc">👨‍🏫 {{ $qTeacher }}</p>
                                                                            @endif

                                                                            @if($qOpenable)
                                                                                <a href="{{ \App\Filament\Student\Pages\QuizAttempt::getUrl(['quiz' => $quiz->id]) }}" class="quiz-open-btn">
                                                                                    {{ $qBtnLabel }}
                                                                                </a>
                                                                            @else
                                                                                <span class="quiz-open-btn quiz-open-disabled">{{ $qBtnLabel }}</span>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                {{-- ── TAB 6: GRADES ── --}}
                @elseif($activeTab === 'grades')
                    <div class="glass-card {{ $tier === 'kids' ? 'kids-grades-panel' : '' }}">
                        <div class="glass-card-title">
                            <span>🏆 My Grades</span>
                            <span style="font-size: 0.85rem; color: #4f46e5; font-weight: 700;">
                                @if($tier === 'kids')
                                    {{ $allGrades->count() }} result(s)
                                @else
                                    Average: {{ $overallAvg }}%
                                @endif
                            </span>
                        </div>

                        @php
                            $kidsGradeIdx = 0;
                        @endphp

                        {{-- Class filter chips --}}
                        @if($allGrades->isNotEmpty() && ($allClasses->count() > 1 || $filteredClass))
                            <div class="asg-filter-bar">
                                <button
                                    wire:click="setTab('grades')"
                                    type="button"
                                    class="asg-filter-chip {{ $classFilterId === null ? 'asg-filter-chip-active' : '' }}">
                                    🌍 All
                                    <span class="asg-filter-chip-count">{{ $allGrades->count() }}</span>
                                </button>

                                @foreach($allClassGroups as $schoolGroup)
                                    @foreach($schoolGroup['groups'] as $gradeGroup)
                                        @foreach($gradeGroup['classes'] as $class)
                                            @if($classHasGrades[$class->id] ?? false)
                                                <button
                                                    wire:click="openTab('grades', {{ (int) $class->id }})"
                                                    type="button"
                                                    class="asg-filter-chip {{ (int) $class->id === $classFilterId ? 'asg-filter-chip-active' : '' }}">
                                                    📘 {{ $class->name }}
                                                    <span class="asg-filter-chip-count">{{ count($gradesByClassId[$class->id] ?? []) }}</span>
                                                </button>
                                            @endif
                                        @endforeach
                                    @endforeach
                                @endforeach

                                @if($filteredClass)
                                    <span class="asg-filter-active-note">📍 {{ $filteredClass->name }}</span>
                                @endif
                            </div>
                        @endif

                        @if(empty($filteredGrades) && $allGrades->isEmpty())
                            <div class="tab-filter-empty">
                                @if($filteredClass)
                                    No grades recorded for <strong>{{ $filteredClass->name }}</strong> yet. Keep studying!
                                @else
                                    📊 No grades yet. Complete some quizzes or assignments to see your results here!
                                @endif
                            </div>
                        @else
                            {{-- Summary KPI cards --}}
                            <div class="kpi-grid">
                                <div class="kpi-card kpi-card-purple">
                                    <div class="kpi-header">
                                        <span class="kpi-label">OVERALL AVERAGE</span>
                                        <span class="kpi-icon">🏆</span>
                                    </div>
                                    <div class="kpi-value">{{ $overallAvg }}%</div>
                                    <div class="kpi-sub">{{ $overallAvg >= 90 ? '🌟 Excellent' : ($overallAvg >= 75 ? '👍 Good' : ($overallAvg >= 60 ? '📈 Fair' : '💪 Keep Trying')) }}</div>
                                </div>

                                <div class="kpi-card kpi-card-emerald">
                                    <div class="kpi-header">
                                        <span class="kpi-label">QUIZ AVERAGE</span>
                                        <span class="kpi-icon">🧠</span>
                                    </div>
                                    <div class="kpi-value">{{ $quizAvg }}%</div>
                                    <div class="kpi-sub">{{ $quizGradeCount }} quiz(es) completed</div>
                                </div>

                                <div class="kpi-card kpi-card-blue">
                                    <div class="kpi-header">
                                        <span class="kpi-label">ASSIGNMENTS</span>
                                        <span class="kpi-icon">📋</span>
                                    </div>
                                    <div class="kpi-value">{{ $assignmentAvg }}%</div>
                                    <div class="kpi-sub">{{ $gradedSubmissionsCount }} graded · {{ $pendingGradesCount }} pending</div>
                                </div>

                                <div class="kpi-card kpi-card-amber">
                                    <div class="kpi-header">
                                        <span class="kpi-label">TOTAL RESULTS</span>
                                        <span class="kpi-icon">📊</span>
                                    </div>
                                    <div class="kpi-value">{{ $allGrades->count() }}</div>
                                    <div class="kpi-sub">{{ $allFeedbacks->count() }} feedback(s) received</div>
                                </div>
                            </div>

                            {{-- Grades list --}}
                            @if($tier === 'kids')
                                {{-- Kids: colorful grade cards --}}
                                @foreach($gradeDisplayItems as $grade)
                                    @php
                                        $kidsGradeIdx++;
                                        $colorClass = 'kidc-' . ['red','blue','purple','green','orange','pink'][($kidsGradeIdx - 1) % 6];
                                        $isQuiz = $grade['type'] === 'quiz';
                                        $scorePct = $grade['score'] ?? 0;
                                        $scoreColor = $scorePct >= 80 ? '#16a34a' : ($scorePct >= 60 ? '#ca8a04' : '#dc2626');
                                        $scoreBg = $scorePct >= 80 ? '#f0fdf4' : ($scorePct >= 60 ? '#fefce8' : '#fef2f2');
                                    @endphp
                                    <div class="kids-grade-card {{ $colorClass }}">
                                        <div class="kids-grade-top">
                                            <span class="kids-grade-type {{ $isQuiz ? 'kgc-quiz' : 'kgc-assignment' }}">
                                                {{ $isQuiz ? '🧠 Quiz' : '📋 Assignment' }}
                                            </span>
                                            <span class="kids-grade-score" style="color: {{ $scoreColor }}; background: {{ $scoreBg }};">
                                                {{ $scorePct }}%
                                            </span>
                                        </div>

                                        <h3 class="kids-grade-title">{{ $grade['title'] }}</h3>

                                        <div class="kids-grade-meta">
                                            <span>📘 {{ $grade['class_name'] }}</span>
                                            <span>👤 {{ $grade['teacher'] }}</span>
                                            <span>📅 {{ $grade['date']?->format('M j, Y') ?? '—' }}</span>
                                            @if($isQuiz)
                                                <span>🔁 {{ $grade['attempt_count'] }} attempt(s)</span>
                                            @endif
                                        </div>

                                        <div class="kids-grade-status">
                                            <span class="kids-grade-status-pill {{ $grade['is_passed'] ? 'kgsp-pass' : 'kgsp-fail' }}">
                                                {{ $grade['status_label'] }}
                                            </span>
                                            @if(! $isQuiz && $grade['points'] !== null)
                                                <span class="kids-grade-points">
                                                    {{ $grade['points'] }} / {{ $grade['max_points'] }} pts
                                                </span>
                                            @endif
                                        </div>

                                        @if($isQuiz)
                                            <div style="margin-top: 0.85rem;">
                                                <button
                                                    type="button"
                                                    class="grade-quiz-toggle"
                                                    @click="openGradeQuizId = openGradeQuizId === {{ (int) $grade['quiz_id'] }} ? null : {{ (int) $grade['quiz_id'] }}"
                                                    :aria-expanded="openGradeQuizId === {{ (int) $grade['quiz_id'] }}">
                                                    <span class="grade-quiz-toggle-icon">›</span>
                                                    <span>View attempts</span>
                                                    <span class="grade-quiz-attempt-count">{{ $grade['attempt_count'] }}</span>
                                                </button>
                                            </div>
                                            <div
                                                class="grade-attempts-panel"
                                                x-show="openGradeQuizId === {{ (int) $grade['quiz_id'] }}"
                                                x-cloak>
                                                @foreach($grade['attempts'] as $attempt)
                                                    <div class="grade-attempt-row">
                                                        <div class="grade-attempt-info">
                                                            <strong>Attempt #{{ $attempt['attempt_number'] }}</strong>
                                                            <span class="grade-attempt-meta">
                                                                {{ $attempt['date']?->format('M j, Y') ?? '—' }} · {{ $attempt['status_label'] }}
                                                            </span>
                                                        </div>
                                                        <div class="grade-attempt-side">
                                                            <span class="grade-attempt-score">{{ $attempt['score'] }}%</span>
                                                            <span class="grade-attempt-status {{ $attempt['is_passed'] ? 'is-pass' : 'is-fail' }}">
                                                                {{ $attempt['is_passed'] ? 'Passed' : 'Not passed' }}
                                                            </span>
                                                            @if($grade['only_shows_score'])
                                                                <span class="grade-attempt-hidden">🔒 Answers hidden</span>
                                                            @else
                                                                <button
                                                                    type="button"
                                                                    class="grade-attempt-view"
                                                                    wire:click="openQuizAttemptDetail({{ (int) $attempt['model_id'] }})">
                                                                    See answers
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($grade['has_feedback'] && $grade['feedback'])
                                            <div class="kids-grade-feedback">
                                                <div class="kids-grade-feedback-head">
                                                    💬 Teacher Feedback
                                                    @if($grade['grader_name'])
                                                        <span class="kids-grade-feedback-teacher">— {{ $grade['grader_name'] }}</span>
                                                    @endif
                                                </div>
                                                <div class="kids-grade-feedback-text">{!! $grade['feedback'] !!}</div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                {{-- Junior/Senior: table layout --}}
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Title</th>
                                            <th>Class</th>
                                            <th>Teacher</th>
                                            <th>Score</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Feedback</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gradeDisplayItems as $grade)
                                            @php
                                                $isQuiz = $grade['type'] === 'quiz';
                                                $scorePct = $grade['score'] ?? 0;
                                                $scoreColor = $scorePct >= 80 ? '#16a34a' : ($scorePct >= 60 ? '#ca8a04' : '#dc2626');
                                            @endphp
                                            <tr>
                                                <td data-label="Type">
                                                    <span class="status-pill {{ $isQuiz ? 'status-ok' : 'status-pending' }}" style="font-size: 0.8rem;">
                                                        {{ $isQuiz ? '🧠 Quiz' : '📋 Assignment' }}
                                                    </span>
                                                </td>
                                                <td data-label="Title" class="class-title">
                                                    @if($isQuiz)
                                                        <button
                                                            type="button"
                                                            class="grade-quiz-toggle"
                                                            @click="openGradeQuizId = openGradeQuizId === {{ (int) $grade['quiz_id'] }} ? null : {{ (int) $grade['quiz_id'] }}"
                                                            :aria-expanded="openGradeQuizId === {{ (int) $grade['quiz_id'] }}">
                                                            <span class="grade-quiz-toggle-icon">›</span>
                                                            <span>{{ $grade['title'] }}</span>
                                                            <span class="grade-quiz-attempt-count">{{ $grade['attempt_count'] }}</span>
                                                        </button>
                                                    @else
                                                        {{ $grade['title'] }}
                                                    @endif
                                                </td>
                                                <td data-label="Class" style="color: #64748b; font-weight: 600;">{{ $grade['class_name'] }}</td>
                                                <td data-label="Teacher" class="teacher-name">{{ $grade['teacher'] }}</td>
                                                <td data-label="Score" style="font-size: 1.15rem; font-weight: 800; color: {{ $scoreColor }};">
                                                    @if($isQuiz && $grade['attempt_count'] > 1)
                                                        <span style="display: block; font-size: 0.68rem; color: #64748b; font-weight: 800; letter-spacing: 0.04em;">BEST</span>
                                                    @endif
                                                    {{ $scorePct }}%
                                                    @if($isQuiz)
                                                        <span class="grade-attempt-sub">
                                                            {{ $grade['attempt_count'] }} attempt(s) · Latest {{ $grade['latest_score'] }}%
                                                        </span>
                                                    @elseif($grade['points'] !== null)
                                                        <span style="font-size: 0.8rem; color: #64748b;">({{ $grade['points'] }}/{{ $grade['max_points'] }})</span>
                                                    @endif
                                                </td>
                                                <td data-label="Status">
                                                    <span class="status-pill {{ $grade['is_passed'] ? 'status-ok' : 'status-pending' }}">
                                                        {{ $grade['status_label'] }}
                                                    </span>
                                                </td>
                                                <td data-label="Date" style="color: #64748b;">{{ $grade['date']?->format('M j, Y') ?? '—' }}</td>
                                                <td data-label="Feedback">
                                                    @if($isQuiz)
                                                        <span class="status-pill status-pending" style="font-size: 0.8rem;">
                                                            🔎 {{ $grade['attempt_count'] }} attempt(s)
                                                        </span>
                                                    @elseif($grade['has_feedback'] && $grade['feedback'])
                                                        <span class="status-pill status-ok" style="font-size: 0.8rem;" title="{{ $grade['feedback'] }}">
                                                            💬 Feedback
                                                            @if($grade['grader_name'])
                                                                — {{ $grade['grader_name'] }}
                                                            @endif
                                                        </span>
                                                    @else
                                                        <span style="color: #cbd5e1; font-weight: 600;">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($isQuiz)
                                                <tr
                                                    class="grade-attempts-row"
                                                    x-show="openGradeQuizId === {{ (int) $grade['quiz_id'] }}"
                                                    x-cloak>
                                                    <td colspan="8" class="grade-attempts-cell">
                                                        <div class="grade-attempts-panel">
                                                            @foreach($grade['attempts'] as $attempt)
                                                                <div class="grade-attempt-row">
                                                                    <div class="grade-attempt-info">
                                                                        <strong>Attempt #{{ $attempt['attempt_number'] }}</strong>
                                                                        <span class="grade-attempt-meta">
                                                                            {{ $attempt['date']?->format('M j, Y · g:i A') ?? '—' }} · {{ $attempt['status_label'] }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="grade-attempt-side">
                                                                        <span class="grade-attempt-score">{{ $attempt['score'] }}%</span>
                                                                        <span class="grade-attempt-status {{ $attempt['is_passed'] ? 'is-pass' : 'is-fail' }}">
                                                                            {{ $attempt['is_passed'] ? 'Passed' : 'Not passed' }}
                                                                        </span>
                                                                        @if($grade['only_shows_score'])
                                                                            <span class="grade-attempt-hidden">🔒 Answers hidden</span>
                                                                        @else
                                                                            <button
                                                                                type="button"
                                                                                class="grade-attempt-view"
                                                                                wire:click="openQuizAttemptDetail({{ (int) $attempt['model_id'] }})">
                                                                                See answers & AI insights
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            {{-- Feedback section (all feedbacks combined) --}}
                            @if($allFeedbacks->isNotEmpty())
                                <div class="grades-feedback-section">
                                    <div class="glass-card-title" style="margin-top: 0.5rem;">
                                        <span>💬 Teacher Feedback</span>
                                        <span style="font-size: 0.85rem; color: #4f46e5; font-weight: 700;">{{ $allFeedbacks->count() }} feedback(s)</span>
                                    </div>

                                    @foreach($allFeedbacks as $fb)
                                        @php
                                            $fbIsQuiz = $fb['type'] === 'quiz';
                                        @endphp
                                        <div class="grades-feedback-card">
                                            <div class="grades-feedback-head">
                                                <span class="grades-feedback-type">{{ $fbIsQuiz ? '🧠 Quiz' : '📋 Assignment' }}</span>
                                                <span class="grades-feedback-title">{{ $fb['title'] }}</span>
                                                <span class="grades-feedback-class">📘 {{ $fb['class_name'] }}</span>
                                            </div>
                                            <div class="grades-feedback-body">
                                                <div class="grades-feedback-text">{!! $fb['feedback'] !!}</div>
                                                <div class="grades-feedback-meta">
                                                    👤 {{ $fb['grader_name'] ?? $fb['teacher'] }}
                                                    · 📅 {{ $fb['date']?->format('M j, Y') ?? '—' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        @if($gradeDetailOpen && $gradeDetail)
                            <div
                                class="grade-detail-overlay"
                                @click.self="$wire.closeQuizAttemptDetail()"
                                @keydown.escape.window="$wire.closeQuizAttemptDetail()">
                                <div class="grade-detail-modal" role="dialog" aria-modal="true" aria-label="Quiz attempt details">
                                    <div class="grade-detail-header">
                                        <div>
                                            <span class="grade-detail-kicker">
                                                Attempt #{{ $gradeDetail['attempt_number'] }} · {{ $gradeDetail['date'] }}
                                            </span>
                                            <h2>{{ $gradeDetail['quiz_title'] }}</h2>
                                            <p>
                                                Score: <strong>{{ $gradeDetail['percentage'] }}%</strong>
                                                · {{ $gradeDetail['correct_count'] }} / {{ $gradeDetail['total_questions'] }} correct
                                                · {{ $gradeDetail['passed'] ? 'Passed' : 'Not passed' }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            class="grade-detail-close"
                                            wire:click="closeQuizAttemptDetail"
                                            aria-label="Close details">
                                            ×
                                        </button>
                                    </div>

                                    @php
                                        $ai = $gradeDetail['ai_analysis'];
                                    @endphp

                                    <div class="grade-detail-ai">
                                        <h3>✨ AI Learning Insights</h3>
                                        @if($ai)
                                            @if(! empty($ai['summary']))
                                                <p class="grade-detail-ai-summary">{{ $ai['summary'] }}</p>
                                            @endif

                                            <div class="grade-detail-ai-grid">
                                                <section class="grade-detail-ai-panel is-strength">
                                                    <h4>💪 Strongest areas</h4>
                                                    @forelse($ai['strengths'] ?? [] as $area)
                                                        <div class="grade-detail-ai-area">
                                                            <strong>{{ $area['area'] }}</strong>
                                                            <span>{{ (int) ($area['score'] ?? 0) }}%</span>
                                                            @if(! empty($area['comment']))
                                                                <p>{{ $area['comment'] }}</p>
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <p class="grade-detail-ai-empty">No standout strengths were detected for this attempt.</p>
                                                    @endforelse
                                                </section>

                                                <section class="grade-detail-ai-panel is-focus">
                                                    <h4>🎯 Areas to focus on</h4>
                                                    @forelse($ai['focus_areas'] ?? [] as $area)
                                                        <div class="grade-detail-ai-area">
                                                            <strong>{{ $area['area'] }}</strong>
                                                            <span>{{ (int) ($area['score'] ?? 0) }}%</span>
                                                            @if(! empty($area['comment']))
                                                                <p>{{ $area['comment'] }}</p>
                                                            @endif
                                                            @if(! empty($area['suggestion']))
                                                                <p><strong>Try this:</strong> {{ $area['suggestion'] }}</p>
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <p class="grade-detail-ai-empty">No weak areas were detected — excellent work!</p>
                                                    @endforelse
                                                </section>
                                            </div>

                                            @if(! empty($ai['next_steps']))
                                                <div class="grade-detail-next">
                                                    <h4>🚀 Next steps</h4>
                                                    <ul>
                                                        @foreach($ai['next_steps'] as $step)
                                                            <li>{{ $step }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        @else
                                            <p class="grade-detail-ai-empty">
                                                AI learning insights are not available for this attempt yet.
                                            </p>
                                        @endif
                                    </div>

                                    <div class="grade-detail-questions">
                                        @foreach($gradeDetail['questions'] as $question)
                                            @php
                                                $questionState = ! $question['is_answered']
                                                    ? 'is-skipped'
                                                    : ($question['is_correct'] ? 'is-correct' : 'is-wrong');
                                            @endphp
                                            <article class="grade-detail-question {{ $questionState }}">
                                                <div class="grade-detail-question-head">
                                                    <span class="grade-detail-question-num">Question {{ $question['number'] }}</span>
                                                    <span class="grade-detail-question-badge {{ $questionState }}">
                                                        {{ ! $question['is_answered'] ? '— Not answered' : ($question['is_correct'] ? '✓ Correct' : '✗ Incorrect') }}
                                                    </span>
                                                    <span class="grade-detail-question-points">
                                                        {{ $question['points_awarded'] }} / {{ $question['points_possible'] }} pts
                                                    </span>
                                                </div>

                                                <p class="grade-detail-question-text">{{ $question['question'] }}</p>

                                                @if($question['question_image_url'])
                                                    <div class="grade-detail-media">
                                                        <img src="{{ $question['question_image_url'] }}" alt="Question image">
                                                    </div>
                                                @elseif($question['question_video_url'])
                                                    <div class="grade-detail-media">
                                                        <video controls preload="metadata" src="{{ $question['question_video_url'] }}"></video>
                                                    </div>
                                                @endif

                                                <div class="grade-detail-options">
                                                    @foreach($question['options'] as $option)
                                                        <div class="grade-detail-option {{ $option['is_correct'] ? 'is-correct' : '' }} {{ $option['is_selected'] ? 'is-selected' : '' }}">
                                                            <span>
                                                                {{ $option['is_correct'] ? '✓' : ($option['is_selected'] ? '✗' : '·') }}
                                                                {{ $option['text'] }}
                                                            </span>
                                                            @if($option['is_correct'])
                                                                <em>Correct answer</em>
                                                            @elseif($option['is_selected'])
                                                                <em>Your choice</em>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>

                                                @if($question['explanation'])
                                                    <div class="grade-detail-explanation">
                                                        <strong>💡 Explanation:</strong> {{ $question['explanation'] }}
                                                    </div>
                                                @endif
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                {{-- ── TAB 7: CALENDAR (BY TIER) ── --}}
                @elseif($activeTab === 'calendar')
                    @php
                        $calYear = ($calendarYear ?? 0) ?: (int) now()->year;
                        $calMonth = ($calendarMonth ?? 0) ?: (int) now()->month;
                        $calMonthObj = \Carbon\Carbon::create($calYear, $calMonth, 1);
                        $calDaysInMonth = $calMonthObj->daysInMonth;
                        $calFirstDayOfWeek = $calMonthObj->dayOfWeek;
                        $calToday = now()->format('Y-m-d');
                        $calPrevMonth = $calMonthObj->copy()->subMonth();
                        $calNextMonth = $calMonthObj->copy()->addMonth();
                        $calMonthName = $calMonthObj->format('F Y');
                        $calMonthLabel = strtoupper($calMonthObj->format('F'));
                        $calYearLabel = $calYear;
                        $calWeekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

                        $calEvents = [];
                        $asgnCount = 0;
                        $quizCount = 0;
                        $urgentCount = 0;
                        $now48h = now()->addHours(48);

                        foreach ($allClasses as $cls) {
                            $clsAssignments = $assignmentsByClassId[$cls->id] ?? collect();
                            foreach ($clsAssignments as $asgn) {
                                if ($asgn->start_at) {
                                    $d = \Carbon\Carbon::parse($asgn->start_at)->format('Y-m-d');
                                    $calEvents[$d][] = [
                                        'type' => 'assignment_start',
                                        'title' => $asgn->title,
                                        'class' => $cls->name,
                                        'class_id' => $cls->id,
                                        'icon' => '📋',
                                        'color' => '#3b82f6',
                                        'meta' => 'Assignment opens',
                                        'url' => \App\Filament\Student\Pages\AssignmentView::getUrl(['assignment' => $asgn->id]),
                                    ];
                                }
                                if ($asgn->end_at) {
                                    $endCarbon = \Carbon\Carbon::parse($asgn->end_at);
                                    $d = $endCarbon->format('Y-m-d');
                                    $asgnCount++;
                                    if ($endCarbon->isFuture() && $endCarbon->lte($now48h)) {
                                        $urgentCount++;
                                    }
                                    $calEvents[$d][] = [
                                        'type' => 'assignment_due',
                                        'title' => $asgn->title,
                                        'class' => $cls->name,
                                        'class_id' => $cls->id,
                                        'icon' => '📋',
                                        'color' => '#f97316',
                                        'meta' => 'Assignment due at ' . $endCarbon->format('g:i A'),
                                        'url' => \App\Filament\Student\Pages\AssignmentView::getUrl(['assignment' => $asgn->id]),
                                    ];
                                }
                            }
                            $clsQuizzes = $quizzesByClassId[$cls->id] ?? collect();
                            foreach ($clsQuizzes as $quiz) {
                                if ($quiz->start_at) {
                                    $d = \Carbon\Carbon::parse($quiz->start_at)->format('Y-m-d');
                                    $calEvents[$d][] = [
                                        'type' => 'quiz_start',
                                        'title' => $quiz->title,
                                        'class' => $cls->name,
                                        'class_id' => $cls->id,
                                        'icon' => '🧠',
                                        'color' => '#8b5cf6',
                                        'meta' => 'Quiz opens',
                                        'url' => \App\Filament\Student\Pages\QuizAttempt::getUrl(['quiz' => $quiz->id]),
                                    ];
                                }
                                if ($quiz->end_at) {
                                    $endCarbon = \Carbon\Carbon::parse($quiz->end_at);
                                    $d = $endCarbon->format('Y-m-d');
                                    $quizCount++;
                                    if ($endCarbon->isFuture() && $endCarbon->lte($now48h)) {
                                        $urgentCount++;
                                    }
                                    $calEvents[$d][] = [
                                        'type' => 'quiz_due',
                                        'title' => $quiz->title,
                                        'class' => $cls->name,
                                        'class_id' => $cls->id,
                                        'icon' => '🧠',
                                        'color' => '#e11d48',
                                        'meta' => 'Quiz deadline ' . $endCarbon->format('g:i A'),
                                        'url' => \App\Filament\Student\Pages\QuizAttempt::getUrl(['quiz' => $quiz->id]),
                                    ];
                                }
                            }
                        }

                        ksort($calEvents);

                        $calNotes = [];
                        if ($student) {
                            $monthStart = $calMonthObj->copy()->startOfMonth()->toDateString();
                            $monthEnd = $calMonthObj->copy()->endOfMonth()->toDateString();
                            foreach ($student->calendarNotes()->whereBetween('note_date', [$monthStart, $monthEnd])->orderBy('id')->get() as $cn) {
                                $calNotes[$cn->note_date->format('Y-m-d')][] = ['id' => $cn->id, 'content' => $cn->content];
                            }
                        }
                        $notesCount = array_sum(array_map('count', $calNotes));
                    @endphp

                    <script type="application/json" id="cal-events-json">{!! json_encode($calEvents) !!}</script>
                    <script type="application/json" id="cal-notes-json">{!! json_encode($calNotes) !!}</script>

                    @if($tier === 'kids')
                        @include('filament.student.pages.calendar.kids-calendar', [
                            'student'              => $student,
                            'firstName'            => $firstName,
                            'calYear'              => $calYear,
                            'calMonth'             => $calMonth,
                            'calMonthObj'          => $calMonthObj,
                            'calDaysInMonth'       => $calDaysInMonth,
                            'calFirstDayOfWeek'    => $calFirstDayOfWeek,
                            'calToday'             => $calToday,
                            'calPrevMonth'         => $calPrevMonth,
                            'calNextMonth'         => $calNextMonth,
                            'calMonthName'         => $calMonthName,
                            'calMonthLabel'        => $calMonthLabel,
                            'calYearLabel'         => $calYearLabel,
                            'calWeekdays'          => $calWeekdays,
                            'allClasses'           => $allClasses,
                            'calEvents'            => $calEvents,
                            'calNotes'             => $calNotes,
                            'calendarSelectedDate' => $calendarSelectedDate,
                            'asgnCount'            => $asgnCount,
                            'quizCount'            => $quizCount,
                            'notesCount'           => $notesCount,
                            'urgentCount'          => $urgentCount,
                        ])
                    @elseif($tier === 'junior')
                        @include('filament.student.pages.calendar.junior-calendar', [
                            'student'              => $student,
                            'firstName'            => $firstName,
                            'activeGradeName'      => $activeGradeName,
                            'calYear'              => $calYear,
                            'calMonth'             => $calMonth,
                            'calMonthObj'          => $calMonthObj,
                            'calDaysInMonth'       => $calDaysInMonth,
                            'calFirstDayOfWeek'    => $calFirstDayOfWeek,
                            'calToday'             => $calToday,
                            'calPrevMonth'         => $calPrevMonth,
                            'calNextMonth'         => $calNextMonth,
                            'calMonthName'         => $calMonthName,
                            'calMonthLabel'        => $calMonthLabel,
                            'calYearLabel'         => $calYearLabel,
                            'calWeekdays'          => $calWeekdays,
                            'allClasses'           => $allClasses,
                            'calEvents'            => $calEvents,
                            'calNotes'             => $calNotes,
                            'calendarSelectedDate' => $calendarSelectedDate,
                            'asgnCount'            => $asgnCount,
                            'quizCount'            => $quizCount,
                            'notesCount'           => $notesCount,
                            'urgentCount'          => $urgentCount,
                        ])
                    @else
                        @include('filament.student.pages.calendar.senior-calendar', [
                            'student'              => $student,
                            'firstName'            => $firstName,
                            'activeGradeName'      => $activeGradeName,
                            'calYear'              => $calYear,
                            'calMonth'             => $calMonth,
                            'calMonthObj'          => $calMonthObj,
                            'calDaysInMonth'       => $calDaysInMonth,
                            'calFirstDayOfWeek'    => $calFirstDayOfWeek,
                            'calToday'             => $calToday,
                            'calPrevMonth'         => $calPrevMonth,
                            'calNextMonth'         => $calNextMonth,
                            'calMonthName'         => $calMonthName,
                            'calMonthLabel'        => $calMonthLabel,
                            'calYearLabel'         => $calYearLabel,
                            'calWeekdays'          => $calWeekdays,
                            'allClasses'           => $allClasses,
                            'calEvents'            => $calEvents,
                            'calNotes'             => $calNotes,
                            'calendarSelectedDate' => $calendarSelectedDate,
                            'asgnCount'            => $asgnCount,
                            'quizCount'            => $quizCount,
                            'notesCount'           => $notesCount,
                            'urgentCount'          => $urgentCount,
                        ])
                    @endif

                {{-- ── TAB 8: NOTIFICATIONS ── --}}
                @elseif($activeTab === 'notifications')
                    @include('filament.student.pages.components.notifications-view', [
                        'tier' => $tier,
                        'notifications' => $notifications,
                        'notifStats' => $notifStats ?? [],
                        'notificationFilter' => $notificationFilter ?? 'all',
                        'notificationSearch' => $notificationSearch ?? '',
                    ])


                {{-- ── TAB 9: PROFILE ── --}}
                @elseif($activeTab === 'profile')
                    <div class="profile-wrap">
                        @if($profileMessage)
                            <div class="profile-alert profile-alert-{{ $profileMessageType }}">
                                {{ $profileMessage }}
                            </div>
                        @endif

                        <div class="profile-grid-3">

                            {{-- Photo card --}}
                            <div class="profile-card">
                                <div class="profile-card-head">
                                    <div class="profile-avatar" style="width: 4.5rem; height: 4.5rem; font-size: 1.8rem; overflow: hidden; flex-shrink: 0;">
                                        @if($profilePhoto)
                                            <img src="{{ $profilePhoto->temporaryUrl() }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                        @elseif($student?->profile_photo_url)
                                            <img src="{{ $student->profile_photo_url }}" alt="My photo" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            {{ strtoupper(substr($firstName, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <h3>My Photo</h3>
                                        <p>Pick a picture so your friends know it's you!</p>
                                    </div>
                                </div>
                                <label class="profile-photo-btn">
                                    📸 Choose a Photo
                                    <input type="file" wire:model="profilePhoto" accept="image/*" hidden>
                                </label>
                                <div class="profile-upload-progress" wire:loading wire:target="profilePhoto">
                                    Uploading your photo... ⏳
                                </div>
                                @error('profilePhoto') <small class="profile-error">{{ $message }}</small> @enderror
                                <div class="profile-note" style="margin-top: 0.5rem;">
                                    Your photo stays just for your school account. Ask a parent if you are not sure!
                                </div>
                            </div>

                            {{-- My info card --}}
                            <div class="profile-card">
                                <div class="profile-card-head">
                                    <div class="profile-avatar">👤</div>
                                    <div>
                                        <h3>My Account</h3>
                                        <p>Update your details so we know how to reach you.</p>
                                    </div>
                                </div>

                                <form wire:submit="updateProfile" class="profile-form">
                                    <div class="profile-field-row">
                                        <label class="profile-field">
                                            <span>My Name</span>
                                            <input type="text" wire:model="profileName" placeholder="Your full name">
                                            @error('profileName') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                        <label class="profile-field">
                                            <span>My Email</span>
                                            <input type="email" wire:model="profileEmail" placeholder="you@school.com">
                                            @error('profileEmail') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                    </div>

                                    <div class="profile-field-row">
                                        <label class="profile-field">
                                            <span>My Birthday</span>
                                            <input type="date" wire:model="profileDateOfBirth">
                                            @error('profileDateOfBirth') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                        <label class="profile-field">
                                            <span>I am a</span>
                                            <select wire:model="profileGender">
                                                <option value="male">Boy 👦</option>
                                                <option value="female">Girl 👧</option>
                                                <option value="other">Other 🧑</option>
                                            </select>
                                            @error('profileGender') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                    </div>

                                    <div class="profile-field-row">
                                        <label class="profile-field">
                                            <span>My Phone</span>
                                            <input type="text" wire:model="profilePhone" placeholder="Your phone number">
                                            @error('profilePhone') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                        <label class="profile-field">
                                            <span>Address</span>
                                            <input type="text" wire:model="profileAddress" placeholder="Where do you live?">
                                            @error('profileAddress') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                    </div>

                                    <div class="profile-field-row">
                                        <label class="profile-field">
                                            <span>Parent / Guardian Name</span>
                                            <input type="text" wire:model="profileParentName" placeholder="Parent or guardian name">
                                            @error('profileParentName') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                        <label class="profile-field">
                                            <span>Parent / Guardian Phone</span>
                                            <input type="text" wire:model="profileParentPhone" placeholder="Parent's phone number">
                                            @error('profileParentPhone') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                    </div>

                                    <button type="submit" class="profile-save-btn">Save My Info 💾</button>
                                </form>
                            </div>
                        </div>

                        {{-- Password card --}}
                        <div class="profile-card">
                            <div class="profile-card-head">
                                <div class="profile-avatar profile-avatar-lock">🔒</div>
                                <div>
                                    <h3>Change My Password</h3>
                                    <p>Pick a new secret password to keep your account safe.</p>
                                </div>
                            </div>

                            <form wire:submit="updatePassword" class="profile-form">
                                <div class="profile-field-row">
                                    <label class="profile-field">
                                        <span>Current Password</span>
                                        <input type="password" wire:model="currentPassword" placeholder="Your current password">
                                        @error('currentPassword') <small class="profile-error">{{ $message }}</small> @enderror
                                    </label>
                                    <label class="profile-field">
                                        <span>New Password</span>
                                        <input type="password" wire:model="newPassword" placeholder="A brand new password">
                                        @error('newPassword') <small class="profile-error">{{ $message }}</small> @enderror
                                    </label>
                                    <label class="profile-field">
                                        <span>Repeat New Password</span>
                                        <input type="password" wire:model="newPassword_confirmation" placeholder="Same password again">
                                        @error('newPassword_confirmation') <small class="profile-error">{{ $message }}</small> @enderror
                                    </label>
                                </div>
                                <button type="submit" class="profile-save-btn">Update Password 🔑</button>
                            </form>
                        </div>

                        <div class="profile-note">
                            🏫 For any changes to your school, grade or class, please ask your teacher or school admin to help. Students cannot change their own enrollment.
                        </div>
                    </div>
                @endif

            </div>

        </main>

    </div>
</div>
