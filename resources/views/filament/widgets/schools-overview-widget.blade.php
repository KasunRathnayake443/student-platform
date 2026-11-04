<x-filament-widgets::widget class="fi-schools-overview-widget">
    <style>
        .fi-schools-overview-widget .so-header,
        .fi-schools-overview-widget .so-school-banner,
        .fi-schools-overview-widget .so-row,
        .fi-schools-overview-widget .so-modal-header,
        .fi-schools-overview-widget .so-modal-list-row {
            min-width: 0;
        }
        .fi-schools-overview-widget .so-row-main,
        .fi-schools-overview-widget .so-row-meta,
        .fi-schools-overview-widget .so-modal-title-wrap {
            min-width: 0;
        }
        .fi-schools-overview-widget .so-row-main,
        .fi-schools-overview-widget .so-row-meta,
        .fi-schools-overview-widget .so-row-title,
        .fi-schools-overview-widget .so-panel-header {
            flex-wrap: wrap;
        }
        .fi-schools-overview-widget .so-row-main span,
        .fi-schools-overview-widget .so-school-banner-info h3,
        .fi-schools-overview-widget .so-school-banner-info p,
        .fi-schools-overview-widget .so-modal-title-wrap h3 {
            overflow-wrap: anywhere;
        }
        .fi-schools-overview-widget .so-school-banner-info > div:last-child,
        .fi-schools-overview-widget .so-modal-title-wrap > div:last-child {
            min-width: 0;
        }
        .fi-schools-overview-widget .so-row {
            gap: 0.75rem;
        }
        .fi-schools-overview-widget .so-modal {
            overflow-x: hidden !important;
        }

        @media (max-width: 768px) {
            .fi-schools-overview-widget .so-header h2 {
                font-size: 1rem !important;
            }
            .fi-schools-overview-widget .so-grid {
                grid-template-columns: minmax(0, 1fr) !important;
            }
            .fi-schools-overview-widget .so-school-tabs {
                flex-wrap: nowrap !important;
                overflow-x: auto;
                padding-bottom: 0.75rem;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
            }
            .fi-schools-overview-widget .so-school-tabs > button {
                flex-shrink: 0;
            }
        }

        @media (max-width: 640px) {
            .fi-schools-overview-widget .so-school-banner {
                flex-direction: column;
                align-items: stretch !important;
            }
            .fi-schools-overview-widget .so-school-banner-info,
            .fi-schools-overview-widget .so-school-banner-status {
                width: 100%;
            }
            .fi-schools-overview-widget .so-row {
                flex-direction: column;
                align-items: stretch !important;
                gap: 0.6rem !important;
            }
            .fi-schools-overview-widget .so-row-main,
            .fi-schools-overview-widget .so-row-meta,
            .fi-schools-overview-widget .so-row-action {
                width: 100%;
            }
            .fi-schools-overview-widget .so-row-action {
                display: flex;
                align-items: center;
            }
            .fi-schools-overview-widget .so-row-action > .fi-badge,
            .fi-schools-overview-widget .so-row-action > .so-row-button,
            .fi-schools-overview-widget .so-row-action > button {
                width: 100%;
                justify-content: center;
            }
            .fi-schools-overview-widget .so-modal-overlay {
                padding: 0.5rem !important;
            }
            .fi-schools-overview-widget .so-modal {
                max-height: 92vh !important;
                padding: 1rem !important;
                border-radius: 0.65rem !important;
            }
            .fi-schools-overview-widget .so-modal-header {
                align-items: flex-start !important;
                gap: 0.5rem;
            }
            .fi-schools-overview-widget .so-modal-grid {
                grid-template-columns: 1fr !important;
            }
            .fi-schools-overview-widget .so-modal-list-row {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.5rem !important;
            }
            .fi-schools-overview-widget .so-modal-list-row > button,
            .fi-schools-overview-widget .so-modal-list-row > a {
                width: 100%;
                justify-content: center;
                text-align: center;
            }
            .fi-schools-overview-widget .so-modal-footer {
                text-align: left !important;
            }
            .fi-schools-overview-widget .so-modal-footer > button {
                width: 100%;
            }
        }
    </style>

    <x-filament::section>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            
            <!-- Section Title & Header -->
            <div class="so-header" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                <div>
                    <h2 style="font-size: 1.125rem; font-weight: 700; line-height: 1.5; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <x-filament::icon
                            icon="heroicon-o-building-office-2"
                            style="width: 1.35rem; height: 1.35rem; color: #d97706;"
                        />
                        <span>Schools & Academic Structure Explorer</span>
                    </h2>
                    <p style="font-size: 0.85rem; opacity: 0.7; margin: 0.15rem 0 0 0;">
                        Select a school tab below to view its assigned grades, classes, lessons, and assignments. Click any item or button to view full details and learning materials.
                    </p>
                </div>
            </div>

            <!-- Divider -->
            <div style="height: 1px; background-color: currentColor; opacity: 0.1;"></div>

            <!-- SCHOOL TABS (ONLY displaying School names) -->
            <div class="so-school-tabs" style="display: flex; flex-wrap: wrap; gap: 0.5rem; border-bottom: 1px solid rgba(156, 163, 175, 0.2); pb: 0.75rem;">
                @forelse($schools as $school)
                    @php
                        $isSelected = ($selectedSchoolId === $school->id);
                    @endphp
                    <button
                        type="button"
                        wire:click="selectSchool({{ $school->id }})"
                        style="
                            padding: 0.5rem 1rem;
                            border-radius: 0.5rem;
                            font-size: 0.875rem;
                            font-weight: {{ $isSelected ? '700' : '500' }};
                            cursor: pointer;
                            transition: all 0.2s ease;
                            border: 1px solid {{ $isSelected ? '#d97706' : 'rgba(156, 163, 175, 0.3)' }};
                            background-color: {{ $isSelected ? 'rgba(217, 119, 6, 0.15)' : 'transparent' }};
                            color: {{ $isSelected ? '#d97706' : 'inherit' }};
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                        "
                    >
                        <x-filament::icon
                            icon="heroicon-m-building-office"
                            style="width: 1rem; height: 1rem;"
                        />
                        <span>{{ $school->name }}</span>

                        @if(!$school->is_active)
                            <x-filament::badge color="danger" size="xs">Inactive</x-filament::badge>
                        @endif
                    </button>
                @empty
                    <p style="font-size: 0.875rem; opacity: 0.7;">No schools available.</p>
                @endforelse
            </div>

            <!-- DYNAMICALLY RENDERED SECTION FOR SELECTED SCHOOL -->
            @if($selectedSchool)
                <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-top: 0.5rem;">
                    
                    <!-- Selected School Banner -->
                    <div class="so-school-banner" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; padding: 0.875rem 1.125rem; border-radius: 0.5rem; background-color: rgba(156, 163, 175, 0.08); border: 1px solid rgba(156, 163, 175, 0.15); gap: 0.75rem;">
                        <div class="so-school-banner-info" style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; background-color: rgba(217, 119, 6, 0.2); color: #d97706; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                {{ strtoupper(substr($selectedSchool->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 style="font-size: 1rem; font-weight: 700; margin: 0;">
                                    {{ $selectedSchool->name }}
                                </h3>
                                <p style="font-size: 0.8rem; opacity: 0.7; margin: 0;">
                                    Code: <strong>{{ $selectedSchool->code ?? 'N/A' }}</strong> &bull; 
                                    Email: {{ $selectedSchool->email ?? 'N/A' }} &bull; 
                                    Phone: {{ $selectedSchool->phone ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="so-school-banner-status" style="display: flex; align-items: center; gap: 0.5rem;">
                            <x-filament::badge color="{{ $selectedSchool->is_active ? 'success' : 'danger' }}">
                                {{ $selectedSchool->is_active ? 'Active School' : 'Inactive School' }}
                            </x-filament::badge>
                        </div>
                    </div>

                    <!-- Grid Layout for Grades, Classes, Lessons, and Assignments -->
                    <div class="so-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                        
                        <!-- ASSIGNED GRADES SECTION -->
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(156, 163, 175, 0.2); background-color: rgba(255, 255, 255, 0.02);">
                            <div class="so-panel-header" style="display: flex; align-items: center; justify-content: space-between;">
                                <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                    <x-filament::icon icon="heroicon-o-academic-cap" style="width: 1.1rem; height: 1.1rem; color: #d97706;" />
                                    <span>Assigned Grades</span>
                                </h4>
                                <x-filament::badge color="amber" size="sm">
                                    {{ $selectedSchool->grades->count() }} Grades
                                </x-filament::badge>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.25rem;">
                                @forelse($selectedSchool->grades as $grade)
                                    <div class="so-row so-grade-row" style="display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.875rem; border-radius: 0.375rem; border: 1px solid rgba(156, 163, 175, 0.15); background-color: rgba(156, 163, 175, 0.05);">
                                        <div class="so-row-main" style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-size: 0.875rem; font-weight: 600;">{{ $grade->name }}</span>
                                            <x-filament::badge size="xs" color="{{ $grade->is_active ? 'success' : 'gray' }}">
                                                {{ $grade->is_active ? 'Active' : 'Inactive' }}
                                            </x-filament::badge>
                                        </div>
                                        <div class="so-row-meta" style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.775rem; opacity: 0.8;">
                                            <span>{{ $grade->learning_classes_count }} Classes</span>
                                            <span>&bull;</span>
                                            <span>{{ $grade->students_count }} Students</span>
                                        </div>
                                    </div>
                                @empty
                                    <div style="text-align: center; padding: 1.5rem; opacity: 0.6; font-size: 0.85rem;">
                                        No grades assigned to {{ $selectedSchool->name }}.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- CLICKABLE ASSIGNED CLASSES SECTION -->
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(156, 163, 175, 0.2); background-color: rgba(255, 255, 255, 0.02);">
                            <div class="so-panel-header" style="display: flex; align-items: center; justify-content: space-between;">
                                <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                    <x-filament::icon icon="heroicon-o-building-library" style="width: 1.1rem; height: 1.1rem; color: #3b82f6;" />
                                    <span>Assigned Classes</span>
                                </h4>
                                <x-filament::badge color="info" size="sm">
                                    {{ $classes->count() }} Classes
                                </x-filament::badge>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.25rem;">
                                @forelse($classes as $class)
                                    <!-- Clickable Class Item -->
                                    <div
                                        class="so-row so-class-row"
                                        wire:click="selectClass({{ $class->id }})"
                                        style="
                                            display: flex;
                                            align-items: center;
                                            justify-content: space-between;
                                            padding: 0.625rem 0.875rem;
                                            border-radius: 0.375rem;
                                            border: 1px solid {{ ($selectedClassId === $class->id) ? '#3b82f6' : 'rgba(156, 163, 175, 0.2)' }};
                                            background-color: {{ ($selectedClassId === $class->id) ? 'rgba(59, 130, 246, 0.12)' : 'rgba(156, 163, 175, 0.05)' }};
                                            cursor: pointer;
                                            transition: all 0.2s ease;
                                        "
                                    >
                                        <div class="so-row-main">
                                            <div class="so-row-title" style="display: flex; align-items: center; gap: 0.4rem;">
                                                <span style="font-size: 0.875rem; font-weight: 600; color: #3b82f6;">{{ $class->name }}</span>
                                                <x-filament::badge size="xs" color="gray">
                                                    {{ $class->grade->name ?? 'No Grade' }}
                                                </x-filament::badge>
                                                @if($class->medium)
                                                    <x-filament::badge size="xs" color="info">
                                                        {{ ucfirst($class->medium) }}
                                                    </x-filament::badge>
                                                @endif
                                            </div>
                                            <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 0.15rem;">
                                                {{ $class->teachers_count }} Teachers &bull; {{ $class->lessons_count }} Lessons &bull; {{ $class->assignments_count }} Assignments
                                            </div>
                                        </div>
                                        <div class="so-row-action">
                                            <x-filament::badge size="xs" color="primary" icon="heroicon-m-eye">
                                                View Class
                                            </x-filament::badge>
                                        </div>
                                    </div>
                                @empty
                                    <div style="text-align: center; padding: 1.5rem; opacity: 0.6; font-size: 0.85rem;">
                                        No classes assigned to {{ $selectedSchool->name }}.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- LESSONS & MATERIALS SECTION -->
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(156, 163, 175, 0.2); background-color: rgba(255, 255, 255, 0.02);">
                            <div class="so-panel-header" style="display: flex; align-items: center; justify-content: space-between;">
                                <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                    <x-filament::icon icon="heroicon-o-book-open" style="width: 1.1rem; height: 1.1rem; color: #06b6d4;" />
                                    <span>Lessons & Materials</span>
                                </h4>
                                <x-filament::badge color="cyan" size="sm">
                                    {{ $lessons->count() }} Lessons
                                </x-filament::badge>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.25rem;">
                                @forelse($lessons as $lesson)
                                    <!-- Clickable Lesson Item -->
                                    <div
                                        class="so-row so-lesson-row"
                                        wire:click.stop="selectLesson({{ $lesson->id }})"
                                        style="
                                            display: flex;
                                            align-items: center;
                                            justify-content: space-between;
                                            padding: 0.625rem 0.875rem;
                                            border-radius: 0.375rem;
                                            border: 1px solid {{ ($selectedLessonId === $lesson->id) ? '#06b6d4' : 'rgba(156, 163, 175, 0.2)' }};
                                            background-color: {{ ($selectedLessonId === $lesson->id) ? 'rgba(6, 182, 212, 0.12)' : 'rgba(156, 163, 175, 0.05)' }};
                                            cursor: pointer;
                                            transition: all 0.2s ease;
                                        "
                                    >
                                        <div class="so-row-main">
                                            <div class="so-row-title" style="display: flex; align-items: center; gap: 0.4rem;">
                                                <span style="font-size: 0.875rem; font-weight: 600; color: #06b6d4;">{{ $lesson->title }}</span>
                                                <x-filament::badge size="xs" color="{{ $lesson->is_published ? 'success' : 'gray' }}">
                                                    {{ $lesson->is_published ? 'Published' : 'Draft' }}
                                                </x-filament::badge>
                                            </div>
                                            <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 0.15rem;">
                                                Class: <strong>{{ $lesson->learningClass->name ?? 'N/A' }}</strong> &bull; 
                                                Materials: <strong>{{ $lesson->attachments_count }} Files</strong>
                                            </div>
                                        </div>
                                        <div class="so-row-action">
                                            <!-- View Lessons & Materials Button -->
                                            <button
                                                class="so-row-button"
                                                type="button"
                                                wire:click.stop="selectLesson({{ $lesson->id }})"
                                                style="
                                                    padding: 0.3rem 0.65rem;
                                                    border-radius: 0.375rem;
                                                    background: linear-gradient(135deg, #06b6d4, #3b82f6);
                                                    color: white;
                                                    border: none;
                                                    font-size: 0.75rem;
                                                    font-weight: 700;
                                                    cursor: pointer;
                                                    display: flex;
                                                    align-items: center;
                                                    gap: 0.25rem;
                                                "
                                            >
                                                View Lessons & Materials 📚
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div style="text-align: center; padding: 1.5rem; opacity: 0.6; font-size: 0.85rem;">
                                        No lessons or materials uploaded for {{ $selectedSchool->name }}.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- CLICKABLE ASSIGNED ASSIGNMENTS SECTION -->
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(156, 163, 175, 0.2); background-color: rgba(255, 255, 255, 0.02);">
                            <div class="so-panel-header" style="display: flex; align-items: center; justify-content: space-between;">
                                <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                    <x-filament::icon icon="heroicon-o-clipboard-document-list" style="width: 1.1rem; height: 1.1rem; color: #10b981;" />
                                    <span>Class Assignments</span>
                                </h4>
                                <x-filament::badge color="success" size="sm">
                                    {{ $assignments->count() }} Assignments
                                </x-filament::badge>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.25rem;">
                                @forelse($assignments as $assignment)
                                    <!-- Clickable Assignment Item -->
                                    <div
                                        class="so-row so-assignment-row"
                                        wire:click="selectAssignment({{ $assignment->id }})"
                                        style="
                                            display: flex;
                                            align-items: center;
                                            justify-content: space-between;
                                            padding: 0.625rem 0.875rem;
                                            border-radius: 0.375rem;
                                            border: 1px solid {{ ($selectedAssignmentId === $assignment->id) ? '#10b981' : 'rgba(156, 163, 175, 0.2)' }};
                                            background-color: {{ ($selectedAssignmentId === $assignment->id) ? 'rgba(16, 185, 129, 0.12)' : 'rgba(156, 163, 175, 0.05)' }};
                                            cursor: pointer;
                                            transition: all 0.2s ease;
                                        "
                                    >
                                        <div class="so-row-main">
                                            <div class="so-row-title" style="display: flex; align-items: center; gap: 0.4rem;">
                                                <span style="font-size: 0.875rem; font-weight: 600; color: #10b981;">{{ $assignment->title }}</span>
                                                <x-filament::badge size="xs" color="{{ $assignment->is_published ? 'success' : 'gray' }}">
                                                    {{ $assignment->is_published ? 'Published' : 'Draft' }}
                                                </x-filament::badge>
                                            </div>
                                            <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 0.15rem;">
                                                Class: <strong>{{ $assignment->learningClass->name ?? 'N/A' }}</strong> &bull; 
                                                Submissions: <strong>{{ $assignment->submissions_count }}</strong>
                                            </div>
                                        </div>
                                        <div class="so-row-action">
                                            <x-filament::badge size="xs" color="success" icon="heroicon-m-eye">
                                                View
                                            </x-filament::badge>
                                        </div>
                                    </div>
                                @empty
                                    <div style="text-align: center; padding: 1.5rem; opacity: 0.6; font-size: 0.85rem;">
                                        No assignments created for classes in {{ $selectedSchool->name }}.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
            @endif

            <!-- CLASS DETAILS MODAL / POPUP OVERLAY (z-index: 99999) -->
            @if($selectedClass && !$selectedSubmission && !$selectedLesson)
                <div class="so-modal-overlay" style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.75); backdrop-filter: blur(6px); padding: 1rem;" wire:click.self="closeDetails">
                    <div class="so-modal" style="background: #18181b; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 0.75rem; max-width: 650px; width: 100%; max-height: 85vh; overflow-y: auto; padding: 1.5rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); color: #f4f4f5;" @click.stop>
                        
                        <div class="so-modal-header" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 1rem; margin-bottom: 1rem;">
                            <div class="so-modal-title-wrap" style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; background-color: rgba(59, 130, 246, 0.2); color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                                    <x-filament::icon icon="heroicon-o-building-library" style="width: 1.5rem; height: 1.5rem;" />
                                </div>
                                <div>
                                    <h3 style="font-size: 1.125rem; font-weight: 700; margin: 0; color: #ffffff;">
                                        {{ $selectedClass->name }}
                                    </h3>
                                    <p style="font-size: 0.8rem; opacity: 0.7; margin: 0;">
                                        {{ $selectedClass->grade->school->name ?? 'School' }} &bull; {{ $selectedClass->grade->name ?? 'Grade' }}
                                    </p>
                                </div>
                            </div>
                            <button type="button" wire:click="closeDetails" style="background: transparent; border: none; color: #a1a1aa; cursor: pointer; font-size: 1.25rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                                &times;
                            </button>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div class="so-modal-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; background: rgba(255, 255, 255, 0.05); padding: 0.75rem; border-radius: 0.5rem; font-size: 0.85rem;">
                                <div><strong>Medium:</strong> {{ ucfirst($selectedClass->medium ?? 'English') }}</div>
                                <div><strong>Status:</strong> {{ $selectedClass->is_active ? 'Active' : 'Inactive' }}</div>
                                <div><strong>Total Students:</strong> {{ $selectedClass->students_count }}</div>
                                <div><strong>Total Teachers:</strong> {{ $selectedClass->teachers_count }}</div>
                            </div>

                            <div>
                                <h4 style="font-size: 0.9rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #3b82f6;">Assigned Teachers</h4>
                                @forelse($selectedClass->teachers as $teacher)
                                    <div class="so-modal-list-row" style="padding: 0.4rem 0.75rem; border-radius: 0.375rem; background: rgba(255, 255, 255, 0.03); margin-bottom: 0.35rem; font-size: 0.85rem; display: flex; justify-content: space-between;">
                                        <span>{{ $teacher->user->name ?? 'Teacher' }}</span>
                                        <span style="opacity: 0.7;">{{ $teacher->user->email ?? '' }}</span>
                                    </div>
                                @empty
                                    <p style="font-size: 0.8rem; opacity: 0.6; margin: 0;">No teachers assigned to this class.</p>
                                @endforelse
                            </div>

                            <!-- Assigned Lessons with View Lessons & Materials 📚 Buttons -->
                            <div>
                                <h4 style="font-size: 0.9rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #06b6d4;">Assigned Lessons & Materials</h4>
                                @forelse($selectedClass->lessons as $les)
                                    <div class="so-modal-list-row" style="padding: 0.5rem 0.75rem; border-radius: 0.375rem; background: rgba(255, 255, 255, 0.03); margin-bottom: 0.35rem; font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(6, 182, 212, 0.2);">
                                        <div>
                                            <div style="font-weight: 600; color: #ffffff;">{{ $les->title }}</div>
                                            <div style="font-size: 0.75rem; opacity: 0.7;">Materials: {{ $les->attachments->count() }} Files</div>
                                        </div>
                                        <button
                                            type="button"
                                            wire:click.stop="selectLesson({{ $les->id }})"
                                            style="padding: 0.3rem 0.7rem; border-radius: 0.375rem; background: linear-gradient(135deg, #06b6d4, #3b82f6); color: white; border: none; font-size: 0.75rem; font-weight: 700; cursor: pointer;"
                                        >
                                            View Lessons & Materials 📚
                                        </button>
                                    </div>
                                @empty
                                    <p style="font-size: 0.8rem; opacity: 0.6; margin: 0;">No lessons listed for this class.</p>
                                @endforelse
                            </div>

                            <div>
                                <h4 style="font-size: 0.9rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #10b981;">Assignments</h4>
                                @forelse($selectedClass->assignments as $assign)
                                    <div class="so-modal-list-row" style="padding: 0.5rem 0.75rem; border-radius: 0.375rem; background: rgba(255, 255, 255, 0.03); margin-bottom: 0.35rem; font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <div style="font-weight: 600;">{{ $assign->title }}</div>
                                            <div style="font-size: 0.75rem; opacity: 0.7;">Max Score: {{ $assign->max_score ?? 100 }}</div>
                                        </div>
                                        <button
                                            type="button"
                                            wire:click.stop="selectAssignment({{ $assign->id }})"
                                            style="padding: 0.25rem 0.6rem; border-radius: 0.375rem; background: #10b981; color: white; border: none; font-size: 0.75rem; font-weight: 600; cursor: pointer;"
                                        >
                                            View Assignment
                                        </button>
                                    </div>
                                @empty
                                    <p style="font-size: 0.8rem; opacity: 0.6; margin: 0;">No assignments listed for this class.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="so-modal-footer" style="margin-top: 1.5rem; text-align: right; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 1rem;">
                            <button type="button" wire:click="closeDetails" style="padding: 0.5rem 1.25rem; border-radius: 0.375rem; background: #3f3f46; color: white; border: none; cursor: pointer; font-size: 0.875rem; font-weight: 600;">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- ASSIGNMENT DETAILS MODAL / POPUP OVERLAY (z-index: 99999) -->
            @if($selectedAssignment && !$selectedSubmission && !$selectedLesson)
                <div class="so-modal-overlay" style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.75); backdrop-filter: blur(6px); padding: 1rem;" wire:click.self="closeDetails">
                    <div class="so-modal" style="background: #18181b; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 0.75rem; max-width: 650px; width: 100%; max-height: 85vh; overflow-y: auto; padding: 1.5rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); color: #f4f4f5;" @click.stop>
                        
                        <div class="so-modal-header" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 1rem; margin-bottom: 1rem;">
                            <div class="so-modal-title-wrap" style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; background-color: rgba(16, 185, 129, 0.2); color: #10b981; display: flex; align-items: center; justify-content: center;">
                                    <x-filament::icon icon="heroicon-o-clipboard-document-list" style="width: 1.5rem; height: 1.5rem;" />
                                </div>
                                <div>
                                    <h3 style="font-size: 1.125rem; font-weight: 700; margin: 0; color: #ffffff;">
                                        {{ $selectedAssignment->title }}
                                    </h3>
                                    <p style="font-size: 0.8rem; opacity: 0.7; margin: 0;">
                                        Class: {{ $selectedAssignment->learningClass->name ?? 'N/A' }} &bull; Teacher: {{ $selectedAssignment->teacher->user->name ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <button type="button" wire:click="closeDetails" style="background: transparent; border: none; color: #a1a1aa; cursor: pointer; font-size: 1.25rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                                &times;
                            </button>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div class="so-modal-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; background: rgba(255, 255, 255, 0.05); padding: 0.75rem; border-radius: 0.5rem; font-size: 0.85rem;">
                                <div><strong>Status:</strong> {{ $selectedAssignment->is_published ? 'Published' : 'Draft' }}</div>
                                <div><strong>Max Score:</strong> {{ $selectedAssignment->max_score ?? 100 }}</div>
                                <div><strong>Submissions:</strong> {{ $selectedAssignment->submissions_count }}</div>
                                <div><strong>Availability:</strong> {{ ucfirst($selectedAssignment->availability_type ?? 'Always Available') }}</div>
                            </div>

                            <!-- Student Submissions List with PURPLE "View Submission 📄" Buttons -->
                            <div>
                                <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #a855f7;">Student Submissions</h4>
                                @forelse($selectedAssignment->submissions as $sub)
                                    <div class="so-modal-list-row" style="padding: 0.6rem 0.85rem; border-radius: 0.375rem; background: rgba(255, 255, 255, 0.04); margin-bottom: 0.4rem; font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(168, 85, 247, 0.2);">
                                        <div>
                                            <div style="font-weight: 600; color: #ffffff;">{{ $sub->student->user->name ?? 'Student' }}</div>
                                            <div style="font-size: 0.75rem; opacity: 0.7;">
                                                Status: <span style="color: #4ade80;">{{ ucfirst($sub->status) }}</span> &bull; 
                                                Submitted: {{ $sub->submitted_at ? $sub->submitted_at->format('M j, Y h:i A') : 'N/A' }}
                                            </div>
                                        </div>
                                        <!-- PURPLE View Submission 📄 Button -->
                                        <button
                                            type="button"
                                            wire:click.stop="selectSubmission({{ $sub->id }})"
                                            style="
                                                padding: 0.35rem 0.85rem;
                                                border-radius: 0.375rem;
                                                background: linear-gradient(135deg, #7c3aed, #6366f1);
                                                color: white;
                                                border: none;
                                                font-size: 0.775rem;
                                                font-weight: 700;
                                                cursor: pointer;
                                                box-shadow: 0 2px 4px rgba(124, 58, 237, 0.3);
                                                display: flex;
                                                align-items: center;
                                                gap: 0.3rem;
                                            "
                                        >
                                            View Submission 📄
                                        </button>
                                    </div>
                                @empty
                                    <p style="font-size: 0.8rem; opacity: 0.6; margin: 0;">No student submissions recorded for this assignment yet.</p>
                                @endforelse
                            </div>

                        </div>

                        <div class="so-modal-footer" style="margin-top: 1.5rem; text-align: right; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 1rem;">
                            <button type="button" wire:click="closeDetails" style="padding: 0.5rem 1.25rem; border-radius: 0.375rem; background: #3f3f46; color: white; border: none; cursor: pointer; font-size: 0.875rem; font-weight: 600;">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- LESSON & MATERIALS DETAILS MODAL / POPUP OVERLAY (z-index: 999999) -->
            @if($selectedLesson)
                <div class="so-modal-overlay" style="position: fixed; inset: 0; z-index: 999999; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.8); backdrop-filter: blur(6px); padding: 1rem;" wire:click.self="closeDetails">
                    <div class="so-modal" style="background: #18181b; border: 1px solid rgba(6, 182, 212, 0.5); border-radius: 0.75rem; max-width: 700px; width: 100%; max-height: 88vh; overflow-y: auto; padding: 1.5rem; box-shadow: 0 25px 50px -12px rgba(6, 182, 212, 0.4); color: #f4f4f5;" @click.stop>
                        
                        <!-- Modal Header -->
                        <div class="so-modal-header" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 1rem; margin-bottom: 1rem;">
                            <div class="so-modal-title-wrap" style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; background-color: rgba(6, 182, 212, 0.25); color: #06b6d4; display: flex; align-items: center; justify-content: center;">
                                    <x-filament::icon icon="heroicon-o-book-open" style="width: 1.5rem; height: 1.5rem;" />
                                </div>
                                <div>
                                    <h3 style="font-size: 1.125rem; font-weight: 700; margin: 0; color: #ffffff;">
                                        {{ $selectedLesson->title }}
                                    </h3>
                                    <p style="font-size: 0.8rem; opacity: 0.7; margin: 0;">
                                        Class: <strong>{{ $selectedLesson->learningClass->name ?? 'N/A' }}</strong> &bull; 
                                        Teacher: {{ $selectedLesson->teacher->user->name ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <button type="button" wire:click="closeDetails" style="background: transparent; border: none; color: #a1a1aa; cursor: pointer; font-size: 1.25rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                                &times;
                            </button>
                        </div>

                        <!-- Modal Body Content -->
                        <div style="display: flex; flex-direction: column; gap: 1.2rem;">
                            
                            <!-- Badges -->
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                                <x-filament::badge color="{{ $selectedLesson->is_published ? 'success' : 'gray' }}">
                                    Status: {{ $selectedLesson->is_published ? 'Published' : 'Draft' }}
                                </x-filament::badge>
                                
                                <x-filament::badge color="cyan">
                                    Materials: {{ $selectedLesson->attachments->count() }} Files
                                </x-filament::badge>

                                @if($selectedLesson->video_url)
                                    <a
                                        href="{{ $selectedLesson->video_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        style="padding: 0.25rem 0.6rem; border-radius: 0.375rem; background: rgba(239, 68, 68, 0.2); color: #f87171; font-size: 0.75rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 0.25rem;"
                                    >
                                        📺 Watch Video Lesson
                                    </a>
                                @endif
                            </div>

                            <!-- Description -->
                            @if($selectedLesson->description)
                                <div>
                                    <h4 style="font-size: 0.9rem; font-weight: 700; margin: 0 0 0.35rem 0; color: #06b6d4;">Overview</h4>
                                    <div style="padding: 0.75rem; border-radius: 0.375rem; background: rgba(255, 255, 255, 0.04); font-size: 0.85rem; line-height: 1.4;">
                                        {{ $selectedLesson->description }}
                                    </div>
                                </div>
                            @endif

                            <!-- Full Lesson Content -->
                            @if($selectedLesson->content)
                                <div>
                                    <h4 style="font-size: 0.9rem; font-weight: 700; margin: 0 0 0.35rem 0; color: #3b82f6;">Lesson Content & Notes</h4>
                                    <div style="padding: 0.85rem; border-radius: 0.5rem; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.875rem; line-height: 1.5;">
                                        {!! $selectedLesson->content !!}
                                    </div>
                                </div>
                            @endif

                            <!-- Learning Materials & Attachments -->
                            <div>
                                <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #06b6d4;">Downloadable Learning Materials</h4>
                                @forelse($selectedLesson->attachments as $material)
                                    <div class="so-modal-list-row" style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.85rem; border-radius: 0.375rem; background: rgba(255, 255, 255, 0.04); margin-bottom: 0.4rem; border: 1px solid rgba(6, 182, 212, 0.25);">
                                        <div>
                                            <div style="font-size: 0.85rem; font-weight: 600; color: #ffffff;">
                                                {{ $material->original_name ?? basename($material->file_path) }}
                                            </div>
                                            <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 0.1rem;">
                                                Size: {{ $material->file_size ? number_format($material->file_size / 1024, 1) . ' KB' : 'N/A' }} &bull; 
                                                Type: {{ $material->mime_type ?? 'File' }}
                                            </div>
                                        </div>
                                        @if($material->file_path)
                                            <a
                                                href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($material->file_path) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                style="
                                                    padding: 0.35rem 0.85rem;
                                                    border-radius: 0.375rem;
                                                    background: linear-gradient(135deg, #06b6d4, #3b82f6);
                                                    color: white;
                                                    text-decoration: none;
                                                    font-size: 0.775rem;
                                                    font-weight: 700;
                                                    box-shadow: 0 2px 4px rgba(6, 182, 212, 0.3);
                                                "
                                            >
                                                Download Material 📥
                                            </a>
                                        @endif
                                    </div>
                                @empty
                                    <p style="font-size: 0.8rem; opacity: 0.6; margin: 0;">No downloadable materials attached to this lesson.</p>
                                @endforelse
                            </div>

                        </div>

                        <!-- Modal Footer -->
                        <div class="so-modal-footer" style="margin-top: 1.5rem; text-align: right; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 1rem;">
                            <button type="button" wire:click="closeDetails" style="padding: 0.5rem 1.25rem; border-radius: 0.375rem; background: #06b6d4; color: white; border: none; cursor: pointer; font-size: 0.875rem; font-weight: 700;">
                                Close Lesson Details
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- SUBMISSION DETAILS MODAL / POPUP OVERLAY (z-index: 999999) -->
            @if($selectedSubmission)
                <div class="so-modal-overlay" style="position: fixed; inset: 0; z-index: 999999; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.8); backdrop-filter: blur(6px); padding: 1rem;" wire:click.self="closeDetails">
                    <div class="so-modal" style="background: #18181b; border: 1px solid rgba(168, 85, 247, 0.5); border-radius: 0.75rem; max-width: 700px; width: 100%; max-height: 88vh; overflow-y: auto; padding: 1.5rem; box-shadow: 0 25px 50px -12px rgba(124, 58, 237, 0.4); color: #f4f4f5;" @click.stop>
                        
                        <!-- Modal Header -->
                        <div class="so-modal-header" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 1rem; margin-bottom: 1rem;">
                            <div class="so-modal-title-wrap" style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; background-color: rgba(124, 58, 237, 0.25); color: #a855f7; display: flex; align-items: center; justify-content: center;">
                                    <x-filament::icon icon="heroicon-o-document-text" style="width: 1.5rem; height: 1.5rem;" />
                                </div>
                                <div>
                                    <h3 style="font-size: 1.125rem; font-weight: 700; margin: 0; color: #ffffff;">
                                        Submission Details — {{ $selectedSubmission->student->user->name ?? 'Student' }}
                                    </h3>
                                    <p style="font-size: 0.8rem; opacity: 0.7; margin: 0;">
                                        Assignment: <strong>{{ $selectedSubmission->assignment->title ?? 'N/A' }}</strong> &bull; 
                                        Submitted: {{ $selectedSubmission->submitted_at ? $selectedSubmission->submitted_at->format('d M Y, h:i A') : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <button type="button" wire:click="closeDetails" style="background: transparent; border: none; color: #a1a1aa; cursor: pointer; font-size: 1.25rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                                &times;
                            </button>
                        </div>

                        <!-- Modal Body Content -->
                        <div style="display: flex; flex-direction: column; gap: 1.2rem;">
                            
                            <!-- Status Badges -->
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                                <x-filament::badge color="{{ match($selectedSubmission->status) { 'graded' => 'success', 'submitted' => 'info', 'returned' => 'warning', default => 'gray' } }}">
                                    Status: {{ ucfirst($selectedSubmission->status) }}
                                </x-filament::badge>
                                
                                <x-filament::badge color="{{ $selectedSubmission->is_late ? 'danger' : 'success' }}">
                                    {{ $selectedSubmission->is_late ? 'Late Submission' : 'On Time' }}
                                </x-filament::badge>

                                @if($selectedSubmission->score !== null)
                                    <x-filament::badge color="success">
                                        Score: {{ $selectedSubmission->score }} / {{ $selectedSubmission->assignment->max_score ?? 100 }}
                                    </x-filament::badge>
                                @endif
                            </div>

                            <!-- Student Answer Text -->
                            <div>
                                <h4 style="font-size: 0.9rem; font-weight: 700; margin: 0 0 0.4rem 0; color: #a855f7;">Student Response / Answer</h4>
                                <div style="padding: 0.85rem; border-radius: 0.5rem; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.875rem; line-height: 1.5;">
                                    {!! $selectedSubmission->content ?: '<em>No written response provided.</em>' !!}
                                </div>
                            </div>

                            <!-- Submitted Attachments -->
                            @if($selectedSubmission->attachments->count() > 0)
                                <div>
                                    <h4 style="font-size: 0.9rem; font-weight: 700; margin: 0 0 0.4rem 0; color: #3b82f6;">Submitted Attachments</h4>
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                        @foreach($selectedSubmission->attachments as $file)
                                            <div class="so-modal-list-row" style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.85rem; border-radius: 0.375rem; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1);">
                                                <div style="font-size: 0.85rem; font-weight: 600; color: #e4e4e7;">
                                                    {{ $file->original_name ?? basename($file->file_path) }}
                                                </div>
                                                @if($file->file_path)
                                                    <a
                                                        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($file->file_path) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        style="padding: 0.3rem 0.75rem; border-radius: 0.25rem; background: #3b82f6; color: white; text-decoration: none; font-size: 0.775rem; font-weight: 600;"
                                                    >
                                                        Download / View
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Teacher Feedback -->
                            @if($selectedSubmission->feedback)
                                <div>
                                    <h4 style="font-size: 0.9rem; font-weight: 700; margin: 0 0 0.4rem 0; color: #eab308;">Teacher Feedback</h4>
                                    <div style="padding: 0.85rem; border-radius: 0.5rem; background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.2); font-size: 0.875rem; line-height: 1.5; color: #fef08a;">
                                        {!! $selectedSubmission->feedback !!}
                                    </div>
                                </div>
                            @endif

                        </div>

                        <!-- Modal Footer -->
                        <div class="so-modal-footer" style="margin-top: 1.5rem; text-align: right; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 1rem;">
                            <button type="button" wire:click="closeDetails" style="padding: 0.5rem 1.25rem; border-radius: 0.375rem; background: #7c3aed; color: white; border: none; cursor: pointer; font-size: 0.875rem; font-weight: 700;">
                                Close Submission Details
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
