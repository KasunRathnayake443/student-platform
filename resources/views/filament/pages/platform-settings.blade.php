<x-filament-panels::page>
    <div>
        <div class="ps-intro">
            <div class="ps-intro-icon">
                <x-filament::icon icon="heroicon-o-cog-6-tooth" class="h-6 w-6" />
            </div>
            <div>
                <h1>Platform Settings</h1>
                <p>Control the global platform identity and the sender used for password reset emails.</p>
            </div>
        </div>

        {{ $this->content }}
    </div>

    <style>
        .ps-intro {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: linear-gradient(135deg, #b45309, #d97706 55%, #f59e0b);
            color: #fff;
            border-radius: 1.1rem;
            padding: 1.3rem 1.6rem;
            margin-bottom: 1.4rem;
            box-shadow: 0 8px 24px -8px rgba(180, 83, 9, 0.5);
        }
        .ps-intro-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.16);
        }
        .ps-intro h1 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 800;
        }
        .ps-intro p {
            margin: 0.15rem 0 0;
            font-size: 0.85rem;
            opacity: 0.9;
        }
    </style>
</x-filament-panels::page>