@php
    try {
$lang = Utility::fetchUserLang();
    } catch (\Throwable $e) {
        \Log::error('email/common — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<!doctype html>
<html lang="{{ $lang ?? app()->getLocale() }}" xmlns="http://www.w3.org/1999/xhtml">
    @php
        $logo= Utility::getFile('uploads/logo');
        $company_logo = Utility::getValByName(SC::CPN_LG);
@endphp
    <head>
        <title>{{ __('Common Email') }}</title>
        @include('fragments.std', [
            'meta_title' => $meta_title ?? '',
            'meta_desc' => $meta_desc ?? ''
        ])
        <link rel="stylesheet" href="{{ asset('assets/css/routes/emails/main.css') }}" />
        <link href="https://fonts.googleapis.com/css?family=Open Sans" rel="stylesheet" type="text/css">
    </head>
    <body class="bg-light">
    <div class="bg-light">
        <table align="center" border="0" cellpadding="0" cellspacing="0"
            role="presentation"
            class="mx-auto"
            style="max-width:600px;" width="600">
        <tr>
            <td style="mso-line-height-rule:exactly; line-height:0; font-size:0;">
            <div class="{{ VC::BG_WT_CC_RD }}" style="max-width:600px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                    class="{{ VC::W100_BG_WT }}">
                <tbody>
                    <tr>
                    <td class="{{ VC::TXCT }} align-top p-0">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="mx-auto">
                        <tr>
                            <td class="align-top" style="width:600px;">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" class="{{ VC::W100 }}">
                                <tr>
                                <td class="p-0">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0"
                                        role="presentation" class="{{ VC::W100 }}"
                                        style="border-top:7px solid #6676EF;">
                                    <tr><td style="height:0; line-height:0;"></td></tr>
                                    </table>
                                </td>
                                </tr>
                                <tr>
                                <td class="{{ VC::TXCT_PY4 }}">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                        style="border-collapse:collapse;">
                                    <tr>
                                        <td style="width:110px;">
                                        <img
                                            src="{{ rtrim($logo ?? '', '/') . '/' . (!empty($company_logo ?? null) ? e($company_logo) : 'logo.png') }}"
                                            alt="{{ $company_name ?? 'Logo da empresa' }}"
                                            width="110"
                                            class="{{ VC::IMG_FL }} {{ VC::DBL }}"
                                            style="height:auto;"
                                            loading="lazy" decoding="async">
                                        </td>
                                    </tr>
                                    </table>
                                </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>
                </tbody>
                </table>
            </div>
            </td>
        </tr>
        </table>
        <table align="center" border="0" cellpadding="0" cellspacing="0"
            role="presentation"
            class="mx-auto {{ VC::MT3 }}"
            style="max-width:600px;" width="600">
        <tr>
            <td style="mso-line-height-rule:exactly;">
            <div class="{{ VC::BG_WT_CC_RD }}" style="max-width:600px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                    class="{{ VC::W100_BG_WT }}">
                <tbody>
                    <tr>
                    <td class="text-start {{ VC::PY4 }} px-4 px-md-5" style="padding-bottom:70px;">
                        @yield('content')
                    </td>
                    </tr>
                </tbody>
                </table>
            </div>
            </td>
        </tr>
        </table>
    </div>
    </body>
</html>
