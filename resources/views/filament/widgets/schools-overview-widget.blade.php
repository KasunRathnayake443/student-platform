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
        .fi-schools-overview-widget .so-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
        }

        .dark .fi-schools-overview-widget .so-modal-overlay {
            background: rgba(0, 0, 0, 0.7);
        }

        .fi-schools-overview-widget .so-modal {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 700px;
            max-height: 88vh;
            overflow: hidden;
            border: 1px solid rgba(156, 163, 175, 0.22);
            border-radius: 0.85rem;
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 25px 60px -20px rgba(15, 23, 42, 0.35);
        }

        .dark .fi-schools-overview-widget .so-modal {
            border-color: rgba(255, 255, 255, 0.12);
            background: #18181b;
            color: #f4f4f5;
            box-shadow: 0 25px 60px -20px rgba(0, 0, 0, 0.65);
        }

        .fi-schools-overview-widget .so-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-shrink: 0;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(156, 163, 175, 0.18);
        }

        .dark .fi-schools-overview-widget .so-modal-header {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        .fi-schools-overview-widget .so-modal-title-wrap {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
        }

        .fi-schools-overview-widget .so-modal-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.35rem;
            height: 2.35rem;
            flex-shrink: 0;
            border-radius: 0.65rem;
            background: rgba(217, 119, 6, 0.12);
            color: #d97706;
        }

        .dark .fi-schools-overview-widget .so-modal-icon {
            background: rgba(251, 191, 36, 0.16);
            color: #fbbf24;
        }

        .fi-schools-overview-widget .so-modal-icon--blue {
            background: rgba(59, 130, 246, 0.12);
            color: #2563eb;
        }

        .dark .fi-schools-overview-widget .so-modal-icon--blue {
            background: rgba(96, 165, 250, 0.16);
            color: #93c5fd;
        }

        .fi-schools-overview-widget .so-modal-icon--green {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
        }

        .dark .fi-schools-overview-widget .so-modal-icon--green {
            background: rgba(52, 211, 153, 0.16);
            color: #6ee7b7;
        }

        .fi-schools-overview-widget .so-modal-icon--cyan {
            background: rgba(6, 182, 212, 0.12);
            color: #0891b2;
        }

        .dark .fi-schools-overview-widget .so-modal-icon--cyan {
            background: rgba(34, 211, 238, 0.16);
            color: #67e8f9;
        }

        .fi-schools-overview-widget .so-modal-icon--purple {
            background: rgba(168, 85, 247, 0.12);
            color: #9333ea;
        }

        .dark .fi-schools-overview-widget .so-modal-icon--purple {
            background: rgba(192, 132, 252, 0.16);
            color: #d8b4fe;
        }

        .fi-schools-overview-widget .so-modal-title-wrap h3 {
            margin: 0;
            color: inherit;
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .fi-schools-overview-widget .so-modal-title-wrap p {
            margin: 0.2rem 0 0;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 500;
            line-height: 1.45;
        }

        .dark .fi-schools-overview-widget .so-modal-title-wrap p {
            color: #a1a1aa;
        }

        .fi-schools-overview-widget .so-modal-close {
            flex-shrink: 0;
        }

        .fi-schools-overview-widget .so-modal-body {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            overflow-y: auto;
            padding: 1.5rem;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .dark .fi-schools-overview-widget .so-modal-body {
            scrollbar-color: #52525b transparent;
        }

        .fi-schools-overview-widget .so-modal-section {
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
        }

        .fi-schools-overview-widget .so-modal-section-title {
            margin: 0;
            color: #475569;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .dark .fi-schools-overview-widget .so-modal-section-title {
            color: #d4d4d8;
        }

        .fi-schools-overview-widget .so-modal-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.7rem;
            padding: 1rem;
            border: 1px solid rgba(156, 163, 175, 0.16);
            border-radius: 0.65rem;
            background: rgba(156, 163, 175, 0.08);
            color: inherit;
            font-size: 0.82rem;
            line-height: 1.45;
        }

        .dark .fi-schools-overview-widget .so-modal-grid {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
        }

        .fi-schools-overview-widget .so-modal-grid > div {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .fi-schools-overview-widget .so-modal-grid strong {
            color: #334155;
            font-weight: 700;
        }

        .dark .fi-schools-overview-widget .so-modal-grid strong {
            color: #e4e4e7;
        }

        .fi-schools-overview-widget .so-modal-list {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .fi-schools-overview-widget .so-modal-list-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            min-width: 0;
            padding: 0.7rem 0.85rem;
            border: 1px solid rgba(156, 163, 175, 0.14);
            border-radius: 0.55rem;
            background: rgba(156, 163, 175, 0.06);
            color: inherit;
            font-size: 0.82rem;
            line-height: 1.45;
        }

        .dark .fi-schools-overview-widget .so-modal-list-row {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
        }

        .fi-schools-overview-widget .so-modal-list-row > div {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .fi-schools-overview-widget .so-modal-list-title {
            color: #1e293b;
            font-weight: 600;
        }

        .dark .fi-schools-overview-widget .so-modal-list-title {
            color: #f4f4f5;
        }

        .fi-schools-overview-widget .so-modal-list-meta {
            margin-top: 0.16rem;
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 500;
            line-height: 1.45;
        }

        .dark .fi-schools-overview-widget .so-modal-list-meta {
            color: #a1a1aa;
        }

        .fi-schools-overview-widget .so-modal-empty {
            margin: 0;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .dark .fi-schools-overview-widget .so-modal-empty {
            color: #a1a1aa;
        }

        .fi-schools-overview-widget .so-modal-content-box {
            padding: 0.9rem 1rem;
            border: 1px solid rgba(156, 163, 175, 0.14);
            border-radius: 0.6rem;
            background: rgba(156, 163, 175, 0.06);
            color: #334155;
            font-size: 0.84rem;
            line-height: 1.55;
            overflow-wrap: anywhere;
        }

        .dark .fi-schools-overview-widget .so-modal-content-box {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: #d4d4d8;
        }

        .fi-schools-overview-widget .so-modal-content-box > :first-child {
            margin-top: 0;
        }

        .fi-schools-overview-widget .so-modal-content-box > :last-child {
            margin-bottom: 0;
        }

        .fi-schools-overview-widget .so-modal-feedback {
            border-color: rgba(234, 179, 8, 0.24);
            background: rgba(234, 179, 8, 0.08);
            color: #854d0e;
        }

        .dark .fi-schools-overview-widget .so-modal-feedback {
            border-color: rgba(234, 179, 8, 0.24);
            background: rgba(234, 179, 8, 0.1);
            color: #fef08a;
        }

        .fi-schools-overview-widget .so-text-success {
            color: #059669;
            font-weight: 600;
        }

        .dark .fi-schools-overview-widget .so-text-success {
            color: #34d399;
        }

        .fi-schools-overview-widget .so-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            flex-shrink: 0;
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(156, 163, 175, 0.18);
        }

        .dark .fi-schools-overview-widget .so-modal-footer {
            border-top-color: rgba(255, 255, 255, 0.1);
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
                padding: 0 !important;
                border-radius: 0.65rem !important;
            }
            .fi-schools-overview-widget .so-modal-header {
                align-items: flex-start !important;
                gap: 0.5rem;
                padding: 1rem !important;
            }
            .fi-schools-overview-widget .so-modal-body {
                padding: 1rem !important;
            }
            .fi-schools-overview-widget .so-modal-grid {
                grid-template-columns: 1fr !important;
            }
            .fi-schools-overview-widget .so-modal-list-row {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.5rem !important;
            }
            .fi-schools-overview-widget .so-modal-list-row > .fi-btn,
            .fi-schools-overview-widget .so-modal-list-row > button,
            .fi-schools-overview-widget .so-modal-list-row > a {
                width: 100%;
                justify-content: center;
                text-align: center;
            }
            .fi-schools-overview-widget .so-modal-footer {
                text-align: left !important;
                padding: 1rem !important;
            }
            .fi-schools-overview-widget .so-modal-footer > .fi-btn,
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
                                            <x-filament::badge size="xs" color="info" icon="heroicon-m-book-open">
                                                View Materials
                                            </x-filament::badge>
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
                <div class="so-modal-overlay" wire:click.self="closeDetails">
                    <div class="so-modal" @click.stop>
                        <div class="so-modal-header">
                            <div class="so-modal-title-wrap">
                                <div class="so-modal-icon so-modal-icon--blue">
                                    <x-filament::icon icon="heroicon-o-building-library" style="width: 1.35rem; height: 1.35rem;" />
                                </div>
                                <div>
                                    <h3>{{ $selectedClass->name }}</h3>
                                    <p>
                                        {{ $selectedClass->grade->school->name ?? 'School' }} &bull; {{ $selectedClass->grade->name ?? 'Grade' }}
                                    </p>
                                </div>
                            </div>

                            <x-filament::icon-button
                                icon="heroicon-o-x-mark"
                                color="gray"
                                size="sm"
                                label="Close class details"
                                wire:click="closeDetails"
                                class="so-modal-close"
                            />
                        </div>

                        <div class="so-modal-body">
                            <div class="so-modal-grid">
                                <div><strong>Medium:</strong> {{ ucfirst($selectedClass->medium ?? 'English') }}</div>
                                <div><strong>Status:</strong> {{ $selectedClass->is_active ? 'Active' : 'Inactive' }}</div>
                                <div><strong>Total Students:</strong> {{ $selectedClass->students_count }}</div>
                                <div><strong>Total Teachers:</strong> {{ $selectedClass->teachers_count }}</div>
                            </div>

                            <div class="so-modal-section">
                                <h4 class="so-modal-section-title">Assigned Teachers</h4>

                                <div class="so-modal-list">
                                    @forelse($selectedClass->teachers as $teacher)
                                        <div class="so-modal-list-row">
                                            <div>
                                                <div class="so-modal-list-title">{{ $teacher->user->name ?? 'Teacher' }}</div>
                                                <div class="so-modal-list-meta">{{ $teacher->user->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="so-modal-empty">No teachers assigned to this class.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="so-modal-section">
                                <h4 class="so-modal-section-title">Assigned Lessons & Materials</h4>

                                <div class="so-modal-list">
                                    @forelse($selectedClass->lessons as $les)
                                        <div class="so-modal-list-row">
                                            <div>
                                                <div class="so-modal-list-title">{{ $les->title }}</div>
                                                <div class="so-modal-list-meta">Materials: {{ $les->attachments->count() }} Files</div>
                                            </div>

                                            <x-filament::button
                                                type="button"
                                                size="xs"
                                                color="gray"
                                                outlined
                                                icon="heroicon-m-book-open"
                                                wire:click.stop="selectLesson({{ $les->id }})"
                                            >
                                                View materials
                                            </x-filament::button>
                                        </div>
                                    @empty
                                        <p class="so-modal-empty">No lessons listed for this class.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="so-modal-section">
                                <h4 class="so-modal-section-title">Assignments</h4>

                                <div class="so-modal-list">
                                    @forelse($selectedClass->assignments as $assign)
                                        <div class="so-modal-list-row">
                                            <div>
                                                <div class="so-modal-list-title">{{ $assign->title }}</div>
                                                <div class="so-modal-list-meta">Max Score: {{ $assign->max_score ?? 100 }}</div>
                                            </div>

                                            <x-filament::button
                                                type="button"
                                                size="xs"
                                                color="gray"
                                                outlined
                                                icon="heroicon-m-eye"
                                                wire:click.stop="selectAssignment({{ $assign->id }})"
                                            >
                                                View assignment
                                            </x-filament::button>
                                        </div>
                                    @empty
                                        <p class="so-modal-empty">No assignments listed for this class.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="so-modal-footer">
                            <x-filament::button type="button" color="gray" wire:click="closeDetails">
                                Close
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- ASSIGNMENT DETAILS MODAL / POPUP OVERLAY (z-index: 99999) -->
            @if($selectedAssignment && !$selectedSubmission && !$selectedLesson)
                <div class="so-modal-overlay" wire:click.self="closeDetails">
                    <div class="so-modal" @click.stop>
                        <div class="so-modal-header">
                            <div class="so-modal-title-wrap">
                                <div class="so-modal-icon so-modal-icon--green">
                                    <x-filament::icon icon="heroicon-o-clipboard-document-list" style="width: 1.35rem; height: 1.35rem;" />
                                </div>
                                <div>
                                    <h3>{{ $selectedAssignment->title }}</h3>
                                    <p>
                                        Class: {{ $selectedAssignment->learningClass->name ?? 'N/A' }} &bull; Teacher: {{ $selectedAssignment->teacher->user->name ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>

                            <x-filament::icon-button
                                icon="heroicon-o-x-mark"
                                color="gray"
                                size="sm"
                                label="Close assignment details"
                                wire:click="closeDetails"
                                class="so-modal-close"
                            />
                        </div>

                        <div class="so-modal-body">
                            <div class="so-modal-grid">
                                <div><strong>Status:</strong> {{ $selectedAssignment->is_published ? 'Published' : 'Draft' }}</div>
                                <div><strong>Max Score:</strong> {{ $selectedAssignment->max_score ?? 100 }}</div>
                                <div><strong>Submissions:</strong> {{ $selectedAssignment->submissions_count }}</div>
                                <div><strong>Availability:</strong> {{ ucfirst($selectedAssignment->availability_type ?? 'Always Available') }}</div>
                            </div>

                            <div class="so-modal-section">
                                <h4 class="so-modal-section-title">Student Submissions</h4>

                                <div class="so-modal-list">
                                    @forelse($selectedAssignment->submissions as $sub)
                                        <div class="so-modal-list-row">
                                            <div>
                                                <div class="so-modal-list-title">{{ $sub->student->user->name ?? 'Student' }}</div>
                                                <div class="so-modal-list-meta">
                                                    Status: <span class="so-text-success">{{ ucfirst($sub->status) }}</span> &bull;
                                                    Submitted: {{ $sub->submitted_at ? $sub->submitted_at->format('M j, Y h:i A') : 'N/A' }}
                                                </div>
                                            </div>

                                            <x-filament::button
                                                type="button"
                                                size="xs"
                                                color="gray"
                                                outlined
                                                icon="heroicon-m-document-text"
                                                wire:click.stop="selectSubmission({{ $sub->id }})"
                                            >
                                                View submission
                                            </x-filament::button>
                                        </div>
                                    @empty
                                        <p class="so-modal-empty">No student submissions recorded for this assignment yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="so-modal-footer">
                            <x-filament::button type="button" color="gray" wire:click="closeDetails">
                                Close
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- LESSON & MATERIALS DETAILS MODAL / POPUP OVERLAY (z-index: 999999) -->
            @if($selectedLesson)
                <div class="so-modal-overlay" wire:click.self="closeDetails">
                    <div class="so-modal" @click.stop>
                        <div class="so-modal-header">
                            <div class="so-modal-title-wrap">
                                <div class="so-modal-icon so-modal-icon--cyan">
                                    <x-filament::icon icon="heroicon-o-book-open" style="width: 1.35rem; height: 1.35rem;" />
                                </div>
                                <div>
                                    <h3>{{ $selectedLesson->title }}</h3>
                                    <p>
                                        Class: <strong>{{ $selectedLesson->learningClass->name ?? 'N/A' }}</strong> &bull;
                                        Teacher: {{ $selectedLesson->teacher->user->name ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>

                            <x-filament::icon-button
                                icon="heroicon-o-x-mark"
                                color="gray"
                                size="sm"
                                label="Close lesson details"
                                wire:click="closeDetails"
                                class="so-modal-close"
                            />
                        </div>

                        <div class="so-modal-body">
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                                <x-filament::badge color="{{ $selectedLesson->is_published ? 'success' : 'gray' }}">
                                    Status: {{ $selectedLesson->is_published ? 'Published' : 'Draft' }}
                                </x-filament::badge>

                                <x-filament::badge color="cyan">
                                    Materials: {{ $selectedLesson->attachments->count() }} Files
                                </x-filament::badge>

                                @if($selectedLesson->video_url)
                                    <x-filament::button
                                        tag="a"
                                        href="{{ $selectedLesson->video_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        size="xs"
                                        color="gray"
                                        outlined
                                        icon="heroicon-m-play"
                                    >
                                        Watch video lesson
                                    </x-filament::button>
                                @endif
                            </div>

                            @if($selectedLesson->description)
                                <div class="so-modal-section">
                                    <h4 class="so-modal-section-title">Overview</h4>
                                    <div class="so-modal-content-box">
                                        {{ $selectedLesson->description }}
                                    </div>
                                </div>
                            @endif

                            @if($selectedLesson->content)
                                <div class="so-modal-section">
                                    <h4 class="so-modal-section-title">Lesson Content & Notes</h4>
                                    <div class="so-modal-content-box">
                                        {!! $selectedLesson->content !!}
                                    </div>
                                </div>
                            @endif

                            <div class="so-modal-section">
                                <h4 class="so-modal-section-title">Downloadable Learning Materials</h4>

                                <div class="so-modal-list">
                                    @forelse($selectedLesson->attachments as $material)
                                        <div class="so-modal-list-row">
                                            <div>
                                                <div class="so-modal-list-title">
                                                    {{ $material->original_name ?? basename($material->file_path) }}
                                                </div>
                                                <div class="so-modal-list-meta">
                                                    Size: {{ $material->file_size ? number_format($material->file_size / 1024, 1) . ' KB' : 'N/A' }} &bull;
                                                    Type: {{ $material->mime_type ?? 'File' }}
                                                </div>
                                            </div>

                                            @if($material->file_path)
                                                <x-filament::button
                                                    tag="a"
                                                    href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($material->file_path) }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    size="xs"
                                                    color="gray"
                                                    outlined
                                                    icon="heroicon-m-arrow-down-tray"
                                                >
                                                    Download
                                                </x-filament::button>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="so-modal-empty">No downloadable materials attached to this lesson.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="so-modal-footer">
                            <x-filament::button type="button" color="gray" wire:click="closeDetails">
                                Close
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- SUBMISSION DETAILS MODAL / POPUP OVERLAY (z-index: 999999) -->
            @if($selectedSubmission)
                <div class="so-modal-overlay" wire:click.self="closeDetails">
                    <div class="so-modal" @click.stop>
                        <div class="so-modal-header">
                            <div class="so-modal-title-wrap">
                                <div class="so-modal-icon so-modal-icon--purple">
                                    <x-filament::icon icon="heroicon-o-document-text" style="width: 1.35rem; height: 1.35rem;" />
                                </div>
                                <div>
                                    <h3>Submission Details — {{ $selectedSubmission->student->user->name ?? 'Student' }}</h3>
                                    <p>
                                        Assignment: <strong>{{ $selectedSubmission->assignment->title ?? 'N/A' }}</strong> &bull;
                                        Submitted: {{ $selectedSubmission->submitted_at ? $selectedSubmission->submitted_at->format('d M Y, h:i A') : 'N/A' }}
                                    </p>
                                </div>
                            </div>

                            <x-filament::icon-button
                                icon="heroicon-o-x-mark"
                                color="gray"
                                size="sm"
                                label="Close submission details"
                                wire:click="closeDetails"
                                class="so-modal-close"
                            />
                        </div>

                        <div class="so-modal-body">
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

                            <div class="so-modal-section">
                                <h4 class="so-modal-section-title">Student Response / Answer</h4>
                                <div class="so-modal-content-box">
                                    {!! $selectedSubmission->content ?: '<em>No written response provided.</em>' !!}
                                </div>
                            </div>

                            @if($selectedSubmission->attachments->count() > 0)
                                <div class="so-modal-section">
                                    <h4 class="so-modal-section-title">Submitted Attachments</h4>

                                    <div class="so-modal-list">
                                        @foreach($selectedSubmission->attachments as $file)
                                            <div class="so-modal-list-row">
                                                <div class="so-modal-list-title">
                                                    {{ $file->original_name ?? basename($file->file_path) }}
                                                </div>

                                                @if($file->file_path)
                                                    <x-filament::button
                                                        tag="a"
                                                        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($file->file_path) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        size="xs"
                                                        color="gray"
                                                        outlined
                                                        icon="heroicon-m-arrow-down-tray"
                                                    >
                                                        Download / View
                                                    </x-filament::button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($selectedSubmission->feedback)
                                <div class="so-modal-section">
                                    <h4 class="so-modal-section-title">Teacher Feedback</h4>
                                    <div class="so-modal-content-box so-modal-feedback">
                                        {!! $selectedSubmission->feedback !!}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="so-modal-footer">
                            <x-filament::button type="button" color="gray" wire:click="closeDetails">
                                Close
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
