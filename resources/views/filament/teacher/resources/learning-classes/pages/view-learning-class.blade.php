<x-teacher-layout title="{{ $record->name }}">
    <div style="padding: clamp(12px, 3vw, 24px); max-width: 1200px; margin: 0 auto; height: 100%; overflow-y: auto; width: 100%;">
        
        <!-- Header -->
        <header style="margin-bottom: 20px;">
            <h1 style="font-size: clamp(18px, 4vw, 24px); font-weight: 700; color: #0f172a;">{{ $record->name }}</h1>
        </header>

        <!-- Filament Page Content (Infolist & Relation Managers) -->
        <div style="display: flex; flex-direction: column; gap: 32px;">
            {{ $this->infolist }}

            @if (count($relationManagers = $this->getRelationManagers()))
                <x-filament-panels::resources.relation-managers
                    :active-manager="$this->activeRelationManager"
                    :managers="$relationManagers"
                    :owner-record="$record"
                    :page-class="static::class"
                />
            @endif
        </div>

    </div>
</x-teacher-layout>
