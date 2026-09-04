<x-filament-panels::page>
    <style>
        .nc-wrap{width:100%;}
        .nc-hero{
            display:flex;align-items:center;gap:1rem;
            background:linear-gradient(135deg,#4f46e5,#7c3aed 60%,#a855f7);
            color:#fff;border-radius:1.1rem;padding:1.4rem 1.6rem;
            box-shadow:0 8px 24px -8px rgba(124,58,237,.5);margin-bottom:1.4rem;
        }
        .nc-hero-icon{
            width:52px;height:52px;border-radius:14px;flex-shrink:0;
            display:flex;align-items:center;justify-content:center;font-size:1.6rem;
            background:rgba(255,255,255,.16);backdrop-filter:blur(4px);
        }
        .nc-hero-text{flex:1;min-width:0;}
        .nc-hero-text h1{margin:0;font-size:1.3rem;font-weight:800;}
        .nc-hero-text p{margin:.15rem 0 0;font-size:.85rem;opacity:.85;}
        .nc-hero-stat{
            flex-shrink:0;text-align:center;background:rgba(255,255,255,.14);
            border-radius:.8rem;padding:.5rem .9rem;min-width:74px;
        }
        .nc-hero-stat b{display:block;font-size:1.25rem;font-weight:800;line-height:1;}
        .nc-hero-stat span{font-size:.68rem;opacity:.85;text-transform:uppercase;letter-spacing:.04em;}

        .nc-grid{display:grid;grid-template-columns:minmax(320px,400px) minmax(0,1fr);gap:1.4rem;align-items:start;}

        .nc-card{background:#fff;border:1px solid #e8ecf3;border-radius:1rem;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.04);}
        .nc-card-head{padding:.9rem 1.2rem;border-bottom:1px solid #f1f5f9;font-size:.85rem;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:.5rem;}
        .nc-card-head .nc-badge-icon{color:#4f46e5;font-size:1.05rem;}
        .nc-card-body{padding:1.2rem;}
        .nc-card-body form{display:flex;flex-direction:column;gap:1rem;}

        .nc-list{display:flex;flex-direction:column;gap:.85rem;}
        .nc-item{
            display:flex;gap:1rem;align-items:flex-start;
            background:#fff;border:1px solid #e8ecf3;border-radius:1rem;
            padding:1.1rem 1.25rem;transition:all .18s ease;position:relative;
        }
        .nc-item:hover{border-color:#c7d2fe;box-shadow:0 6px 18px -8px rgba(79,70,229,.18);transform:translateY(-1px);}
        .nc-item.np-unread{background:#f8faff;border-color:#c7d2fe;border-left:4px solid #6366f1;}
        .nc-badge{
            width:42px;height:42px;border-radius:12px;flex-shrink:0;
            display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#fff;
        }
        .nc-badge.bg-primary{background:linear-gradient(135deg,#6366f1,#4f46e5);}
        .nc-badge.bg-success{background:linear-gradient(135deg,#10b981,#059669);}
        .nc-badge.bg-warning{background:linear-gradient(135deg,#f59e0b,#d97706);}
        .nc-badge.bg-danger{background:linear-gradient(135deg,#ef4444,#dc2626);}
        .nc-badge.bg-info{background:linear-gradient(135deg,#0ea5e9,#0284c7);}
        .nc-main{flex:1;min-width:0;}
        .nc-title{font-weight:700;color:#0f172a;font-size:.95rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
        .nc-unread-dot{width:8px;height:8px;border-radius:50%;background:#ef4444;flex-shrink:0;}
        .nc-meta{font-size:.75rem;color:#8b95a9;margin-top:.15rem;display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;}
        .nc-sender{font-weight:600;color:#64748b;}
        .nc-body{color:#475569;font-size:.85rem;margin-top:.45rem;white-space:pre-wrap;line-height:1.5;}
        .nc-mention{
            display:inline-flex;align-items:center;gap:.4rem;margin-top:.6rem;
            font-size:.75rem;font-weight:600;color:#4f46e5;background:#eef2ff;
            border:1px solid #e0e7ff;border-radius:999px;padding:.3rem .7rem;text-decoration:none;
        }
        .nc-mention:hover{background:#e0e7ff;}
        .nc-read-state{margin-top:.6rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
        .nc-chip{display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;font-weight:700;border-radius:999px;padding:.2rem .6rem;}
        .nc-chip.read{color:#059669;background:#ecfdf5;}
        .nc-chip.unread{color:#b45309;background:#fef3c7;}
        .nc-chip.count{color:#475569;background:#f1f5f9;}
        .nc-actions{display:flex;gap:.5rem;flex-shrink:0;}
        .nc-btn{
            border:1px solid #e8ecf3;border-radius:.6rem;padding:.5rem .8rem;
            font-size:.75rem;font-weight:600;color:#475569;background:#fff;cursor:pointer;text-decoration:none;transition:all .15s;
        }
        .nc-btn:hover{background:#f8fafc;color:#0f172a;}
        .nc-btn-danger{color:#dc2626;border-color:#fecaca;background:#fef2f2;}
        .nc-btn-danger:hover{background:#fee2e2;}
        .nc-empty{
            background:#fff;border:1px dashed #e2e8f0;border-radius:1rem;padding:3.5rem 1.5rem;
            text-align:center;color:#94a3b8;font-size:.9rem;
        }
        .nc-empty .nc-empty-icon{font-size:2.2rem;display:block;margin-bottom:.6rem;opacity:.6;}
        .nc-pagination{margin-top:1.2rem;}

        @media (max-width:900px){
            .nc-grid{grid-template-columns:1fr;}
            .nc-hero-stat{display:none;}
        }
    </style>

    <div class="nc-wrap">
        {{-- Hero --}}
        <div class="nc-hero">
            <div class="nc-hero-icon">🔔</div>
            <div class="nc-hero-text">
                <h1>Notifications</h1>
                <p>{{ $canSend ? 'Compose and manage notifications for your school community.' : 'Stay up to date with your classes, assignments, and announcements.' }}</p>
            </div>
            @php $total = $notifications->total(); @endphp
            @if($total > 0)
                <div class="nc-hero-stat">
                    <b>{{ $total }}</b>
                    <span>Total</span>
                </div>
            @endif
        </div>

        <div class="nc-grid">

            {{-- Compose (senders only) --}}
            @if ($canSend)
                <div class="nc-card">
                    <div class="nc-card-head"><span class="nc-badge-icon">✏️</span> New Notification</div>
                    <div class="nc-card-body">
                        {{ $this->content }}
                    </div>
                </div>
            @endif

            {{-- List --}}
            <div>
                @if ($notifications->isEmpty())
                    <div class="nc-empty">
                        <span class="nc-empty-icon">📭</span>
                        No notifications yet.
                        @if($canSend)
                            <div style="margin-top:.4rem;font-size:.8rem;">Use the form to send your first notification.</div>
                        @endif
                    </div>
                @else
                    <div class="nc-list">
                        @foreach ($notifications as $notification)
                            @php
                                $isRead = $readIds->contains($notification->id);
                                $color = $notification->color ?: 'primary';
                                $badgeClass = \Illuminate\Support\Str::startsWith($color, ['bg-']) ? $color : 'bg-'.$color;
                            @endphp
                            <div class="nc-item {{ $isRead ? '' : 'np-unread' }}">
                                <div class="nc-badge {{ $badgeClass }}">
                                    @if ($notification->icon && \Illuminate\Support\Str::startsWith($notification->icon, 'heroicon'))
                                        <x-filament::icon :icon="$notification->icon" class="h-5 w-5" />
                                    @else
                                        {{ $notification->icon ?? '🔔' }}
                                    @endif
                                </div>
                                <div class="nc-main">
                                    <div class="nc-title">
                                        {{ $notification->title }}
                                        @if (! $isRead && ! $canSend)
                                            <span class="nc-unread-dot" title="Unread"></span>
                                        @endif
                                    </div>
                                    <div class="nc-meta">
                                        <span class="nc-sender">{{ $notification->sender?->name ?? 'System' }}</span>
                                        <span>·</span>
                                        <span>{{ $notification->created_at->format('M j, Y g:i A') }}</span>
                                        <span>·</span>
                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                        @if ($canSend && $notification->recipients->isNotEmpty())
                                            <span>·</span>
                                            <span>{{ $notification->recipients->count() }} recipient(s) · {{ $notification->recipients->where('is_read', true)->count() }} read</span>
                                        @endif
                                    </div>
                                    @if ($notification->body)
                                        <div class="nc-body">{{ $notification->body }}</div>
                                    @endif
                                    @if (($label = $this->mentionLabel($notification)) && ($url = $this->mentionUrl($notification)))
                                        <a class="nc-mention" href="{{ $url }}" target="_blank">
                                            🔗 {{ ucfirst($notification->mention_type?->value) }}: {{ $label }}
                                        </a>
                                    @endif
                                    <div class="nc-read-state">
                                        @if ($isRead)
                                            <span class="nc-chip read">✓ Read</span>
                                        @else
                                            <span class="nc-chip unread">● Unread</span>
                                        @endif
                                        @if ($canSend && $notification->recipients->isNotEmpty())
                                            <span class="nc-chip count">👥 {{ $notification->recipients->where('is_read', true)->count() }}/{{ $notification->recipients->count() }} read</span>
                                        @endif
                                    </div>
                                </div>
                                @if ($canSend && $deleteableIds->contains($notification->id))
                                    <div class="nc-actions">
                                        <button class="nc-btn nc-btn-danger" wire:click="deleteNotification({{ $notification->id }})"
                                                wire:confirm="Delete this notification?">Delete</button>
                                    </div>
                                @elseif (! $canSend && ! $isRead)
                                    <div class="nc-actions">
                                        <button class="nc-btn" wire:click="markAsRead({{ $notification->id }})">Mark as read</button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="nc-pagination">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
