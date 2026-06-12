<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>@yield('title', $brand['name'] ?? 'Bacha Stylo')</title>
    <style>
        body { margin:0; padding:0; width:100% !important; }
        table { border-collapse:collapse; }
        img { border:0; line-height:100%; outline:none; text-decoration:none; }
        a { text-decoration:none; }

        @media only screen and (max-width:600px) {
            .email-container { width:100% !important; }
            .sp { padding:24px 18px !important; }
            .sp-x { padding-left:20px !important; padding-right:20px !important; }
            .h1 { font-size:23px !important; line-height:1.25 !important; }
            /* stack the two-column meta rows */
            .stack { display:block !important; width:100% !important; box-sizing:border-box !important; text-align:left !important; padding:14px 18px !important; }
            .stack-r { padding-top:0 !important; text-align:left !important; }
            /* full-width, stacked buttons */
            .btn { display:block !important; width:100% !important; box-sizing:border-box !important; text-align:center !important; margin:0 0 10px 0 !important; }
            .btn-ghost { margin-left:0 !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f3f3f4; -webkit-font-smoothing:antialiased; font-family:Arial, Helvetica, sans-serif; color:#141414;">
    <span style="display:none; font-size:1px; color:#f3f3f4; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">@yield('preheader', 'An update from '.($brand['name'] ?? 'Bacha Stylo'))</span>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f3f4;">
        <tr>
            <td align="center" style="padding:24px 10px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="email-container" style="width:600px; max-width:100%; background-color:#ffffff;">

                    {{-- Header --}}
                    <tr>
                        <td class="sp-x" style="background-color:#141414; border-top:3px solid #e81d25; padding:26px 32px;">
                            <span style="font-family:Georgia,'Times New Roman',serif; font-size:23px; font-weight:bold; color:#ffffff; letter-spacing:0.5px;">{{ $brand['name'] ?? 'Bacha Stylo' }}</span><span style="display:inline-block; width:7px; height:7px; background-color:#e81d25; border-radius:50%; margin-left:5px; vertical-align:middle;">&nbsp;</span>
                            <div style="margin-top:4px; font-size:10px; letter-spacing:2px; text-transform:uppercase; color:#9a9a9a;">Premium Pakistani Fashion &amp; Lifestyle</div>
                        </td>
                    </tr>

                    {{-- Content --}}
                    <tr>
                        <td class="sp" style="padding:34px 32px;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td class="sp-x" style="background-color:#141414; padding:26px 32px;">
                            <p style="margin:0 0 6px; font-size:14px; font-weight:bold; color:#ffffff;">{{ $brand['name'] ?? 'Bacha Stylo' }}</p>
                            @if(! empty($brand['address']))
                                <p style="margin:0 0 3px; font-size:12px; line-height:1.7; color:#9a9a9a;">{{ $brand['address'] }}</p>
                            @endif
                            <p style="margin:0; font-size:12px; line-height:1.7; color:#9a9a9a;">
                                @if(! empty($brand['phone'])) Phone: {{ $brand['phone'] }} @endif
                                @if(! empty($brand['phone']) && ! empty($brand['email'])) &nbsp;&middot;&nbsp; @endif
                                @if(! empty($brand['email'])) {{ $brand['email'] }} @endif
                            </p>
                            <p style="margin:14px 0 0; font-size:11px; color:#6b6b6b;">&copy; {{ date('Y') }} {{ $brand['name'] ?? 'Bacha Stylo' }}. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
