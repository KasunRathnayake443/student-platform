<x-filament-panels::page>
    @php
        $statCards = [
            ['label' => 'Schools', 'value' => $totalSchools, 'icon' => '🏫'],
            ['label' => 'Teachers', 'value' => $totalTeachers, 'icon' => '🧑‍🏫'],
            ['label' => 'Students', 'value' => $totalStudents, 'icon' => '🎒'],
            ['label' => 'Classes', 'value' => $totalClasses, 'icon' => '📚'],
        ];
    @endphp

    <style>
        .sa-dashboard {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .sa-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }
        .sa-stat-card {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            padding: 1.1rem 1.25rem;
            background: #fff;
            border: 1px solid #f0eef5;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            min-width: 0;
        }
        .sa-stat-icon {
            width: 2.9rem;
            height: 2.9rem;
            border-radius: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(217, 70, 239, 0.14) 0%, rgba(236, 72, 153, 0.14) 100%);
        }
        .sa-stat-copy { min-width: 0; }
        .sa-stat-value {
            display: block;
            font-size: 1.55rem;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.1;
        }
        .sa-stat-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #8b95a9;
            margin-top: 0.15rem;
        }

        .sa-school-body {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .sa-school-stats {
            display: flex;
            gap: 1.1rem;
            flex-wrap: wrap;
            padding: 0.85rem 1rem;
            background: #faf8fd;
            border: 1px solid #f3eef9;
            border-radius: 0.9rem;
        }
        .sa-school-stat { text-align: center; min-width: 5rem; }
        .sa-school-stat-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #86198f;
        }
        .sa-school-stat-label {
            font-size: 0.72rem;
            font-weight: 600;
            color: #8b95a9;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .sa-school-action {
            margin-left: auto;
            display: flex;
            align-items: center;
        }
        .sa-open-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.6rem 1.1rem;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #d946ef 0%, #c026d3 100%);
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            box-shadow: 0 8px 18px -8px rgba(217, 70, 239, 0.6);
        }

        @media (max-width: 900px) {
            .sa-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 640px) {
            .sa-stat-grid {
                gap: 0.65rem;
            }
            .sa-stat-card {
                gap: 0.7rem;
                padding: 0.85rem 0.9rem;
                border-radius: 0.85rem;
            }
            .sa-stat-icon {
                width: 2.4rem;
                height: 2.4rem;
                border-radius: 0.7rem;
                font-size: 1.15rem;
            }
            .sa-stat-value { font-size: 1.3rem; }
            .sa-stat-label { font-size: 0.7rem; }

            .sa-school-body {
                flex-direction: column;
                align-items: stretch;
                gap: 0.8rem;
            }
            .sa-school-stats {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
                padding: 0.75rem;
            }
            .sa-school-stat { min-width: 0; }
            .sa-school-action { margin-left: 0; }
            .sa-open-btn {
                width: 100%;
                padding: 0.75rem 1rem;
            }
        }

        @media (max-width: 380px) {
            .sa-stat-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="sa-dashboard">
        <div class="sa-stat-grid">
            @foreach ($statCards as $card)
                <div class="sa-stat-card">
                    <span class="sa-stat-icon" aria-hidden="true">{{ $card['icon'] }}</span>
                    <span class="sa-stat-copy">
                        <span class="sa-stat-value">{{ $card['value'] }}</span>
                        <span class="sa-stat-label">{{ $card['label'] }}</span>
                    </span>
                </div>
            @endforeach
        </div>

        @if ($schools->isEmpty())
            <x-filament::section>
                <div style="text-align:center;padding:3rem 1rem;">
                    <div style="font-size:2.2rem;">🏫</div>
                    <h3 style="font-size:1.05rem;font-weight:700;color:#334155;margin-top:0.75rem;">No schools assigned yet</h3>
                    <p style="font-size:0.9rem;color:#8b95a9;margin-top:0.4rem;max-width:44ch;margin-left:auto;margin-right:auto;">
                        You must be assigned to one or more schools by a super administrator before you can manage anything here.
                    </p>
                </div>
            </x-filament::section>
        @else
            @foreach ($schools as $school)
                @php
                    $schoolStats = [
                        ['label' => 'Grades', 'value' => $school->grades_count],
                        ['label' => 'Classes', 'value' => $school->classes_count],
                        ['label' => 'Teachers', 'value' => $school->teachers_count],
                        ['label' => 'Students', 'value' => $school->students_count],
                    ];
                @endphp

                <x-filament::section :heading="$school->name" :description="$school->code">
                    <div class="sa-school-body">
                        <div class="sa-school-stats">
                            @foreach ($schoolStats as $stat)
                                <div class="sa-school-stat">
                                    <div class="sa-school-stat-value">{{ $stat['value'] }}</div>
                                    <div class="sa-school-stat-label">{{ $stat['label'] }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="sa-school-action">
                            <a href="{{ \App\Filament\SchoolAdmin\Resources\Schools\SchoolResource::getUrl('view', ['record' => $school]) }}"
                               class="sa-open-btn">
                                Open School
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </div>
                </x-filament::section>
            @endforeach
        @endif
    </div>
</x-filament-panels::page>
