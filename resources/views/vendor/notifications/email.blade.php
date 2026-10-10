<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ $subject ?? 'GrowPOS' }}</title>
</head>

<body
    class="[margin:0] [padding:0] [background-color:#f3f4f6] [font-family:Segoe_UI,_Roboto,_Helvetica,_Arial,_sans-serif]">

    {{-- Preheader: teks preview yang muncul di inbox, tersembunyi dari tampilan email --}}
    <div
        class="hidden [max-height:0px] overflow-hidden [mso-hide:all] [font-size:1px] [line-height:1px] [color:#f3f4f6]">
        {{ $preheader ?? ($introLines[0] ?? 'Notifikasi dari GrowPOS System') }}
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        class="[background-color:#f3f4f6] [padding:32px_16px]">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    class="[max-width:560px] [background-color:#ffffff] [border-radius:12px] overflow-hidden [box-shadow:0_1px_3px_rgba(0,0,0,0.08)]">

                    {{-- Header --}}
                    <tr>
                        <td class="[background-color:#065f46] [padding:28px_32px] text-center">
                            <img src="{{ config('app.url') }}/img/growpos_logo-bg.png" alt="GrowPOS"
                                class="[height:40px] [width:40px] [border-radius:50%] block [margin:0_auto_8px]">
                            <div class="[color:#ffffff] [font-size:16px] font-bold [letter-spacing:0.3px]">GrowPOS
                                System</div>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td class="[padding:36px_32px]">

                            @if (!empty($greeting))
                                <h1 class="[margin:0_0_16px] [font-size:20px] font-bold [color:#111827]">
                                    {{ $greeting }}</h1>
                            @else
                                <h1 class="[margin:0_0_16px] [font-size:20px] font-bold [color:#111827]">Halo!</h1>
                            @endif

                            @foreach ($introLines as $line)
                                <p class="[margin:0_0_14px] [font-size:15px] [line-height:1.6] [color:#374151]">
                                    {{ $line }}</p>
                            @endforeach

                            @isset($actionText)
                                <table role="presentation" cellpadding="0" cellspacing="0" class="[margin:28px_auto]">
                                    <tr>
                                        <td align="center" class="[border-radius:8px] [background-color:#059669]">
                                            <a href="{{ $actionUrl }}" target="_blank"
                                                class="inline-block [padding:14px_32px] [font-size:15px] font-semibold [color:#ffffff] [text-decoration:none] [border-radius:8px]">
                                                {{ $actionText }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endisset

                            @foreach ($outroLines as $line)
                                <p class="[margin:0_0_14px] [font-size:15px] [line-height:1.6] [color:#374151]">
                                    {{ $line }}</p>
                            @endforeach

                            @isset($securityInfo)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                    class="[margin:20px_0] [background-color:#fef3c7] [border-radius:8px] [border:1px_solid_#fde68a]">
                                    <tr>
                                        <td class="[padding:16px_18px]">
                                            <p class="[margin:0_0_8px] [font-size:13px] font-bold [color:#92400e]">
                                                🔒 Detail Permintaan
                                            </p>
                                            <p class="[margin:0_0_4px] [font-size:13px] [color:#78350f] [line-height:1.6]">
                                                Waktu: {{ $securityInfo['time'] ?? '-' }}<br>
                                                Alamat IP: {{ $securityInfo['ip'] ?? '-' }}<br>
                                                Perangkat: {{ $securityInfo['device'] ?? '-' }}
                                            </p>
                                            <p class="[margin:8px_0_0] [font-size:12px] [color:#92400e]">
                                                Bukan Anda yang melakukan ini? Segera amankan akun Anda atau hubungi tim
                                                support kami.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            @endisset

                            <p class="[margin:24px_0_0] [font-size:15px] [line-height:1.6] [color:#374151]">
                                @if (!empty($salutation))
                                    {!! nl2br(e($salutation)) !!}
                                @else
                                    Salam hangat,<br>
                                    <strong>Tim Ekosistem GrowPOS Indonesia</strong>
                                @endif
                            </p>

                        </td>
                    </tr>

                    {{-- Subcopy (fallback link) --}}
                    @isset($actionText)
                        <tr>
                            <td class="[padding:0_32px_28px]">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                    class="[border-top:1px_solid_#e5e7eb] [padding-top:20px]">
                                    <tr>
                                        <td>
                                            <p class="[margin:0_0_8px] [font-size:13px] [color:#6b7280] [line-height:1.5]">
                                                Jika Anda mengalami kendala saat menekan tombol
                                                "<strong>{{ $actionText }}</strong>", silakan salin dan tempel URL
                                                berikut ke browser Anda:
                                            </p>
                                            <p class="[margin:0] [font-size:12px] [color:#059669] [word-break:break-all]">
                                                <a href="{{ $actionUrl }}"
                                                    class="[color:#059669] [text-decoration:underline]">{{ $displayableActionUrl }}</a>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endisset

                    {{-- Footer --}}
                    <tr>
                        <td
                            class="[background-color:#f9fafb] [padding:28px_32px] text-center [border-top:1px_solid_#e5e7eb]">

                            <p class="[margin:0_0_12px] [font-size:13px] [color:#4b5563]">
                                Butuh bantuan? <a
                                    href="{{ config('app.support_url', $actionUrl ?? config('app.url')) }}"
                                    class="[color:#059669] [text-decoration:none] font-semibold">Hubungi Tim Support
                                    GrowPOS</a>
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" class="[margin:0_auto_16px]">
                                <tr>
                                    @if (config('services.social.instagram'))
                                        <td class="[padding:0_6px]">
                                            <a href="{{ config('services.social.instagram') }}"
                                                class="[color:#6b7280] [text-decoration:none] [font-size:12px]">Instagram</a>
                                        </td>
                                    @endif
                                    @if (config('services.social.whatsapp'))
                                        <td class="[padding:0_6px] [border-left:1px_solid_#d1d5db]">
                                            <a href="{{ config('services.social.whatsapp') }}"
                                                class="[color:#6b7280] [text-decoration:none] [font-size:12px] [padding-left:12px]">WhatsApp</a>
                                        </td>
                                    @endif
                                    @if (config('services.social.website', config('app.url')))
                                        <td class="[padding:0_6px] [border-left:1px_solid_#d1d5db]">
                                            <a href="{{ config('app.url') }}"
                                                class="[color:#6b7280] [text-decoration:none] [font-size:12px] [padding-left:12px]">Website</a>
                                        </td>
                                    @endif
                                </tr>
                            </table>

                            <p class="[margin:0_0_6px] [font-size:11px] [color:#9ca3af] [line-height:1.5]">
                                {{ config('app.company_name', 'GrowPOS Indonesia') }}<br>
                                {{ config('app.company_address', 'Surabaya, Jawa Timur, Indonesia') }}
                            </p>

                            <p class="[margin:0] [font-size:11px] [color:#9ca3af]">
                                &copy; {{ date('Y') }} GrowPOS. All rights reserved.
                            </p>

                            @isset($unsubscribeUrl)
                                <p class="[margin:10px_0_0] [font-size:11px]">
                                    <a href="{{ $unsubscribeUrl }}"
                                        class="[color:#9ca3af] [text-decoration:underline]">Kelola preferensi notifikasi</a>
                                </p>
                            @endisset

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
