@php
    try {
$lang = Utility::fetchUserLang();
        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName(SC::CPN_LG);
    } catch (\Throwable $e) {
        \Log::error('email/lead_mail — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<!doctype html>
<html lang="{{ $lang ?? app()->getLocale() }}" xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>{{ __('Lead Email') }}</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc
        ])
        <link rel="stylesheet" href="{{ asset('assets/css/routes/emails/main.css') }}" />
        <link href="https://fonts.googleapis.com/css?family=Open Sans" rel="stylesheet" type="text/css" />
    </head>
    <body style="background-color:#f8f8f8;">
        <div class="bg-light">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" class="mx-auto" style="max-width:600px;" width="600">
            <tr>
            <td style="mso-line-height-rule:exactly; line-height:0; font-size:0;">
                <div class="{{ VC::BG_WT_CC_RD }}" style="max-width:600px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" class="{{ VC::W100_BG_WT }}">
                    <tbody>
                    <tr>
                        <td class="{{ VC::TXCT }} align-top p-0">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="mx-auto">
                            <tr>
                            <td style="width:600px;">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" class="{{ VC::W100 }}">
                                <tr>
                                    <td class="p-0">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" class="{{ VC::W100 }}" style="border-top:7px solid #6676EF;">
                                        <tr><td style="height:0; line-height:0;"></td></tr>
                                    </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="{{ VC::TXCT_PY4 }}">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" class="mx-auto" style="border-collapse:collapse;">
                                        <tbody>
                                        <tr>
                                            <td style="width:110px;">
                                            <img
                                                src="{{ rtrim($logo ?? '', '/') . '/' . (isset($company_logo) && !empty($company_logo) ? e($company_logo) : 'logo.png') }}"
                                                alt="{{ (isset(\Utility::settings()['company_name']) && !empty(\Utility::settings()['company_name'])) ? \Utility::settings()['company_name'] : env('APP_NAME') }}"
                                                width="110"
                                                class="{{ VC::IMG_FL }} {{ VC::DBL }}"
                                                style="height:auto; border:0; outline:0; text-decoration:none;"
                                                loading="lazy"
                                                decoding="async">
                                            </td>
                                        </tr>
                                        </tbody>
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

        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" class="mx-auto {{ VC::MT3 }}" style="max-width:600px;" width="600">
            <tr>
            <td style="mso-line-height-rule:exactly;">
                <div class="{{ VC::BG_WT_CC_RD }}" style="max-width:600px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" class="{{ VC::W100_BG_WT }}">
                    <tbody>
                    <tr>
                        <td class="text-start {{ VC::PY4 }} px-4 px-md-5 text-secondary" style="padding-bottom:70px;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" class="{{ VC::W100 }}">
                            <tr>
                            <td class="text-start">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" class="{{ VC::W100 }}">
                                <tr>
                                    <td class="px-0">
                                    <div class="fs-6">
                                        <p class="{{ VC::MB3_FWB }}">{{ __('Hello,') }}</p>
                                    </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-0">
                                    <div class="fs-6">
                                        <p class="{{ VC::MB2 }} fw-bold">{{ __('Lead : ') }} {!! $lArr['lead_name'] !!}</p>
                                        <p class="{{ VC::MB3_FWB }}">{!! $lArr['description'] !!}</p>
                                    </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-0">
                                    <div class="fs-6">
                                        <p class="{{ VC::MY2_FWB }}">{{ __('Regards,') }}</p>
                                        <p class="{{ VC::MY2_FWB }}">{{ (isset(\Utility::settings()['company_name']) && !empty(\Utility::settings()['company_name'])) ? \Utility::settings()['company_name'] : env('APP_NAME') }}</p>
                                    </div>
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
        </div>
    </body>
</html>

{{--                                                                <p style=" line-height:32px"><b style="font-weight:700">{{__('Subject : ').'New lead has been Assign to you.'}}</b></p>--}}
