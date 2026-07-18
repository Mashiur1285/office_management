@php
    $settings = \App\Models\Setting::getSettings();
    $appName  = $settings->app_name ?? 'Zulia Tours & Travels BD Limited';
    $tagline  = $settings->company_tagline;
    $topGap   = (float) ($settings->letterhead_top_gap ?? 0);

    if ($settings->logo_path && \Storage::disk('public')->exists($settings->logo_path)) {
        $logoPath = storage_path('app/public/' . $settings->logo_path);
    } else {
        $logoPath = public_path('images/zulia.jpeg');
    }

    $headerImage = ($settings->header_image_path && \Storage::disk('public')->exists($settings->header_image_path))
        ? storage_path('app/public/' . $settings->header_image_path)
        : null;

    $watermarkImage = ($settings->watermark_image_path && \Storage::disk('public')->exists($settings->watermark_image_path))
        ? storage_path('app/public/' . $settings->watermark_image_path)
        : null;
@endphp

@if($settings->watermark_enabled)
    <div style="position:fixed; top:40%; left:0; right:0; text-align:center; z-index:-1;">
        @if($settings->watermark_type === 'image' && $watermarkImage)
            <img src="{{ $watermarkImage }}" style="max-width:60%; max-height:300px; opacity:0.08;" alt="" />
        @else
            <span style="font-size:70px; font-weight:800; color:#000000; opacity:0.05; text-transform:uppercase; letter-spacing:2px;">
                {{ $settings->watermark_text ?: $appName }}
            </span>
        @endif
    </div>
@endif

@if($topGap > 0)
    {{-- Applied as a page-level top margin so the gap repeats on every page (e.g. pre-printed pads). --}}
    <style>
        @page { margin-top: {{ $topGap }}in; }
    </style>
@endif

@if($settings->letterhead_enabled)
    @if($headerImage)
        <div style="border-bottom:2px solid #1a3a8f; padding-bottom:8px; margin-bottom:10px;">
            <img src="{{ $headerImage }}" alt="Letterhead" style="width:100%; max-height:110px; object-fit:contain;" />
        </div>
    @else
        <table style="width:100%; border-collapse:collapse; border:none; margin-bottom:10px; padding-bottom:8px; border-bottom:2px solid #1a3a8f;">
            <tr>
                <td style="border:none; width:48px; vertical-align:middle; padding-right:10px;">
                    <img src="{{ $logoPath }}" alt="Logo" style="height:40px; width:40px; object-fit:contain;" />
                </td>
                <td style="border:none; vertical-align:middle;">
                    <div style="font-size:16px; font-weight:800; color:#1a3a8f; letter-spacing:0.03em; text-transform:uppercase;">{{ $appName }}</div>
                    @if($tagline)
                    <div style="font-size:8px; color:#64748b; margin-top:2px; letter-spacing:0.05em; text-transform:uppercase;">
                        {{ $tagline }}
                    </div>
                    @endif
                </td>
            </tr>
        </table>
    @endif
@endif
