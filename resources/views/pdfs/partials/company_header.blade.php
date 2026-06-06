@php
    $settings = \App\Models\Setting::getSettings();
    $appName  = $settings->app_name ?? 'Zulia Tours & Travels BD Limited';

    if ($settings->logo_path && \Storage::disk('public')->exists($settings->logo_path)) {
        $logoPath = storage_path('app/public/' . $settings->logo_path);
    } else {
        $logoPath = public_path('images/zulia.jpeg');
    }
@endphp

<table style="width:100%; border-collapse:collapse; border:none; margin-bottom:10px; padding-bottom:8px; border-bottom:2px solid #1a3a8f;">
    <tr>
        <td style="border:none; width:48px; vertical-align:middle; padding-right:10px;">
            <img src="{{ $logoPath }}" alt="Logo" style="height:40px; width:40px; object-fit:contain;" />
        </td>
        <td style="border:none; vertical-align:middle;">
            <div style="font-size:16px; font-weight:800; color:#1a3a8f; letter-spacing:0.03em; text-transform:uppercase;">{{ $appName }}</div>
            <div style="font-size:8px; color:#64748b; margin-top:2px; letter-spacing:0.05em; text-transform:uppercase;">
                IATA Accredited &nbsp;|&nbsp; ATAB Member &nbsp;|&nbsp; Official Quotation &amp; Reports
            </div>
        </td>
    </tr>
</table>
