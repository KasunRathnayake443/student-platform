@php
    $paymentLabels = [
        'bank_transfer' => 'Bank Transfer / Deposit',
        'ezcash' => 'Dialog eZ Cash',
        'mcash' => 'Mobitel mCash',
    ];
    $rows = [
        'Full Name' => $record->full_name,
        'Institution' => $record->institution,
        'Grade' => $record->grade,
        'Language' => $record->language,
        'Email' => $record->email,
        'Phone Number' => $record->phone,
        'Referred By' => $record->referred_by ?: '—',
        'Payment Method' => $paymentLabels[$record->payment_method] ?? $record->payment_method,
        'Payment Date' => $record->payment_date?->format('Y-m-d'),
        'Transaction Reference / Slip No.' => $record->transaction_reference,
        'Status' => ucfirst($record->status),
        'Submitted' => $record->created_at?->format('Y-m-d H:i'),
    ];
@endphp

<div class="srd-details">
    <dl class="srd-list">
        @foreach($rows as $label => $value)
            <div class="srd-row">
                <dt class="srd-label">{{ $label }}</dt>
                <dd class="srd-value">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>

    @if(filled($record->receipt_path))
        <div class="srd-receipt">
            <span class="srd-label">Receipt / Slip:</span>
            <a class="srd-link" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($record->receipt_path) }}" target="_blank" rel="noopener noreferrer">Open uploaded file ↗</a>
        </div>
    @else
        <p class="srd-empty">No receipt was uploaded for this registration.</p>
    @endif
</div>

<style>
    .srd-details { display: flex; flex-direction: column; gap: 0.75rem; }
    .srd-list { margin: 0; }
    .srd-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.25rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e4e4e7;
    }
    .dark .srd-row { border-bottom-color: #3f3f46; }
    .srd-label { font-size: 0.875rem; font-weight: 600; color: #52525b; }
    .dark .srd-label { color: #d4d4d8; }
    .srd-value { margin: 0; font-size: 0.875rem; color: #18181b; }
    .dark .srd-value { color: #f4f4f5; }
    .srd-receipt {
        border-radius: 0.5rem;
        background: #f4f4f5;
        padding: 0.75rem;
        font-size: 0.875rem;
    }
    .dark .srd-receipt { background: #27272a; }
    .srd-link { color: var(--primary-600, #d97706); text-decoration: underline; }
    .srd-link:hover { text-decoration: none; }
    .srd-empty { margin: 0; font-size: 0.875rem; color: #71717a; }

    @media (min-width: 640px) {
        .srd-row { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .srd-value { grid-column: span 2 / span 2; }
    }
</style>
