@php
    use App\Models\Utility;
    use App\Config\Constants\SettingsConstants;
    $lang = Utility::fetchUserLang();
@endphp
<!doctype html>
<html lang="{{ $lang ?? app()->getLocale() }}" xmlns="http://www.w3.org/1999/xhtml">
    @php
        $logo= Utility::getFile('uploads/logo');
        $company_logo = Utility::getValByName(SettingsConstants::CPN_LG);
        //   $logo=asset(Storage::url('uploads/logo/'));
    @endphp
    <head>
        <title>Common Email</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc
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
            <div class="bg-white mx-auto rounded-3 shadow-sm" style="max-width:600px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                    class="w-100 bg-white">
                <tbody>
                    <tr>
                    <td class="text-center align-top p-0">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="mx-auto">
                        <tr>
                            <td class="align-top" style="width:600px;">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" class="w-100">
                                <tr>
                                <td class="p-0">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0"
                                        role="presentation" class="w-100"
                                        style="border-top:7px solid #6676EF;">
                                    <tr><td style="height:0; line-height:0;"></td></tr>
                                    </table>
                                </td>
                                </tr>
                                <tr>
                                <td class="text-center py-4">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                        style="border-collapse:collapse;">
                                    <tr>
                                        <td style="width:110px;">
                                        <img
                                            src="{{ rtrim($logo ?? '', '/') . '/' . (!empty($company_logo ?? null) ? e($company_logo) : 'logo.png') }}"
                                            alt="{{ $company_name ?? 'Logo da empresa' }}"
                                            width="110"
                                            class="img-fluid d-block"
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
            class="mx-auto mt-3"
            style="max-width:600px;" width="600">
        <tr>
            <td style="mso-line-height-rule:exactly;">
            <div class="bg-white mx-auto rounded-3 shadow-sm" style="max-width:600px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                    class="w-100 bg-white">
                <tbody>
                    <tr>
                    <td class="text-start py-4 px-4 px-md-5" style="padding-bottom:70px;">
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
