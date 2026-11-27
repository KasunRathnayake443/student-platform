<x-filament-panels::page>
    <div class="sr-tabs">
        <button type="button" wire:click="switchTab('submissions')" class="sr-tab {{ $activeTab === 'submissions' ? 'sr-tab-active' : '' }}">
            Submissions
        </button>
        <button type="button" wire:click="switchTab('settings')" class="sr-tab {{ $activeTab === 'settings' ? 'sr-tab-active' : '' }}">
            Registration Settings
        </button>
    </div>

    @if($activeTab === 'submissions')
        {{ $this->table }}
    @else
        {{ $this->content }}
    @endif

    <style>
        .sr-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
            padding: 0.375rem;
            border-radius: 0.75rem;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }
        .dark .sr-tabs { background: #18181b; }

        .sr-tab {
            appearance: none;
            border: 0;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.25rem;
            color: #52525b;
            background: transparent;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        .dark .sr-tab { color: #d4d4d8; }
        .sr-tab:hover { background: #f4f4f5; }
        .dark .sr-tab:hover { background: #27272a; }

        .sr-tab-active,
        .sr-tab-active:hover {
            color: #fff;
            background: var(--primary-500, #f59e0b);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.18);
        }
        .dark .sr-tab-active,
        .dark .sr-tab-active:hover { color: #fff; }
    </style>
</x-filament-panels::page>
