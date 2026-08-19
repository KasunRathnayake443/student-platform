@php
    use Illuminate\Support\Carbon;
    $userName = $student?->user?->name ?? 'Student';
    $firstName = explode(' ', $userName)[0];
@endphp

@if ($tier === 'kids')
    @include('filament.student.pages.kids-dashboard', [
        'student'       => $student,
        'firstName'     => $firstName,
        'activeContext' => $activeContext,
        'allContexts'   => $allContexts,
    ])
@elseif ($tier === 'junior')
    @include('filament.student.pages.junior-dashboard', [
        'student'       => $student,
        'firstName'     => $firstName,
        'activeContext' => $activeContext,
        'allContexts'   => $allContexts,
    ])
@else
    @include('filament.student.pages.senior-dashboard', [
        'student'       => $student,
        'firstName'     => $firstName,
        'activeContext' => $activeContext,
        'allContexts'   => $allContexts,
    ])
@endif
