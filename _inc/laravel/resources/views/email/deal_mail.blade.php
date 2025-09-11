@php
    use App\Models\Utility;
    use App\Config\Constants\SettingsConstants;
    use Illuminate\Support\Facades\Storage;
    $lang = Utility::fetchUserLang();
    $logo = asset(Storage::url('uploads/logo/'));
    $company_logo = Utility::getValByName(SettingsConstants::CPN_LG);
@endphp
<!doctype html>
<html lang="{{ $lang ?? app()->getLocale() }}" xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Deal Email</title>
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
                <div class="bg-white mx-auto rounded-3 shadow-sm" style="max-width:600px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" class="w-100 bg-white">
                    <tbody>
                    <tr>
                        <td class="text-center align-top p-0">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="mx-auto">
                            <tr>
                            <td style="width:600px;">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" class="w-100">
                                <tr>
                                    <td class="p-0">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" class="w-100" style="border-top:7px solid #6676EF;">
                                        <tr><td style="height:0; line-height:0;"></td></tr>
                                    </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center py-4">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" class="mx-auto" style="border-collapse:collapse;">
                                        <tbody>
                                        <tr>
                                            <td style="width:110px;">
                                            <img
                                                src="{{ rtrim($logo ?? '', '/') . '/' . (isset($company_logo) && !empty($company_logo) ? e($company_logo) : 'logo.png') }}"
                                                alt="{{ (isset(\Utility::settings()['company_name']) && !empty(\Utility::settings()['company_name'])) ? \Utility::settings()['company_name'] : env('APP_NAME') }}"
                                                width="110"
                                                class="img-fluid d-block"
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

        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" class="mx-auto mt-3" style="max-width:600px;" width="600">
            <tr>
            <td style="mso-line-height-rule:exactly;">
                <div class="bg-white mx-auto rounded-3 shadow-sm" style="max-width:600px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" class="w-100 bg-white">
                    <tbody>
                    <tr>
                        <td class="text-start py-4 px-4 px-md-5 text-secondary" style="padding-bottom:70px;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" class="w-100">
                            <tr>
                            <td class="text-start">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" class="w-100">
                                <tr>
                                    <td class="px-0">
                                    <div class="fs-6">
                                        <p class="mb-3 fw-bold">{{ __('Hello,') }}</p>
                                    </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-0">
                                    <div class="fs-6">
                                        <p class="mb-2 fw-bold">{{ __('Deal : ') . $dArr['deal_name'] }}</p>
                                        <p class="mb-3 fw-bold">{!! $dArr['description'] !!}</p>
                                    </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-0">
                                    <div class="fs-6">
                                        <p class="my-2 fw-bold">{{ __('Regards,') }}</p>
                                        <p class="my-2 fw-bold">{{ (isset(\Utility::settings()['company_name']) && !empty(\Utility::settings()['company_name'])) ? \Utility::settings()['company_name'] : env('APP_NAME') }}</p>
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

{{--                                                                <p style=" line-height:32px"><b style="font-weight:700">{{__('Subject : ').'New Deal has been Assign to you.'}}</b></p>--}}