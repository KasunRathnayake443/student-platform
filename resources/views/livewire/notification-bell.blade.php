<div class="nb-ctn"
     wire:poll.30s>
    <a href="{{ $notificationsUrl }}"
       class="nb-bell"
       title="Notifications"
       aria-label="Notifications">
        <svg class="nb-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if ($unread > 0)
            <span class="nb-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif
    </a>
</div>

@once
    <style>
        .nb-ctn{position:relative;display:inline-flex;align-items:center;}
        .nb-bell{position:relative;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:9px;color:#64748b;border:1px solid rgba(0,0,0,.06);background:rgba(255,255,255,.9);text-decoration:none;transition:all .13s ease;}
        .nb-bell:hover{background:#f8fafc;color:#0f172a;}
        .nb-icon{width:18px;height:18px;}
        .nb-badge{position:absolute;top:-5px;right:-5px;min-width:17px;height:17px;padding:0 4px;border-radius:999px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;line-height:17px;text-align:center;box-shadow:0 0 0 2px #fff;}
    </style>
@endonce