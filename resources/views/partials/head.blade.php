<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.\App\Services\PlatformSettings::name() : \App\Services\PlatformSettings::name() }}
</title>

<link rel="icon" href="{{ \App\Services\PlatformSettings::logoUrl() }}">
<link rel="apple-touch-icon" href="{{ \App\Services\PlatformSettings::logoUrl() }}">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
