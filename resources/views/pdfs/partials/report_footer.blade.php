@php
    $footerSettings = \App\Models\Setting::getSettings();
    $footerAppName  = $footerSettings->app_name ?? 'Zulia Tours & Travels BD Limited';

    if ($footerSettings->logo_path && \Storage::disk('public')->exists($footerSettings->logo_path)) {
        $zulia_footer_logo = storage_path('app/public/' . $footerSettings->logo_path);
    } else {
        $zulia_footer_logo = public_path('images/zulia.jpeg');
    }
@endphp

@if($footerSettings->letterhead_enabled)
<style>
    #pdf-footer {
        position: fixed;
        bottom: 20px;
        left: 0;
        right: 0;
    }
    @page { margin-bottom: 100px; }
    body { margin-bottom: 0; }
</style>

<table id="pdf-footer" style="width:100%; border-collapse:collapse; border:none; border-top:2px solid #1a3a8f; background:#fff;">
    <tr>
        <td style="border:none; width:46px; vertical-align:middle; padding-right:12px; padding-top:8px; padding-bottom:6px;">
            <img src="{{ $zulia_footer_logo }}" alt="Logo" style="height:36px; width:36px; object-fit:contain;" />
        </td>
        <td style="border:none; vertical-align:middle; padding-top:8px; padding-bottom:6px;">
            <div style="font-size:10px; font-weight:800; color:#1a3a8f; letter-spacing:0.05em; text-transform:uppercase; margin-bottom:4px;">
                {{ $footerAppName }}
            </div>
            @if($footerSettings->company_address)
            <div style="font-size:7.5px; color:#4b5563; margin-bottom:2px;">
                <strong style="color:#374151;">Address:</strong>&nbsp;{{ $footerSettings->company_address }}
            </div>
            @endif
            @if($footerSettings->company_phone)
            <div style="font-size:7.5px; color:#4b5563; margin-bottom:2px;">
                <strong style="color:#374151;">Mobile:</strong>&nbsp;{{ $footerSettings->company_phone }}
            </div>
            @endif
            @if($footerSettings->company_email)
            <div style="font-size:7.5px; color:#4b5563;">
                <strong style="color:#374151;">Email:</strong>&nbsp;{{ $footerSettings->company_email }}
            </div>
            @endif
            @if($footerSettings->footer_note)
            <div style="font-size:7.5px; color:#64748b; font-style:italic; margin-top:3px;">
                {{ $footerSettings->footer_note }}
            </div>
            @endif
        </td>
    </tr>
</table>
@endif
