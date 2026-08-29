<div class="{{ $tier === 'kids' ? 'lv lv-kids' : 'lv' }}" style="min-height:100vh; background: linear-gradient(180deg, #faf5ff 0%, #fdf2f8 45%, #ffffff 100%); font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; color: #334155; display: flex; flex-direction: column;">

    <style>
        .lv { --accent: #7c3aed; --accent-soft: #ede9fe; --ink: #0f172a; }
        .lv .lv-topbar {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; padding: 1.1rem 2.5rem; flex-wrap: wrap;
            background: rgba(255,255,255,0.7); backdrop-filter: blur(8px);
            border-bottom: 1px solid #ede9fe; position: sticky; top: 0; z-index: 20;
        }
        .lv .lv-back {
            display: inline-flex; align-items: center; gap: 0.45rem;
            background: #ffffff; border: 1px solid #e2e8f0; color: #4c1d95;
            font-weight: 700; font-size: 0.85rem; padding: 0.5rem 1rem;
            border-radius: 9999px; text-decoration: none; transition: all 0.15s ease;
        }
        .lv .lv-back:hover { border-color: #c4b5fd; background: #f5f3ff; }
        .lv .lv-brand { font-weight: 800; color: #4c1d95; font-size: 0.95rem; letter-spacing: 0.01em; }

        .lv .lv-hero {
            padding: 2.75rem 2.5rem 2.25rem; text-align: center;
            background: linear-gradient(135deg, #f5f3ff 0%, #fdf2f8 100%);
            border-bottom: 1px solid #f1e8fd;
        }
        .lv .lv-breadcrumb {
            font-size: 0.82rem; font-weight: 700; color: #8b5cf6; letter-spacing: 0.02em;
            margin-bottom: 1rem; display: inline-flex; gap: 0.45rem; align-items: center; flex-wrap: wrap;
            justify-content: center;
        }
        .lv .lv-breadcrumb .sep { color: #c4b5fd; }
        .lv .lv-title {
            font-size: clamp(1.7rem, 4vw, 2.6rem); font-weight: 900; color: var(--ink);
            line-height: 1.2; margin: 0 0 1.15rem; letter-spacing: -0.01em;
        }
        .lv .lv-meta { display: inline-flex; gap: 0.55rem; flex-wrap: wrap; justify-content: center; }
        .lv .lv-chip {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: #ffffff; border: 1px solid #ede9fe; color: #4c1d95;
            font-weight: 700; font-size: 0.8rem; padding: 0.4rem 0.85rem; border-radius: 9999px;
            box-shadow: 0 2px 8px -4px rgba(124,58,237,0.15);
        }

        .lv .lv-body {
            flex: 1; width: 100%; max-width: 880px; margin: 0 auto; padding: 2.5rem 1.5rem 3.5rem;
            display: flex; flex-direction: column; gap: 1.5rem;
        }
        .lv .lv-card {
            background: #ffffff; border: 1px solid #f1e8fd; border-radius: 1.25rem;
            padding: 1.75rem 2rem; box-shadow: 0 8px 28px -18px rgba(76,29,149,0.25);
        }
        .lv .lv-card h2 {
            font-size: 1.05rem; font-weight: 800; color: #4c1d95; margin: 0 0 0.9rem;
            display: flex; align-items: center; gap: 0.45rem;
        }
        .lv .lv-about p { margin: 0; font-size: 0.98rem; line-height: 1.7; color: #475569; }

        .lv .lv-video-embed {
            position: relative; width: 100%; aspect-ratio: 16 / 9; background: #0f172a;
            border-radius: 0.9rem; overflow: hidden; margin-bottom: 0.9rem;
        }
        .lv .lv-video-embed iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
        .lv .lv-video-link {
            display: inline-flex; align-items: center; gap: 0.45rem;
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%); color: #ffffff;
            font-weight: 800; font-size: 0.88rem; padding: 0.65rem 1.3rem; border-radius: 9999px;
            text-decoration: none; box-shadow: 0 8px 20px -8px rgba(124,58,237,0.6);
        }

        .lv .lv-content-card { background: linear-gradient(180deg, #ffffff 0%, #fdfaff 100%); }
        .lv .lv-content {
            font-size: 0.98rem; line-height: 1.8; color: #334155; white-space: normal; overflow-wrap: break-word;
        }
        .lv .lv-content p { margin: 0 0 1rem; }
        .lv .lv-content p:last-child { margin-bottom: 0; }

        .lv .lv-content-html { overflow-wrap: break-word; }
        .lv .lv-content-html h1, .lv .lv-content-html h2, .lv .lv-content-html h3, .lv .lv-content-html h4 {
            color: #4c1d95; font-weight: 800; line-height: 1.35; margin: 1.5rem 0 0.75rem;
        }
        .lv .lv-content-html h1 { font-size: 1.5rem; }
        .lv .lv-content-html h2 { font-size: 1.3rem; }
        .lv .lv-content-html h3 { font-size: 1.15rem; }
        .lv .lv-content-html h4 { font-size: 1rem; }
        .lv .lv-content-html h1:first-child, .lv .lv-content-html h2:first-child,
        .lv .lv-content-html h3:first-child, .lv .lv-content-html h4:first-child { margin-top: 0; }
        .lv .lv-content-html ul, .lv .lv-content-html ol { margin: 0.5rem 0 1rem 1.4rem; line-height: 1.8; }
        .lv .lv-content-html blockquote {
            margin: 1rem 0; padding: 0.75rem 1.25rem; background: #f5f3ff;
            border-left: 4px solid #8b5cf6; border-radius: 0.5rem; color: #4c1d95;
        }
        .lv .lv-content-html a { color: #7c3aed; font-weight: 700; text-decoration: underline; }
        .lv .lv-content-html img { max-width: 100%; height: auto; border-radius: 0.75rem; }
        .lv .lv-content-html table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.92rem; }
        .lv .lv-content-html th, .lv .lv-content-html td { border: 1px solid #e2e8f0; padding: 0.55rem 0.8rem; text-align: left; }
        .lv .lv-content-html th { background: #f5f3ff; color: #4c1d95; font-weight: 800; }
        .lv .lv-content-html pre {
            background: #1e293b; color: #e2e8f0; padding: 1rem 1.25rem; border-radius: 0.75rem;
            overflow-x: auto; font-family: ui-monospace, 'Cascadia Code', monospace; font-size: 0.85rem; line-height: 1.6;
        }
        .lv .lv-content-html code { background: #f1f5f9; color: #be185d; padding: 0.15rem 0.4rem; border-radius: 0.35rem; font-size: 0.88em; }
        .lv .lv-content-html pre code { background: transparent; color: inherit; padding: 0; }

        .lv .lv-attach-list { display: flex; flex-direction: column; gap: 0.6rem; }
        .lv .lv-attach-link {
            display: flex; align-items: center; gap: 0.6rem;
            background: #f8fafc; border: 1px solid #e2e8f0; color: #0f172a;
            font-weight: 700; font-size: 0.88rem; padding: 0.7rem 1rem; border-radius: 0.8rem;
            text-decoration: none; transition: border-color 0.15s ease, background 0.15s ease;
        }
        .lv .lv-attach-link:hover { border-color: #c4b5fd; background: #f5f3ff; }
        .lv .lv-attach-link .size { margin-left: auto; color: #64748b; font-size: 0.75rem; font-weight: 600; }

        .lv .lv-empty {
            background: #ffffff; border: 1px dashed #d8b4fe; border-radius: 1.25rem;
            padding: 2.5rem; text-align: center; color: #6d28d9; font-weight: 700;
        }
        .lv .lv-footer { text-align: center; padding: 0 1.5rem 3.5rem; }
        .lv .lv-footer .lv-back { padding: 0.75rem 1.5rem; font-size: 0.9rem; }

        /* Kids tier: bigger, friendlier */
        .lv-kids .lv-title { font-size: clamp(2rem, 5vw, 3rem); }
        .lv-kids .lv-card { border-radius: 1.75rem; padding: 2rem 2.25rem; }
        .lv-kids .lv-about p, .lv-kids .lv-content { font-size: 1.12rem; line-height: 1.85; }
        .lv-kids .lv-chip { font-size: 0.9rem; padding: 0.55rem 1.1rem; border-radius: 9999px; }
        .lv-kids .lv-brand { font-size: 1.05rem; }
        .lv-kids .lv-back { font-size: 0.95rem; padding: 0.65rem 1.2rem; }

        @media (max-width: 640px) {
            .lv .lv-topbar { padding: 0.9rem 1.25rem; }
            .lv .lv-hero { padding: 2rem 1.25rem 1.75rem; }
            .lv .lv-card { padding: 1.4rem 1.25rem; }
            .lv-kids .lv-card { padding: 1.6rem 1.4rem; }
        }
    </style>

    {{-- Top bar --}}
    <div class="lv-topbar">
        <a href="{{ \App\Filament\Student\Pages\Dashboard::getUrl(['tab' => 'lessons']) }}" class="lv-back">← Back to My Lessons</a>
        <div class="lv-brand">🎓 Student Portal</div>
    </div>

    {{-- Hero header --}}
    <header class="lv-hero">
        <div class="lv-breadcrumb">
            <span>🏫 {{ $schoolName }}</span>
            @if($gradeName)
                <span class="sep">›</span>
                <span>📗 {{ $gradeName }}</span>
            @endif
            <span class="sep">›</span>
            <span>📘 {{ $className }}</span>
        </div>

        <h1 class="lv-title">{{ $lesson->title }}</h1>

        <div class="lv-meta">
            <span class="lv-chip">👨‍🏫 {{ $teacherName }}</span>
            @if($lesson->created_at)
                <span class="lv-chip">📅 {{ $lesson->created_at->format('M j, Y') }}</span>
            @endif
            @if($lessonNumber)
                <span class="lv-chip">📖 Lesson {{ $lessonNumber }}{{ $lessonTotal ? ' of ' . $lessonTotal : '' }}</span>
            @endif
        </div>
    </header>

    {{-- Content --}}
    <main class="lv-body">

        @php
            $embedUrl = null;
            if ($lesson->video_url) {
                $videoUrl = trim($lesson->video_url);
                if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})#', $videoUrl, $m)) {
                    $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
                } elseif (preg_match('#vimeo\.com/(\d+)#', $videoUrl, $m)) {
                    $embedUrl = 'https://player.vimeo.com/video/' . $m[1];
                }
            }

            // If the lesson content already contains HTML tags, render it as authored HTML.
            $contentLooksLikeHtml = ! empty($lesson->content)
                && preg_match('/<\s*\/?\s*[a-zA-Z][a-zA-Z0-9]*\b[^>]*>/', $lesson->content);
        @endphp

        @if(! $lesson->description && ! $embedUrl && ! $lesson->video_url && ! $lesson->content && ! ($lesson->attachments && $lesson->attachments->count()))
            <div class="lv-empty">
                📖 This lesson is empty for now. Your teacher has not added any content yet — check back soon!
            </div>
        @endif

        @if($lesson->description)
            <section class="lv-card lv-about-card">
                <h2>📖 About this lesson</h2>
                <div class="lv-about"><p>{!! nl2br(e($lesson->description)) !!}</p></div>
            </section>
        @endif

        @if($embedUrl || $lesson->video_url)
            <section class="lv-card lv-video-card">
                <h2>🎬 Video lesson</h2>
                @if($embedUrl)
                    <div class="lv-video-embed">
                        <iframe src="{{ $embedUrl }}" title="{{ $lesson->title }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                @endif
                <a href="{{ $lesson->video_url }}" target="_blank" rel="noopener noreferrer" class="lv-video-link">
                    @if($embedUrl) ▶️ Open video in new tab @else 🎬 Watch video lesson ↗ @endif
                </a>
            </section>
        @endif

        @if($lesson->content)
            <section class="lv-card lv-content-card">
                <h2>📄 Lesson content</h2>
                @if($contentLooksLikeHtml)
                    <div class="lv-content lv-content-html">{!! $lesson->content !!}</div>
                @else
                    <div class="lv-content">{!! nl2br(e($lesson->content)) !!}</div>
                @endif
            </section>
        @endif

        @if($lesson->attachments && $lesson->attachments->count())
            <section class="lv-card lv-attach-card">
                <h2>📎 Attachments &amp; downloads</h2>
                <div class="lv-attach-list">
                    @foreach($lesson->attachments as $attachment)
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($attachment->file_path) }}" download class="lv-attach-link">
                            📄 {{ $attachment->original_name ?? 'Download material' }}
                            @if($attachment->file_size)
                                <span class="size">{{ round($attachment->file_size / 1024, 1) }} KB</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

    </main>

    <footer class="lv-footer">
        <a href="{{ \App\Filament\Student\Pages\Dashboard::getUrl(['tab' => 'lessons']) }}" class="lv-back">← Back to My Lessons</a>
    </footer>
</div>