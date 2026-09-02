<x-filament-panels::page>
    @php
        $statCards = [
            ['label' => 'Schools', 'value' => $totalSchools, 'icon' => '🏫', 'accent' => 'from-fuchsia-500 to-pink-500'],
            ['label' => 'Teachers', 'value' => $totalTeachers, 'icon' => '🧑‍🏫', 'accent' => 'from-indigo-500 to-violet-500'],
            ['label' => 'Students', 'value' => $totalStudents, 'icon' => '🎒', 'accent' => 'from-emerald-500 to-teal-500'],
            ['label' => 'Classes', 'value' => $totalClasses, 'icon' => '📚', 'accent' => 'from-amber-500 to-orange-500'],
        ];
    @endphp

    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1.5rem;">
        @foreach ($statCards as $card)
            <div style="display:flex;align-items:center;gap:0.9rem;padding:1.1rem 1.25rem;background:#fff;border:1px solid #f0eef5;border-radius:1rem;box-shadow:0 1px 3px rgba(15,23,42,0.05);">
                <span style="width:2.9rem;height:2.9rem;border-radius:0.85rem;display:flex;align-items:center;justify-content:center;font-size:1.35rem;background:linear-gradient(135deg, rgba(217,70,239,0.14) 0%, rgba(236,72,153,0.14) 100%);">
                    {{ $card['icon'] }}
                </span>
                <span>
                    <span style="display:block;font-size:1.55rem;font-weight:800;color:#1e293b;line-height:1.1;">{{ $card['value'] }}</span>
                    <span style="display:block;font-size:0.78rem;font-weight:600;color:#8b95a9;margin-top:0.15rem;">{{ $card['label'] }}</span>
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
            <x-filament::section :heading="$school->name" :description="$school->code">
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <div style="display:flex;gap:1.1rem;flex-wrap:wrap;padding:0.85rem 1rem;background:#faf8fd;border:1px solid #f3eef9;border-radius:0.9rem;">
                        <div style="text-align:center;min-width:5rem;">
                            <div style="font-size:1.3rem;font-weight:800;color:#86198f;">{{ $school->grades_count }}</div>
                            <div style="font-size:0.72rem;font-weight:600;color:#8b95a9;text-transform:uppercase;letter-spacing:0.06em;">Grades</div>
                        </div>
                        <div style="text-align:center;min-width:5rem;">
                            <div style="font-size:1.3rem;font-weight:800;color:#86198f;">{{ $school->classes_count }}</div>
                            <div style="font-size:0.72rem;font-weight:600;color:#8b95a9;text-transform:uppercase;letter-spacing:0.06em;">Classes</div>
                        </div>
                        <div style="text-align:center;min-width:5rem;">
                            <div style="font-size:1.3rem;font-weight:800;color:#86198f;">{{ $school->teachers_count }}</div>
                            <div style="font-size:0.72rem;font-weight:600;color:#8b95a9;text-transform:uppercase;letter-spacing:0.06em;">Teachers</div>
                        </div>
                        <div style="text-align:center;min-width:5rem;">
                            <div style="font-size:1.3rem;font-weight:800;color:#86198f;">{{ $school->students_count }}</div>
                            <div style="font-size:0.72rem;font-weight:600;color:#8b95a9;text-transform:uppercase;letter-spacing:0.06em;">Students</div>
                        </div>
                    </div>

                    <div style="margin-left:auto;display:flex;align-items:center;">
                        <a href="{{ \App\Filament\SchoolAdmin\Resources\Schools\SchoolResource::getUrl('view', ['record' => $school]) }}"
                           style="display:inline-flex;align-items:center;gap:0.45rem;padding:0.6rem 1.1rem;border-radius:0.75rem;background:linear-gradient(135deg,#d946ef 0%,#c026d3 100%);color:#fff;font-weight:700;font-size:0.85rem;text-decoration:none;box-shadow:0 8px 18px -8px rgba(217,70,239,0.6);">
                            Open School
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            </x-filament::section>
        @endforeach
    @endif
</x-filament-panels::page>