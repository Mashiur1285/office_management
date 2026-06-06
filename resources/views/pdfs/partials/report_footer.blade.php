@php
    $zulia_footer_logo = public_path('images/zulia.jpeg');
@endphp
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
            <img src="{{ $zulia_footer_logo }}" alt="Zulia Logo" style="height:36px; width:36px; object-fit:contain;" />
        </td>
        <td style="border:none; vertical-align:middle; padding-top:8px; padding-bottom:6px;">
            <div style="font-size:10px; font-weight:800; color:#1a3a8f; letter-spacing:0.05em; text-transform:uppercase; margin-bottom:4px;">
                Zulia Tours &amp; Travels BD Limited
            </div>
            <div style="font-size:7.5px; color:#4b5563; margin-bottom:2px;">
                <strong style="color:#374151;">Address:</strong>&nbsp;
                25,26,27, Kazi Nazrul Islam Avenue, Banglamotor Trade Center, (Former Happy Rahman Plaza) 4th Floor, Banglamotor Shahbagh, Dhaka-1000.
            </div>
            <div style="font-size:7.5px; color:#4b5563; margin-bottom:2px;">
                <strong style="color:#374151;">Mobile:</strong>&nbsp;+88 01716 864 109 / +88 01332 502 234
            </div>
            <div style="font-size:7.5px; color:#4b5563;">
                <strong style="color:#374151;">Email:</strong>&nbsp;zulia.tourstravelsbd@gmail.com
            </div>
        </td>
    </tr>
</table>
