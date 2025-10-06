@php
	use App\Config\Constants\{
        ExtendingLayoutsConstants,
        SettingsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
	use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
	use Illuminate\Support\Facades\{Log,Route};
    use Illuminate\Support\Str;
	$lang = Utility::fetchUserLang();
	$data ??= [];
	$logo ??= '';
	$logo_dark ??= '';
	$logo_light ??= '';
	$company_favicon ??= '';
	$colorSettings ??= [];
	$color ??= '';
	$siteRtl ??= false;
	$meta_image ??= '';
	$file_type ??= [];
	$settings ??= [];
	$local_storage_validation ??= '';
	$local_storage_validations ??= [];
	$s3_storage_validation ??= '';
	$s3_storage_validations ??= [];
	$wasabi_storage_validation ??= '';
	$wasabi_storage_validations ??= [];
	$faviconUrl ??= '';
	try {
		$data=Utility::prepareCommonViewData()?:[];
		$lang=Utility::getValByName(SettingsConstants::DEF_LNG)?:'';
		$logo=$data[SettingsConstants::LOGO]??'';
		$logo_light=Utility::getValByName('logo_light')?:'';
		$logo_dark=Utility::getValByName('logo_dark')?:'';
		$company_favicon=$data[SettingsConstants::FAV_ICN]??'';
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$color=$data[SettingsConstants::THM_CLR]??'';
		$siteRtl=$data[SettingsConstants::RTL]??false;
		$meta_image=$data[SettingsConstants::MT_IMG_K]??'';
		$file_type=config('files_types',[]);
		$settings=Utility::settings()?:[];
		$local_storage_validation=$settings[SettingsConstants::LC_ST_VL]??'';
		$local_storage_validations=explode(',', $local_storage_validation);
		$s3_storage_validation=$settings[SettingsConstants::S3_STG_VL]??'';
		$s3_storage_validations=explode(',', $s3_storage_validation);
		$wasabi_storage_validation=$settings[SettingsConstants::WB_STG_VL]??'';
		$wasabi_storage_validations=explode(',', $wasabi_storage_validation);
		$faviconUrl=Utility::getCompanyLogo()?:'';
	} catch (\Error $e) {
		Log::error(
			'Error fetching storage/view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching storage/view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching storage/view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	}
    $data = Utility::fallbackSettings($data);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Settings') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Settings')}}</li>
@endsection

@push(StacksConstants::ADM_CSS)
    @if(in_array($color, ['theme-1', 'theme-2', 'theme-3', 'theme-4']))
        <link rel="stylesheet" href="{{ asset('assets/css/routes/settings/' . str_replace('theme-', 'theme', $color) . '.css') }}" />
    @endif
@endpush

@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/settings/lang/color.js') }}">
    </script>
    <script defer>
        (() => {
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const DATA_LISTENER_ADDED = "data-listener-added";

        const getMsg = (el, key) => {
            let msg = errFb;
            if (
            el?.getAttribute("data-sv-localized") === "true" ||
            el?.getAttribute(dataClientLocalized) === "true"
            ) {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem("erp-np-lang") ||
                document.documentElement.lang ||
                "en"
            )
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[key] ||
                el?.getAttribute(dataGuardMsg) ||
                window.translations?.en?.[key] ||
                errFb;
            if (msg !== errFb) {
                el?.setAttribute(dataGuardMsg, msg);
                el?.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        };

        const feedback = (el, key, ev = "click") => {
            const text = getMsg(el || document.body, key);
            const hasBs =
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap?.Toast;
            if (hasBs) {
            let t = document.querySelector("#np-error-toast");
            if (!t) {
                t = document.createElement("div");
                t.id = "np-error-toast";
                t.className = "toast align-items-center text-bg-danger border-0";
                t.setAttribute("role", "alert");
                t.setAttribute("aria-live", "assertive");
                t.setAttribute("aria-atomic", "true");
                t.innerHTML = `<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                document.body.appendChild(t);
            }
            const show = () => new bootstrap.Toast(t).show();
            document.addEventListener(ev, show, { once: true });
            const mo = new MutationObserver((_, o) => {
                if (!document.body.contains(t)) {
                document.removeEventListener(ev, show);
                o.disconnect();
                }
            });
            mo.observe(document.body, { childList: true, subtree: true });
            } else {
            const h = () => alert(text);
            document.addEventListener(ev, h, { once: true });
            }
        };

        const attachGuardOnce = (el, key, ev = "click") => {
            if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;
            const handler = () => feedback(el, key, ev);
            el.addEventListener(ev, handler, { once: true });
            el.setAttribute(DATA_LISTENER_ADDED, "true");
            const mo = new MutationObserver((_, o) => {
            if (!document.body.contains(el)) {
                el.removeEventListener(ev, handler);
                o.disconnect();
            }
            });
            mo.observe(document.body, { childList: true, subtree: true });
        };

        const routeGuard = (element, alt) => {
            const url = element?.getAttribute?.("data-url");
            const href = element?.action ?? element?.href;
            return (
            (!url || url === "#") && (!href || href === "#") && (!alt || alt === "#")
            );
        };

        try {
            if (typeof $ === "undefined") {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
                return;
            }

            try {
            if (window.bootstrap?.ScrollSpy) {
                new bootstrap.ScrollSpy(document.body, {
                target: "#useradd-sidenav",
                offset: 300,
                });
            } else {
                attachGuardOnce(document.body, "scrollspy_unavailable", "click");
            }
            } catch {
            attachGuardOnce(document.body, "scrollspy_unavailable", "click");
            }

            $(document).on("click", ".themes-color-change", function () {
            try {
                const color = $(this).data("value");
                if (color == null) {
                attachGuardOnce(this, "theme_color_unavailable", "click");
                return;
                }
                $(".theme-color").prop("checked", false);
                $(".themes-color-change").removeClass("active_color");
                $(this).addClass("active_color");
                $(`input[value=${color}]`).prop("checked", true);
            } catch {
                attachGuardOnce(this, "theme_color_unavailable", "click");
            }
            });

            $(document).on("change", "[name=storage_setting]", function () {
            try {
                const v = $(this).val();
                if (v === "s3") {
                $(".s3-setting").removeClass("d-none");
                $(".wasabi-setting,.local-setting").addClass("d-none");
                } else if (v === "wasabi") {
                $(".wasabi-setting").removeClass("d-none");
                $(".s3-setting,.local-setting").addClass("d-none");
                } else {
                $(".local-setting").removeClass("d-none");
                $(".s3-setting,.wasabi-setting").addClass("d-none");
                }
            } catch {
                attachGuardOnce(this, "storage_setting_unavailable", "click");
            }
            });

            const bindLocalPreview = (inputId, imgId, key) => {
            const input = document.getElementById(inputId);
            const img = document.getElementById(imgId);
            if (!input || !img) {
                attachGuardOnce(document.body, key, "click");
                return;
            }
            if (!input.getAttribute(DATA_LISTENER_ADDED)) {
                const handler = () => {
                try {
                    const f = input.files?.[0];
                    if (!f) return;
                    const src = URL.createObjectURL(f);
                    img.src = src;
                } catch {
                    attachGuardOnce(input, key, "click");
                }
                };
                input.addEventListener("change", handler, false);
                input.setAttribute(DATA_LISTENER_ADDED, "true");
                const mo = new MutationObserver((_, o) => {
                if (!document.body.contains(input)) {
                    input.removeEventListener("change", handler);
                    o.disconnect();
                }
                });
                mo.observe(document.body, { childList: true, subtree: true });
            }
            };

            bindLocalPreview("logo_dark", "image", "logo_dark_unavailable");
            bindLocalPreview("logo_light", "image1", "logo_light_unavailable");
            bindLocalPreview("favicon", "image2", "favicon_unavailable");

            $(document).on("click", ".send_email", function (e) {
            e.preventDefault();
            try {
                const title = $(this).attr("data-title") ?? "";
                const size = "md";
                const url = $(this).attr("data-url") ?? "";
                if (routeGuard(this, url)) {
                attachGuardOnce(this, "email_modal_unavailable", "click");
                return;
                }
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass(`modal-${size}`);
                $("#commonModal").modal("show");
                $.post(
                url,
                {
                    _token: "{{csrf_token()}}",
                    mail_driver: $("#mail_driver").val(),
                    mail_host: $("#mail_host").val(),
                    mail_port: $("#mail_port").val(),
                    mail_username: $("#mail_username").val(),
                    mail_password: $("#mail_password").val(),
                    mail_encryption: $("#mail_encryption").val(),
                    mail_from_address: $("#mail_from_address").val(),
                    mail_from_name: $("#mail_from_name").val(),
                },
                function (data) {
                    $("#commonModal .body").html(data);
                }
                ).fail(() =>
                attachGuardOnce(document.body, "email_modal_unavailable", "click")
                );
            } catch {
                attachGuardOnce(this, "email_modal_unavailable", "click");
            }
            });

            $(document).on("submit", "#test_email", function (e) {
            e.preventDefault();
            const form = this;
            try {
                const url = $(form).attr("action") ?? "";
                if (routeGuard(form, url)) {
                attachGuardOnce(form, "email_test_unavailable", "click");
                return;
                }
                const post = $(form).serialize();
                $.ajax({
                type: "post",
                url,
                data: post,
                cache: false,
                beforeSend: () => {
                    $("#test_email .btn-create").attr("disabled", "disabled");
                },
                success: data => {
                    if (data?.success) {
                    show_toastr("success", data.message, "success");
                    } else {
                    show_toastr(
                        "error",
                        data?.message || getMsg(form, "email_test_unavailable"),
                        "error"
                    );
                    }
                    $("#commonModal").modal("hide");
                },
                complete: () => {
                    $("#test_email .btn-create").removeAttr("disabled");
                },
                error: () => attachGuardOnce(form, "email_test_unavailable", "click"),
                });
            } catch {
                attachGuardOnce(form, "email_test_unavailable", "click");
            }
            });

            window.enablecookie = () => {
            try {
                const enabled = $("#enable_cookie").is(":checked");
                $(".cookieDiv").addClass("disabledCookie");
                if (enabled) {
                $(".cookieDiv").removeClass("disabledCookie");
                $("#cookie_logging").prop("checked", true);
                } else {
                $(".cookieDiv").addClass("disabledCookie");
                $("#cookie_logging").prop("checked", false);
                }
            } catch {
                attachGuardOnce(document.body, "cookie_unavailable", "click");
            }
            };

            const styleToggle = (
            checkboxSel,
            hrefOn,
            hrefOff,
            imgSel,
            imgOn,
            imgOff
            ) => {
            const cb = document.querySelector(checkboxSel);
            if (!cb) return;
            if (cb.getAttribute(DATA_LISTENER_ADDED) === "true") return;
            const handler = () => {
                try {
                if (cb.checked) {
                    $("#style").attr("href", hrefOn);
                    if (imgSel) $(imgSel).attr("src", imgOn);
                } else {
                    $("#style").attr("href", hrefOff);
                    if (imgSel) $(imgSel).attr("src", imgOff);
                }
                } catch {
                attachGuardOnce(cb, "style_switch_unavailable", "click");
                }
            };
            cb.addEventListener("click", handler, false);
            cb.setAttribute(DATA_LISTENER_ADDED, "true");
            const mo = new MutationObserver((_, o) => {
                if (!document.body.contains(cb)) {
                cb.removeEventListener("click", handler);
                o.disconnect();
                }
            });
            mo.observe(document.body, { childList: true, subtree: true });
            };

            styleToggle(
            "#cust-darklayout",
            "{{env('APP_URL')}}/public/assets/css/style-dark.css",
            "{{env('APP_URL')}}/public/assets/css/style.css",
            ".dash-sidebar .main-logo a img",
            "{{$logo.$logo_light}}",
            "{{$logo.$logo_dark}}"
            );

            const cbBg = document.querySelector("#cust-theme-bg");
            if (cbBg && cbBg.getAttribute(DATA_LISTENER_ADDED) !== "true") {
            const handler = () => {
                try {
                const side = document.querySelector(".dash-sidebar");
                const head = document.querySelector(
                    ".dash-header:not(.dash-mob-header)"
                );
                if (cbBg.checked) {
                    side?.classList.add("transprent-bg");
                    head?.classList.add("transprent-bg");
                } else {
                    side?.classList.remove("transprent-bg");
                    head?.classList.remove("transprent-bg");
                }
                } catch {
                attachGuardOnce(cbBg, "style_switch_unavailable", "click");
                }
            };
            cbBg.addEventListener("click", handler, false);
            cbBg.setAttribute(DATA_LISTENER_ADDED, "true");
            const mo = new MutationObserver((_, o) => {
                if (!document.body.contains(cbBg)) {
                cbBg.removeEventListener("click", handler);
                o.disconnect();
                }
            });
            mo.observe(document.body, { childList: true, subtree: true });
            }
        } catch (e) {
            if (
                window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1"
            ) {
                console.error("Initialization failed", e);
            }
        }
        })();
    </script>
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Settings')}}</li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    @php
                        $settingsSections = [
                            ['id' => 'brand-settings',     'label' => __('Brand Settings')],
                            ['id' => 'email-settings',     'label' => __('Email Settings')],
                            ['id' => 'payment-settings',   'label' => __('Payment Settings')],
                            ['id' => 'pusher-settings',    'label' => __('Pusher Settings')],
                            ['id' => 'recaptcha-settings', 'label' => __('ReCaptcha Settings')],
                            ['id' => 'storage-settings',   'label' => __('Storage Settings')],
                            ['id' => 'seo-settings',       'label' => __('SEO Settings')],
                            ['id' => 'cookie-settings',    'label' => __('Cookie Settings')],
                            ['id' => 'cache-settings',     'label' => __('Cache Settings')],
                            ['id' => 'chat-gpt-settings',  'label' => __('Chat GPT Settings')],
                        ];
                    @endphp
                    <div class="{{ VC::CD_STK }}" style="top:30px">
                        <div class="{{ VC::LG_FLSH }}" id="useradd-sidenav">
                            @foreach($settingsSections as $section)
                                <a href="#{{ $section['id'] }}"
                                class="{{ VC::LGI_ACT_NBD }}">
                                    {{ $section['label'] }}
                                    <div class="float-end">
                                        <i class="{{ VC::TI_CHV_RT }}"></i>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div id="brand-settings" class="card">
                        <div class="card-header">
                            <h5>{{ __('Brand Settings') }}</h5>
                        </div>
                        @php
                            $systemStoreBaseName          = ViewsConstants::SYS;
                            $systemStoreKebabName         = Str::kebab($systemStoreBaseName);
                            $systemStoreResolvedName      = Route::has($systemStoreBaseName)
                                ? $systemStoreBaseName
                                : (Route::has($systemStoreKebabName) ? $systemStoreKebabName : null);
                            $systemStoreUrl               = $systemStoreResolvedName ? route($systemStoreResolvedName) : '#';
                            $systemStoreGuardMsg          = Utility::fetchLinkMessage($lang, ViewsConstants::SYS, 'system_store_route_unavailable')
                                ?? 'System store route is unavailable. Please contact technical support or your domain administrator.';
                            $systemStoreFormId            = 'system-store-form';
                        @endphp
                        {!! Form::model($settings, [
                            'url'            => $systemStoreUrl,
                            'method'         => 'POST',
                            'enctype'        => 'multipart/form-data',
                            'id'             => $systemStoreFormId,
                            'data-url'       => $systemStoreUrl,
                            'data-guard-msg' => $systemStoreGuardMsg
                        ]) !!}
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/settings/systems/store.js') }}">
                            </script>
                        @endpush
                        <div class="card-body">
                            <div class="{{ VC::RW }}">
                                <div class="col-lg-4 col-sm-6 {{ VC::CM6 }}">
                                    <div class="{{ VC::CD }} logo_card">
                                        <div class="card-header">
                                            <h5>{{ __('Logo dark') }}</h5>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="setting-card">
                                                <div class="logo-content {{ VC::MT4 }}">
                                                    <img
                                                        id="image"
                                                        src="{{ $logo.'/'.(isset($logo_dark) && !empty($logo_dark) ? $logo_dark : SettingsConstants::CPN_LG_DK_DEF).'?timestamp='.time() }}"
                                                        class="big-logo"
                                                    >
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="logo_dark">
                                                        <div class="{{ VC::BG_P }} company_logo_update">
                                                            <i class="{{ VC::TI }} ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" name="logo_dark" id="logo_dark" class="{{ VC::FM_CT }} file" data-filename="logo_dark">
                                                    </label>
                                                </div>
                                                @error('logo_dark')
                                                    <div class="{{ VC::RW }}">
                                                        <span class="invalid-logo" role="alert">
                                                            <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                        </span>
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 {{ VC::CM6 }}">
                                    <div class="{{ VC::CD }} logo_card">
                                        <div class="card-header">
                                            <h5>{{ __('Logo Light') }}</h5>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="setting-card">
                                                <div class="logo-content {{ VC::MT4 }}">
                                                    <img
                                                        id="image1"
                                                        src="{{ $logo.'/'.(isset($logo_light) && !empty($logo_light) ? $logo_light : SettingsConstants::CPN_LG_LT_DEF).'?timestamp='.time() }}"
                                                        class="big-logo img_setting"
                                                    >
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="logo_light">
                                                        <div class="{{ VC::BG_P }} dark_logo_update">
                                                            <i class="{{ VC::TI }} ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" name="logo_light" id="logo_light" class="{{ VC::FM_CT }} file" data-filename="logo_light">
                                                    </label>
                                                </div>
                                                @error('logo_light')
                                                    <div class="{{ VC::RW }}">
                                                        <span class="invalid-logo" role="alert">
                                                            <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                        </span>
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 {{ VC::CM6 }}">
                                    <div class="{{ VC::CD }} logo_card">
                                        <div class="card-header">
                                            <h5>{{ __('Favicon') }}</h5>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="setting-card">
                                                <div class="logo-content {{ VC::MT4 }}">
                                                    <img id="image2" src="{{ $faviconUrl }}" class="img_setting">
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="favicon">
                                                        <div class="{{ VC::BG_P }} company_favicon_update">
                                                            <i class="{{ VC::TI }} ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="{{ VC::FM_CT }} file" id="favicon" name="favicon" data-filename="favicon">
                                                    </label>
                                                </div>
                                                @error(SettingsConstants::FAV_ICN)
                                                    <div class="{{ VC::RW }}">
                                                        <span class="invalid-logo" role="alert">
                                                            <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                        </span>
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::RW }}">
                                <div class="col-md-4">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('title_text', __('Title Text'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('title_text', null, ['class' => VC::FM_CT, 'placeholder' => __('Title Text')]) }}
                                        @error('title_text')
                                            <span class="invalid-title_text" role="alert">
                                                <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label(SettingsConstants::FT_TXT, __('Footer Text'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text(SettingsConstants::FT_TXT, Utility::getValByName(SettingsConstants::FT_TXT), ['class' => VC::FM_CT, 'placeholder' => __('Enter Footer Text')]) }}
                                        @error(SettingsConstants::FT_TXT)
                                            <span class="invalid-footer_text" role="alert">
                                                <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label(SettingsConstants::DEF_LNG, __('Default Language'), ['class' => VC::FM_LB . ' text-dark']) }}
                                        <div class="changeLanguage">
                                            <select name="default_language" id="default_language" class="{{ VC::FM_CT }} select">
                                                @php $langs = Utility::languages(); @endphp
                                                @if (Utility::isFilled($langs))
                                                    @foreach ($langs as $code => $language)
                                                        <option @if ($lang == $code) selected @endif value="{{ $code }}">{{ ucFirst($language) }}</option>
                                                    @endforeach
                                                @else
                                                    <option value="{{ DatabaseConstants::DEFAULT_LANG }}">{{ __('No languages options available') }}</option>
                                                @endif
                                            </select>
                                        </div>
                                        @error(SettingsConstants::DEF_LNG)
                                            <span class="invalid-default_language" role="alert">
                                                <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::RW }}">
                                <div class="form-group col-md-2">
                                    <div class="custom-control custom-switch">
                                        <label class="text-dark mb-1 mt-3" for="SITE_RTL">{{ __('Enable RTL') }}</label>
                                        <div>
                                            <input type="checkbox" name="SITE_RTL" id="SITE_RTL" data-toggle="switchbutton" data-onstyle="primary" {{ !empty($settings[SettingsConstants::RTL]) && $settings[SettingsConstants::RTL] == 'on' ? 'checked="checked"' : '' }}>
                                            <label class="custom-control-label" for="SITE_RTL"></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="{{ VC::FM_G }}">
                                        <label class="text-dark mb-1 mt-3" for="display_landing_page">{{ __('Enable Landing Page') }}</label>
                                        <div>
                                            <input type="checkbox" name="display_landing_page" class="form-check-input" id="display_landing_page" data-toggle="switchbutton" {{ (Utility::getValByName('display_landing_page') == 'on') ? 'checked' : '' }} data-onstyle="primary">
                                            <label class="form-check-label" for="display_landing_page"></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="{{ VC::FM_G }}">
                                        <label class="text-dark mb-1 mt-3" for="enable_signup">{{ __('Enable Sign-Up Page') }}</label>
                                        <div>
                                            <input type="checkbox" name="enable_signup" id="enable_signup" data-toggle="switchbutton" {{ !empty(SettingsConstants::ENB_SGU) && $settings[SettingsConstants::ENB_SGU] == 'on' ? 'checked="checked"' : '' }} data-onstyle="primary">
                                            <label class="form-check-label" for="enable_signup"></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="{{ VC::FM_G }}">
                                        <label class="text-dark mb-1 mt-3" for="email_verification">{{ __('Email Verification') }}</label>
                                        <div>
                                            <input type="checkbox" name="email_verification" id="email_verification" data-toggle="switchbutton" {{ !empty($settings['email_verification']) ? ($settings['email_verification'] == 'on' ? 'checked="checked"' : '') : '' }} data-onstyle="primary">
                                            <label class="form-check-label" for="email_verification"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <h4 class="small-title">{{ __('Theme Customizer') }}</h4>
                            <div class="setting-card setting-logo-box p-3">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CL_XL4 }}">
                                        <h6 class="mt-2">
                                            <i data-feather="credit-card" class="me-2"></i>{{ __('Primary color settings') }}
                                        </h6>
                                        <hr class="my-2"/>
                                        <div class="theme-color themes-color">
                                            @php
                                                $themes = ['theme-1', 'theme-2', 'theme-3', 'theme-4', 'theme-5'];
                                                $themesRow2 = ['theme-6', 'theme-7', 'theme-8', 'theme-9', 'theme-10'];
                                                $breakAfter = 5;
                                            @endphp
                                            @foreach($themes as $index => $theme)
                                                <a href="#!" class="themes-color-change {{ $settings['color'] == $theme ? 'active_color' : '' }}" data-value="{{ $theme }}"></a>
                                                <input type="radio" class="theme_color d-none" name="color" value="{{ $theme }}" {{ $settings['color'] == $theme ? 'checked' : '' }}>
                                                @if($index + 1 == $breakAfter)
                                                    <br>
                                                @endif
                                            @endforeach
                                            @foreach($themesRow2 as $theme)
                                                <a href="#!" class="themes-color-change {{ $settings['color'] == $theme ? 'active_color' : '' }}" data-value="{{ $theme }}"></a>
                                                <input type="radio" class="theme_color d-none" name="color" value="{{ $theme }}" {{ $settings['color'] == $theme ? 'checked' : '' }}>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_XL4 }}">
                                        <h6 class="mt-2">
                                            <i data-feather="layout" class="me-2"></i>{{ __('Sidebar settings') }}
                                        </h6>
                                        <hr class="my-2"/>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="cust-theme-bg" name="cust_theme_bg" {{ !empty($settings[SettingsConstants::CST_BG]) && $settings[SettingsConstants::CST_BG] == 'on' ? 'checked' : '' }}/>
                                            <label class="form-check-label f-w-600 pl-1" for="cust-theme-bg">{{ __('Transparent layout') }}</label>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_XL4 }}">
                                        <h6 class="mt-2">
                                            <i data-feather="sun" class="me-2"></i>{{ __('Layout settings') }}
                                        </h6>
                                        <hr class="my-2"/>
                                        <div class="form-check form-switch mt-2">
                                            <input type="checkbox" class="form-check-input" id="cust-darklayout" name="cust_darklayout" {{ !empty($colorSettings[SettingsConstants::CST_DRK]) && $colorSettings[SettingsConstants::CST_DRK] == 'on' ? 'checked' : '' }}/>
                                            <label class="form-check-label f-w-600 pl-1" for="cust-darklayout">{{ __('Dark Layout') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <div class="{{ VC::FM_G }}">
                                    <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                    <div id="email-settings" class="{{ VC::CD }}">
                        <div class="card-header">
                            <h5>{{ __('Email Settings') }}</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $emailSettingsBaseRouteName            = ViewsConstants::EML . '.settings';
                                $emailSettingsKebabRouteName           = Str::kebab($emailSettingsBaseRouteName);
                                $emailSettingsResolvedRouteName        = Route::has($emailSettingsBaseRouteName)
                                    ? $emailSettingsBaseRouteName
                                    : (Route::has($emailSettingsKebabRouteName) ? $emailSettingsKebabRouteName : null);
                                $emailSettingsRouteArray               = $emailSettingsResolvedRouteName ? [$emailSettingsResolvedRouteName] : ['#'];
                                $emailSettingsUrl                      = $emailSettingsResolvedRouteName ? route($emailSettingsResolvedRouteName) : '#';
                                $emailSettingsGuardMsg                 = Utility::fetchLinkMessage($lang, ViewsConstants::EML, 'email_settings_route_unavailable') ?? 'Email settings route is unavailable. Please contact technical support or your domain administrator.';
                                $emailSettingsFormId                   = 'email-settings-form';
                            @endphp
                            {!! Form::open([
                                'route'          => $emailSettingsRouteArray,
                                'method'         => 'post',
                                'id'             => $emailSettingsFormId,
                                'data-url'       => $emailSettingsUrl,
                                'data-guard-msg' => $emailSettingsGuardMsg
                            ]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/email.js') }}">
                                    </script>
                                @endpush
                                @csrf
                                <div class="{{ VC::RW }}">
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_driver', __('Mail Driver'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_driver', isset($settings['mail_driver']) ? $settings['mail_driver'] : '', ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail Driver')]) }}
                                            @error('mail_driver')
                                                <span class="invalid-mail_driver" role="alert">
                                                    <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_host', __('Mail Host'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_host', isset($settings['mail_host']) ? $settings['mail_host'] : '', ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail Host')]) }}
                                            @error('mail_host')
                                                <span class="invalid-mail_host" role="alert">
                                                    <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_port', __('Mail Port'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_port', isset($settings['mail_port']) ? $settings['mail_port'] : '', ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail Port')]) }}
                                            @error('mail_port')
                                                <span class="invalid-mail_port" role="alert">
                                                    <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::RW }}">
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_username', __('Mail Username'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_username', isset($settings['mail_username']) ? $settings['mail_username'] : '', ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail Username')]) }}
                                            @error('mail_username')
                                                <span class="invalid-mail_username" role="alert">
                                                    <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_password', __('Mail Password'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_password', isset($settings['mail_password']) ? $settings['mail_password'] : '', ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail Password')]) }}
                                            @error('mail_password')
                                                <span class="invalid-mail_password" role="alert">
                                                    <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_encryption', __('Mail Encryption'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_encryption', isset($settings['mail_encryption']) ? $settings['mail_encryption'] : '', ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail Encryption')]) }}
                                            @error('mail_encryption')
                                                <span class="invalid-mail_encryption" role="alert">
                                                    <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::RW }}">
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_from_address', __('Mail From Address'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_from_address', isset($settings['mail_from_address']) ? $settings['mail_from_address'] : '', ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail From Address')]) }}
                                            @error('mail_from_address')
                                                <span class="invalid-mail_from_address" role="alert">
                                                    <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_from_name', __('Mail From Name'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_from_name', isset($settings['mail_from_name']) ? $settings['mail_from_name'] : '', ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail From Name')]) }}
                                            @error('mail_from_name')
                                                <span class="invalid-mail_from_name" role="alert">
                                                    <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::RW }}">
                                    <div class="card-footer {{ VC::DFL }} {{ VC::JCE }}">
                                        <div class="{{ VC::FM_G }} me-2">
                                            <a
                                                href="#"
                                                data-url="{{ route(ViewsConstants::TT . '.mail') }}"
                                                data-title="{{ __('Send Test Mail') }}"
                                                class="{{ VC::BT_PRM }} send_email"
                                            >
                                                {{ __('Send Test Mail') }}
                                            </a>
                                        </div>
                                        <div class="{{ VC::FM_G }}">
                                            <input class="{{ VC::BT_PRM }}" type="submit" value="{{ __('Save Changes') }}">
                                        </div>
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                    <div class="card" id="payment-settings">
                        <div class="card-header">
                            <h5>{{ 'Payment Settings' }}</h5>
                            <small
                                class="text-secondary font-weight-bold">{{ __('These details will be used to collect invoice payments. Each invoice will have a payment button based on the below configuration.') }}</small>
                        </div>
                        @php
                            $companyPaymentSettingsBaseRouteName          = ViewsConstants::CP.'.payment.settings';
                            $companyPaymentSettingsKebabRouteName         = Str::kebab($companyPaymentSettingsBaseRouteName);
                            $companyPaymentSettingsResolvedRouteName      = Route::has($companyPaymentSettingsBaseRouteName)
                                ? $companyPaymentSettingsBaseRouteName
                                : (Route::has($companyPaymentSettingsKebabRouteName) ? $companyPaymentSettingsKebabRouteName : null);
                            $companyPaymentSettingsRouteArray             = $companyPaymentSettingsResolvedRouteName ? [$companyPaymentSettingsResolvedRouteName] : ['#'];
                            $companyPaymentSettingsUrl                    = $companyPaymentSettingsResolvedRouteName ? route($companyPaymentSettingsResolvedRouteName) : '#';
                            $companyPaymentSettingsGuardMsg               = Utility::fetchLinkMessage($lang, 'company', 'company_payment_settings_route_unavailable') ?? 'Company payment settings route is unavailable. Please contact technical support or your domain administrator.';
                            $companyPaymentSettingsFormId                 = 'company-payment-settings-form';
                        @endphp
                        {!! Form::model($setting, [
                            'route'          => $companyPaymentSettingsRouteArray,
                            'method'         => 'POST',
                            'id'             => $companyPaymentSettingsFormId,
                            'data-url'       => $companyPaymentSettingsUrl,
                            'data-guard-msg' => $companyPaymentSettingsGuardMsg
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/settings/companies/index.js') }}"></script>
                            @endpush
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="faq justify-content-center">
                                            <div class="row">
                                                <div class="col-12">
                                                    @php
                                                        $settings = $admin_payment_setting ?? [];
                                                        $gateways = [
                                                            'manually' => [
                                                                'label'   => 'Manually',
                                                                'enabled' => 'is_manually_payment_enabled',
                                                                'desc'    => 'Requesting manual payment for the planned amount for the subscriptions plan.',
                                                                'fields'  => [],
                                                            ],
                                                            'bank' => [
                                                                'label'   => 'Bank Transfer',
                                                                'enabled' => 'is_bank_transfer_enabled',
                                                                'fields'  => [
                                                                    [
                                                                        'type' => 'textarea',
                                                                        'name' => 'bank_details',
                                                                        'label' => 'Bank Details',
                                                                        'rows' => 4,
                                                                        'col'  => 12,
                                                                        'help' => 'Example : Bank : bank name </br> Account Number : 0000 0000 </br>',
                                                                        'placeholder' => 'Enter Your Bank Details',
                                                                    ],
                                                                ],
                                                            ],
                                                            'stripe' => [
                                                                'label'   => 'Stripe',
                                                                'enabled' => 'is_stripe_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'stripe_key',    'label' => 'Stripe Key',    'col' => 6, 'placeholder' => 'Enter Stripe Key'],
                                                                    ['type' => 'text', 'name' => 'stripe_secret', 'label' => 'Stripe Secret', 'col' => 6, 'placeholder' => 'Enter Stripe Secret'],
                                                                ],
                                                            ],
                                                            'paypal' => [
                                                                'label'   => 'Paypal',
                                                                'enabled' => 'is_paypal_enabled',
                                                                'radios'  => [
                                                                    'name' => 'paypal_mode',
                                                                    'default' => 'sandbox',
                                                                    'options' => [
                                                                        ['value' => 'sandbox', 'label' => 'Sandbox'],
                                                                        ['value' => 'live',    'label' => 'Live'],
                                                                    ],
                                                                ],
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'paypal_client_id',  'label' => 'Client ID',  'col' => 6, 'placeholder' => 'Client ID'],
                                                                    ['type' => 'text', 'name' => 'paypal_secret_key', 'label' => 'Secret Key', 'col' => 6, 'placeholder' => 'Secret Key'],
                                                                ],
                                                            ],
                                                            'paystack' => [
                                                                'label'   => 'Paystack',
                                                                'enabled' => 'is_paystack_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'paystack_public_key', 'label' => 'Public Key', 'col' => 6, 'placeholder' => 'Public Key'],
                                                                    ['type' => 'text', 'name' => 'paystack_secret_key', 'label' => 'Secret Key', 'col' => 6, 'placeholder' => 'Secret Key'],
                                                                ],
                                                            ],
                                                            'flutterwave' => [
                                                                'label'   => 'Flutterwave',
                                                                'enabled' => 'is_flutterwave_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'flutterwave_public_key', 'label' => 'Public Key', 'col' => 6, 'placeholder' => 'Public Key'],
                                                                    ['type' => 'text', 'name' => 'flutterwave_secret_key', 'label' => 'Secret Key', 'col' => 6, 'placeholder' => 'Secret Key'],
                                                                ],
                                                            ],
                                                            'razorpay' => [
                                                                'label'   => 'Razorpay',
                                                                'enabled' => 'is_razorpay_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'razorpay_public_key', 'label' => 'Public Key', 'col' => 6, 'placeholder' => 'Public Key'],
                                                                    ['type' => 'text', 'name' => 'razorpay_secret_key', 'label' => 'Secret Key', 'col' => 6, 'placeholder' => 'Secret Key'],
                                                                ],
                                                            ],
                                                            'paytm' => [
                                                                'label'   => 'Paytm',
                                                                'enabled' => 'is_paytm_enabled',
                                                                'radios'  => [
                                                                    'name' => 'paytm_mode',
                                                                    'default' => 'local',
                                                                    'options' => [
                                                                        ['value' => 'local',       'label' => 'Local'],
                                                                        ['value' => 'production',  'label' => 'Production'],
                                                                    ],
                                                                ],
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'paytm_merchant_id',   'label' => 'Merchant ID',   'col' => 4, 'placeholder' => 'Merchant ID'],
                                                                    ['type' => 'text', 'name' => 'paytm_merchant_key',  'label' => 'Merchant Key',  'col' => 4, 'placeholder' => 'Merchant Key'],
                                                                    ['type' => 'text', 'name' => 'paytm_industry_type', 'label' => 'Industry Type', 'col' => 4, 'placeholder' => 'Industry Type'],
                                                                ],
                                                            ],
                                                            'mercado' => [
                                                                'label'   => 'Mercado Pago',
                                                                'enabled' => 'is_mercado_enabled',
                                                                'radios'  => [
                                                                    'name' => 'mercado_mode',
                                                                    'default' => 'sandbox',
                                                                    'options' => [
                                                                        ['value' => 'sandbox', 'label' => 'Sandbox'],
                                                                        ['value' => 'live',    'label' => 'Live'],
                                                                    ],
                                                                ],
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'mercado_access_token', 'label' => 'Access Token', 'col' => 6, 'placeholder' => 'Access Token'],
                                                                ],
                                                            ],
                                                            'mollie' => [
                                                                'label'   => 'Mollie',
                                                                'enabled' => 'is_mollie_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'mollie_api_key',    'label' => 'Mollie Api Key',    'col' => 6, 'placeholder' => 'Mollie Api Key'],
                                                                    ['type' => 'text', 'name' => 'mollie_profile_id', 'label' => 'Mollie Profile Id', 'col' => 6, 'placeholder' => 'Mollie Profile Id'],
                                                                    ['type' => 'text', 'name' => 'mollie_partner_id', 'label' => 'Mollie Partner Id', 'col' => 6, 'placeholder' => 'Mollie Partner Id'],
                                                                ],
                                                            ],
                                                            'skrill' => [
                                                                'label'   => 'Skrill',
                                                                'enabled' => 'is_skrill_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'email', 'name' => 'skrill_email', 'label' => 'Skrill Email', 'col' => 6, 'placeholder' => 'Skrill Email'],
                                                                ],
                                                            ],
                                                            'coingate' => [
                                                                'label'   => 'CoinGate',
                                                                'enabled' => 'is_coingate_enabled',
                                                                'radios'  => [
                                                                    'name' => 'coingate_mode',
                                                                    'default' => 'sandbox',
                                                                    'options' => [
                                                                        ['value' => 'sandbox', 'label' => 'Sandbox'],
                                                                        ['value' => 'live',    'label' => 'Live'],
                                                                    ],
                                                                ],
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'coingate_auth_token', 'label' => 'CoinGate Auth Token', 'col' => 6, 'placeholder' => 'CoinGate Auth Token'],
                                                                ],
                                                            ],
                                                            'paymentwall' => [
                                                                'label'   => 'PaymentWall',
                                                                'enabled' => 'is_paymentwall_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'paymentwall_public_key', 'label' => 'Public Key',  'col' => 6, 'placeholder' => 'Public Key'],
                                                                    ['type' => 'text', 'name' => 'paymentwall_secret_key', 'label' => 'Private Key', 'col' => 6, 'placeholder' => 'Private Key'],
                                                                ],
                                                            ],
                                                            'toyyibpay' => [
                                                                'label'   => 'Toyyibpay',
                                                                'enabled' => 'is_toyyibpay_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'toyyibpay_category_code', 'label' => 'Category Key', 'col' => 6, 'placeholder' => 'Category Key'],
                                                                    ['type' => 'text', 'name' => 'toyyibpay_secret_key',    'label' => 'Secret Key',   'col' => 6, 'placeholder' => 'Secret Key'],
                                                                ],
                                                            ],
                                                            'payfast' => [
                                                                'label'   => 'PayFast',
                                                                'enabled' => 'is_payfast_enabled',
                                                                'radios'  => [
                                                                    'name' => 'payfast_mode',
                                                                    'default' => 'sandbox',
                                                                    'options' => [
                                                                        ['value' => 'sandbox', 'label' => 'Sandbox'],
                                                                        ['value' => 'live',    'label' => 'Live'],
                                                                    ],
                                                                ],
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'payfast_merchant_id',  'label' => 'Merchant ID',     'col' => 4, 'placeholder' => 'Merchant ID'],
                                                                    ['type' => 'text', 'name' => 'payfast_merchant_key', 'label' => 'Merchant Key',    'col' => 4, 'placeholder' => 'Merchant Key'],
                                                                    ['type' => 'text', 'name' => 'payfast_signature',    'label' => 'Salt Passphrase', 'col' => 4, 'placeholder' => 'Salt Passphrase'],
                                                                ],
                                                            ],
                                                            'iyzipay' => [
                                                                'label'   => 'Iyzipay',
                                                                'enabled' => 'is_iyzipay_enabled',
                                                                'radios'  => [
                                                                    'name' => 'iyzipay_mode',
                                                                    'default' => 'sandbox',
                                                                    'options' => [
                                                                        ['value' => 'sandbox', 'label' => 'Sandbox'],
                                                                        ['value' => 'live',    'label' => 'Live'],
                                                                    ],
                                                                ],
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'iyzipay_public_key', 'label' => 'Public Key', 'col' => 6, 'placeholder' => 'Public Key'],
                                                                    ['type' => 'text', 'name' => 'iyzipay_secret_key', 'label' => 'Secret Key', 'col' => 6, 'placeholder' => 'Secret Key'],
                                                                ],
                                                            ],
                                                            'sspay' => [
                                                                'label'   => 'SSPay',
                                                                'enabled' => 'is_sspay_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'sspay_category_code', 'label' => 'Category Code', 'col' => 6, 'placeholder' => 'Category Code'],
                                                                    ['type' => 'text', 'name' => 'sspay_secret_key',    'label' => 'Secret Key',    'col' => 6, 'placeholder' => 'Secret Key'],
                                                                ],
                                                            ],
                                                            'paytab' => [
                                                                'label'   => 'PayTab',
                                                                'enabled' => 'is_paytab_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'paytab_profile_id', 'label' => 'Profile Id', 'col' => 6, 'placeholder' => 'Profile Id'],
                                                                    ['type' => 'text', 'name' => 'paytab_server_key', 'label' => 'Server Key', 'col' => 6, 'placeholder' => 'Server Key'],
                                                                    ['type' => 'text', 'name' => 'paytab_region',     'label' => 'Region',     'col' => 6, 'placeholder' => 'Region'],
                                                                ],
                                                            ],
                                                            'benefit' => [
                                                                'label'   => 'Benefit',
                                                                'enabled' => 'is_benefit_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'benefit_api_key',    'label' => 'Benefit Key',        'col' => 6, 'placeholder' => 'Enter Benefit Key'],
                                                                    ['type' => 'text', 'name' => 'benefit_secret_key', 'label' => 'Benefit Secret Key', 'col' => 6, 'placeholder' => 'Enter Benefit Secret key'],
                                                                ],
                                                            ],
                                                            'cashfree' => [
                                                                'label'   => 'Cashfree',
                                                                'enabled' => 'is_cashfree_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'cashfree_api_key',    'label' => 'Cashfree Key',        'col' => 6, 'placeholder' => 'Enter Cashfree Key'],
                                                                    ['type' => 'text', 'name' => 'cashfree_secret_key', 'label' => 'Cashfree Secret Key', 'col' => 6, 'placeholder' => 'Enter Cashfree Secret key'],
                                                                ],
                                                            ],
                                                            'aamarpay' => [
                                                                'label'   => 'Aamarpay',
                                                                'enabled' => 'is_aamarpay_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'aamarpay_store_id',       'label' => 'Store Id',       'col' => 6, 'placeholder' => 'Enter Store Id'],
                                                                    ['type' => 'text', 'name' => 'aamarpay_signature_key',  'label' => 'Signature Key',  'col' => 6, 'placeholder' => 'Enter Signature Key'],
                                                                    ['type' => 'text', 'name' => 'aamarpay_description',    'label' => 'Description',    'col' => 6, 'placeholder' => 'Enter Description'],
                                                                ],
                                                            ],
                                                            'paytr' => [
                                                                'label'   => 'PayTR',
                                                                'enabled' => 'is_paytr_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'paytr_merchant_id',   'label' => 'Merchant Id',   'col' => 4, 'placeholder' => 'Merchant Id'],
                                                                    ['type' => 'text', 'name' => 'paytr_merchant_key',  'label' => 'Merchant Key',  'col' => 4, 'placeholder' => 'Merchant Key'],
                                                                    ['type' => 'text', 'name' => 'paytr_merchant_salt', 'label' => 'Merchant Salt', 'col' => 4, 'placeholder' => 'Merchant Salt'],
                                                                ],
                                                            ],
                                                            'yookassa' => [
                                                                'label'   => 'Yookassa',
                                                                'enabled' => 'is_yookassa_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'yookassa_shop_id', 'label' => 'Shop ID Key', 'col' => 6, 'placeholder' => 'Shop ID Key'],
                                                                    ['type' => 'text', 'name' => 'yookassa_secret',  'label' => 'Secret Key',  'col' => 6, 'placeholder' => 'Secret Key'],
                                                                ],
                                                            ],
                                                            'midtrans' => [
                                                                'label'   => 'Midtrans',
                                                                'enabled' => 'is_midtrans_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'midtrans_secret', 'label' => 'Secret Key', 'col' => 6, 'placeholder' => 'Secret Key'],
                                                                ],
                                                            ],
                                                            'xendit' => [
                                                                'label'   => 'Xendit',
                                                                'enabled' => 'is_xendit_enabled',
                                                                'fields'  => [
                                                                    ['type' => 'text', 'name' => 'xendit_api',   'label' => 'API Key', 'col' => 6, 'placeholder' => 'API Key'],
                                                                    ['type' => 'text', 'name' => 'xendit_token', 'label' => 'Token',   'col' => 6, 'placeholder' => 'Token'],
                                                                ],
                                                            ],
                                                        ];
                                                    @endphp
                                                    <div class="accordion accordion-flush setting-accordion" id="accordionExample">
                                                        @foreach($gateways as $key => $gw)
                                                            @php
                                                                $slug        = Str::slug($key);
                                                                $collapseId  = 'collapse-'.$slug;
                                                                $headingId   = 'heading-'.$slug;
                                                                $enabledKey  = $gw['enabled'];
                                                                $enabledVal  = old($enabledKey, data_get($settings, $enabledKey));
                                                                $isChecked   = ($enabledVal === 'on');
                                                            @endphp
                                                            <div class="accordion-item">
                                                                <h2 class="accordion-header" id="{{ $headingId }}">
                                                                    <button class="accordion-button collapsed" type="button"
                                                                            data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                                                            aria-expanded="false" aria-controls="{{ $collapseId }}">
                                                                        <span class="d-flex align-items-center">{{ __($gw['label']) }}</span>

                                                                        <div class="d-flex align-items-center ms-auto">
                                                                            <span class="me-2">{{ __('Enable') }}:</span>
                                                                            <div class="form-check form-switch custom-switch-v1">
                                                                                <input type="hidden" name="{{ $enabledKey }}" value="off">
                                                                                <input type="checkbox"
                                                                                    class="form-check-input input-primary"
                                                                                    id="switch-{{ $slug }}"
                                                                                    name="{{ $enabledKey }}"
                                                                                    @checked($isChecked)>
                                                                            </div>
                                                                        </div>
                                                                    </button>
                                                                </h2>
                                                                <div id="{{ $collapseId }}" class="accordion-collapse collapse"
                                                                    aria-labelledby="{{ $headingId }}" data-bs-parent="#accordionExample">
                                                                    <div class="accordion-body">
                                                                        @if(!empty($gw['desc']))
                                                                            <div class="row gy-4">
                                                                                <div class="col-lg-12">
                                                                                    <div class="input-edits">
                                                                                        <small class="text-md">{!! __($gw['desc']) !!}</small>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                        @if(isset($gw['radios']))
                                                                            @php
                                                                                $radioName = $gw['radios']['name'];
                                                                                $radioDefault = $gw['radios']['default'] ?? null;
                                                                                $currentRadio = old($radioName, data_get($settings, $radioName));
                                                                                if($currentRadio === null || $currentRadio === '')
                                                                                    $currentRadio = $radioDefault;
                                                                            @endphp
                                                                            <div class="d-flex mb-3">
                                                                                @foreach(($gw['radios']['options'] ?? []) as $opt)
                                                                                    @php
                                                                                        $rid = 'radio-'.$slug.'-'.$opt['value'];
                                                                                    @endphp
                                                                                    <div class="me-2" style="margin-right: 15px;">
                                                                                        <div class="border card p-1">
                                                                                            <div class="form-check">
                                                                                                <label class="form-check-label text-dark" for="{{ $rid }}">
                                                                                                    <input type="radio" id="{{ $rid }}"
                                                                                                        name="{{ $radioName }}" value="{{ $opt['value'] }}"
                                                                                                        class="form-check-input"
                                                                                                        @checked($currentRadio === $opt['value'])>
                                                                                                    {{ __($opt['label']) }}
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                        @if(!empty($gw['fields']))
                                                                            <div class="row gy-4">
                                                                                @foreach($gw['fields'] as $field)
                                                                                    @php
                                                                                        $type        = $field['type'] ?? 'text';
                                                                                        $name        = $field['name'];
                                                                                        $label       = __($field['label'] ?? Str::headline($name));
                                                                                        $placeholder = __($field['placeholder'] ?? $label);
                                                                                        $col         = (int)($field['col'] ?? 12);
                                                                                        $rows        = (int)($field['rows'] ?? 3);
                                                                                        $value       = old($name, data_get($settings, $name, ''));
                                                                                    @endphp
                                                                                    <div class="col-lg-{{ $col }}">
                                                                                        <div class="input-edits">
                                                                                            <div class="form-group">
                                                                                                <label class="col-form-label" for="{{ $name }}">{{ $label }}</label>
                                                                                                @if($type === 'textarea')
                                                                                                    <textarea class="form-control"
                                                                                                            id="{{ $name }}" name="{{ $name }}"
                                                                                                            rows="{{ $rows }}"
                                                                                                            placeholder="{{ $placeholder }}">{{ $value }}</textarea>
                                                                                                @else
                                                                                                    <input class="form-control"
                                                                                                        id="{{ $name }}" name="{{ $name }}"
                                                                                                        type="{{ $type }}"
                                                                                                        value="{{ $value }}"
                                                                                                        placeholder="{{ $placeholder }}">
                                                                                                @endif
                                                                                                @if(!empty($field['help']))
                                                                                                    <small class="text-xs">{!! __($field['help']) !!}</small>
                                                                                                @endif
                                                                                                @error($name)
                                                                                                    <span class="invalid-feedback d-block">{{ !empty($message) ? $message : __('No message available')  }}</span>
                                                                                                @enderror
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                    @php
                        $pusherFields = [
                            ['name' => 'pusher_app_id',      'label' => __('Pusher App Id')],
                            ['name' => 'pusher_app_key',     'label' => __('Pusher App Key')],
                            ['name' => 'pusher_app_secret',  'label' => __('Pusher App Secret')],
                            ['name' => 'pusher_app_cluster', 'label' => __('Pusher App Cluster')],
                        ];
                    @endphp
                    <div id="pusher-settings" class="{{ VC::CD }}">
                        <div class="{{ VC::CD }}-header">
                            <h5>{{ __('Pusher Settings') }}</h5>
                        </div>
                        @php
                            $settingsPusherBaseName            = ViewsConstants::SET . '.pusher';
                            $settingsPusherKebabName           = Str::kebab($settingsPusherBaseName);
                            $settingsPusherResolvedName        = Route::has($settingsPusherBaseName)
                                ? $settingsPusherBaseName
                                : (Route::has($settingsPusherKebabName) ? $settingsPusherKebabName : null);
                            $settingsPusherRouteArray          = $settingsPusherResolvedName ? [$settingsPusherResolvedName] : ['#'];
                            $settingsPusherUrl                 = $settingsPusherResolvedName ? route($settingsPusherResolvedName) : '#';
                            $settingsPusherGuardMsg            = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'settings_pusher_route_unavailable') ?? 'Settings pusher route is unavailable. Please contact technical support or your domain administrator.';
                            $settingsPusherFormId              = 'settings-pusher-form';
                        @endphp
                        {!! Form::model($settings, [
                            'route'          => $settingsPusherRouteArray,
                            'method'         => 'post',
                            'id'             => $settingsPusherFormId,
                            'data-url'       => $settingsPusherUrl,
                            'data-guard-msg' => $settingsPusherGuardMsg
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/settings/pusher.js') }}"></script>
                            @endpush
                            @csrf
                            <div class="{{ VC::CD }}-body">
                                <div class="{{ VC::RW }}">
                                    @foreach ($pusherFields as $field)
                                        <div class="{{ VC::CM6 }}">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label($field['name'], $field['label'], ['class' => VC::FM_LB]) }}
                                                {{ Form::text($field['name'], null, ['class' => VC::FM_CT . ' font-style']) }}
                                                @error($field['name'])
                                                    <span class="invalid-{{ $field['name'] }}" role="alert">
                                                        <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="{{ VC::CD }}-footer text-end">
                                <div class="{{ VC::FM_G }}">
                                    <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                    @php
                        $recaptchaEnabled = !empty($settings[SettingsConstants::RCPT_MDL])
                            && $settings[SettingsConstants::RCPT_MDL] === 'on';
                        $fields = [
                            [
                                'name'        => 'google_recaptcha_key',
                                'label'       => __('Google Recaptcha Key'),
                                'placeholder' => __('Enter Google Recaptcha Key'),
                                'value'       => $settings[SettingsConstants::G_RCPT_K] ?? '',
                            ],
                            [
                                'name'        => 'google_recaptcha_secret',
                                'label'       => __('Google Recaptcha Secret'),
                                'placeholder' => __('Enter Google Recaptcha Secret'),
                                'value'       => $settings[SettingsConstants::G_RCPT_SC] ?? '',
                            ],
                        ];
                    @endphp
                    <div id="recaptcha_settings" class="{{ VC::CD }}">
                        @php
                            $settingsRecaptchaStoreBaseName             = ViewsConstants::SET . '.recaptcha.store';
                            $settingsRecaptchaStoreKebabName            = Str::kebab($settingsRecaptchaStoreBaseName);
                            $settingsRecaptchaStoreResolvedName         = Route::has($settingsRecaptchaStoreBaseName)
                                ? $settingsRecaptchaStoreBaseName
                                : (Route::has($settingsRecaptchaStoreKebabName) ? $settingsRecaptchaStoreKebabName : null);
                            $settingsRecaptchaStoreRouteArray           = $settingsRecaptchaStoreResolvedName ? [$settingsRecaptchaStoreResolvedName] : ['#'];
                            $settingsRecaptchaStoreUrl                  = $settingsRecaptchaStoreResolvedName ? route($settingsRecaptchaStoreResolvedName) : '#';
                            $settingsRecaptchaStoreGuardMsg             = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'settings_recaptcha_store_route_unavailable') ?? 'Settings reCAPTCHA store route is unavailable. Please contact technical support or your domain administrator.';
                            $settingsRecaptchaStoreFormId               = 'settings-recaptcha-store-form';
                        @endphp
                        {!! Form::open([
                            'route'          => $settingsRecaptchaStoreRouteArray,
                            'method'         => 'post',
                            'accept-charset' => 'UTF-8',
                            'id'             => $settingsRecaptchaStoreFormId,
                            'data-url'       => $settingsRecaptchaStoreUrl,
                            'data-guard-msg' => $settingsRecaptchaStoreGuardMsg
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/settings/recaptcha.js') }}"></script>
                            @endpush
                            @csrf
                            <div class="{{ VC::CD }}-header">
                                <div class="{{ VC::RW }}">
                                    <div class="col-6">
                                        <h5 class="mb-2">{{ __('ReCaptcha Settings') }}</h5>
                                        <a href="https://phppot.com/php/how-to-get-google-recaptcha-site-and-secret-key/"
                                        target="_blank" class="text-dark">
                                            <small>({{ __('How to Get Google reCaptcha Site and Secret key') }})</small>
                                        </a>
                                    </div>
                                    <div class="col switch-width text-end">
                                        <div class="{{ VC::FM_G }} {{ VC::MB0 }}">
                                            <div class="{{ VC::CST_CTL }} custom-switch">
                                                <input
                                                    type="checkbox"
                                                    data-toggle="switchbutton"
                                                    data-onstyle="primary"
                                                    name="recaptcha_module"
                                                    id="recaptcha_module"
                                                    {{ $recaptchaEnabled ? 'checked="checked"' : '' }}
                                                >
                                                <label class="{{ VC::CST_LB }}" for="recaptcha_module"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::CD }}-body">
                                <div class="{{ VC::RW }}">
                                    @foreach ($fields as $field)
                                        <div class="{{ VC::CM6 }}">
                                            <div class="{{ VC::FM_G }}">
                                                <label for="{{ $field['name'] }}" class="{{ VC::FM_LB }}">
                                                    {{ $field['label'] }}
                                                </label>
                                                <input
                                                    id="{{ $field['name'] }}"
                                                    name="{{ $field['name'] }}"
                                                    type="text"
                                                    class="{{ VC::FM_CT }}"
                                                    placeholder="{{ $field['placeholder'] }}"
                                                    value="{{ old($field['name'], $field['value']) }}"
                                                >
                                                @error($field['name'])
                                                    <span class="invalid-{{ $field['name'] }}" role="alert">
                                                        <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="{{ VC::CD }}-footer text-end">
                                <div class="{{ VC::FM_G }}">
                                    <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                    @php
                        $storage = $settings[SettingsConstants::STR_STT] ?? 'local';
                        $s3Fields = [
                            ['name' => 's3_key',      'label' => __('S3 Key'),      'key' => SettingsConstants::S3_K],
                            ['name' => 's3_secret',   'label' => __('S3 Secret'),   'key' => SettingsConstants::S3_SC],
                            ['name' => 's3_region',   'label' => __('S3 Region'),   'key' => SettingsConstants::S3_RG],
                            ['name' => 's3_bucket',   'label' => __('S3 Bucket'),   'key' => SettingsConstants::S3_BK],
                            ['name' => 's3_url',      'label' => __('S3 URL'),      'key' => SettingsConstants::S3_URL],
                            ['name' => 's3_endpoint', 'label' => __('S3 Endpoint'), 'key' => SettingsConstants::S3_EP],
                        ];
                        $wasabiFields = [
                            ['name' => 'wasabi_key',     'label' => __('Wasabi Key'),     'key' => SettingsConstants::WSB_K],
                            ['name' => 'wasabi_secret',  'label' => __('Wasabi Secret'),  'key' => SettingsConstants::WSB_SC],
                            ['name' => 'wasabi_region',  'label' => __('Wasabi Region'),  'key' => SettingsConstants::WSB_RG],
                            ['name' => 'wasabi_bucket',  'label' => __('Wasabi Bucket'),  'key' => SettingsConstants::WSB_BK],
                            ['name' => 'wasabi_url',     'label' => __('Wasabi URL'),     'key' => SettingsConstants::WSB_URL],
                            ['name' => 'wasabi_root',    'label' => __('Wasabi Root'),    'key' => SettingsConstants::WSB_RT],
                        ];
                        $generateBaseName = 'generate';
                        $generateKebabName = Str::kebab($generateBaseName);
                        $generateResolvedName = Route::has($generateBaseName) ? $generateBaseName : (Route::has($generateKebabName) ? $generateKebabName : null);
                        $generateSeoUrl = $generateResolvedName ? route($generateResolvedName, ['seo']) : '#';
                        $generateSeoGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'generate_ai_seo_route_unavailable') ?? 'Generate AI SEO route is unavailable. Please contact technical support or your domain administrator.';
                        $generateSeoLinkId = 'generate-ai-seo-link';
                        $generateCookieUrl = $generateResolvedName ? route($generateResolvedName, ['cookie']) : '#';
                        $generateCookieGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'generate_ai_cookie_route_unavailable') ?? 'Generate AI cookie route is unavailable. Please contact technical support or your domain administrator.';
                        $generateCookieLinkId = 'generate-ai-cookie-link';
                        $settingsSeoStoreBase = ViewsConstants::SET . '.seo.store';
                        $settingsSeoStoreKebab = Str::kebab($settingsSeoStoreBase);
                        $settingsSeoStoreResolved = Route::has($settingsSeoStoreBase) ? $settingsSeoStoreBase : (Route::has($settingsSeoStoreKebab) ? $settingsSeoStoreKebab : null);
                        $settingsSeoStoreRouteArr = $settingsSeoStoreResolved ? [$settingsSeoStoreResolved] : ['#'];
                        $settingsSeoStoreUrl = $settingsSeoStoreResolved ? route($settingsSeoStoreResolved) : '#';
                        $settingsSeoStoreGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'settings_seo_store_route_unavailable') ?? 'Settings SEO store route is unavailable. Please contact technical support or your domain administrator.';
                        $settingsSeoStoreFormId = 'settings-seo-store-form';
                        $settingsCookiesStoreBase = ViewsConstants::SET . '.cookies.store';
                        $settingsCookiesStoreKebab = Str::kebab($settingsCookiesStoreBase);
                        $settingsCookiesStoreResolved = Route::has($settingsCookiesStoreBase) ? $settingsCookiesStoreBase : (Route::has($settingsCookiesStoreKebab) ? $settingsCookiesStoreKebab : null);
                        $settingsCookiesStoreRouteArr = $settingsCookiesStoreResolved ? [$settingsCookiesStoreResolved] : ['#'];
                        $settingsCookiesStoreUrl = $settingsCookiesStoreResolved ? route($settingsCookiesStoreResolved) : '#';
                        $settingsCookiesStoreGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'settings_cookies_store_route_unavailable') ?? 'Settings cookies store route is unavailable. Please contact technical support or your domain administrator.';
                        $settingsCookiesStoreFormId = 'settings-cookies-store-form';
                        $settingsChatGptBase = ViewsConstants::SET . '.chatgpt.settings';
                        $settingsChatGptKebab = Str::kebab($settingsChatGptBase);
                        $settingsChatGptResolved = Route::has($settingsChatGptBase) ? $settingsChatGptBase : (Route::has($settingsChatGptKebab) ? $settingsChatGptKebab : null);
                        $settingsChatGptRouteArr = $settingsChatGptResolved ? [$settingsChatGptResolved] : ['#'];
                        $settingsChatGptUrl = $settingsChatGptResolved ? route($settingsChatGptResolved) : '#';
                        $settingsChatGptGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'settings_chatgpt_settings_route_unavailable') ?? 'Settings ChatGPT route is unavailable. Please contact technical support or your domain administrator.';
                        $settingsChatGptFormId = 'settings-chatgpt-settings-form';
                    @endphp
                    <div id="seo-settings" class="{{ VC::CD }}">
                        <div class="{{ VC::CD }}-header {{ VC::DFL_JCB }}">
                            <h5>{{ __('SEO Settings') }}</h5>
                            @if(!empty($settings['chat_gpt_key']))
                                <div class="{{ VC::DFL_JCB }}">
                                    <div class="mt-0">
                                        <a
                                            id="{{ $generateSeoLinkId }}"
                                            data-size="md"
                                            class="{{ VC::BT_SM_PM }} text-white"
                                            data-ajax-popup-over="true"
                                            data-url="{{ $generateSeoUrl }}"
                                            data-guard-msg="{{ $generateSeoGuardMsg }}"
                                            data-bs-placement="top"
                                            data-title="{{ __('Generate content with AI') }}"
                                        >
                                            <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {!! Form::open([
                            'route'          => $settingsSeoStoreRouteArr,
                            'method'         => 'post',
                            'enctype'        => 'multipart/form-data',
                            'id'             => $settingsSeoStoreFormId,
                            'data-url'       => $settingsSeoStoreUrl,
                            'data-guard-msg' => $settingsSeoStoreGuardMsg
                        ]) !!}
                        @csrf
                        <div class="{{ VC::CD }}-body">
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CM6 }}">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('Meta Keywords', __('Meta Keywords'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text(
                                            SettingsConstants::MT_TTL,
                                            $settings[SettingsConstants::MT_TTL] ?? '',
                                            ['class' => VC::FM_CT, 'placeholder' => 'Meta Keywords']
                                        ) }}
                                    </div>
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('Meta Description', __('Meta Description'), ['class' => VC::FM_LB]) }}
                                        {{ Form::textarea(
                                            SettingsConstants::MT_DSC_K,
                                            $settings[SettingsConstants::MT_DSC_K] ?? '',
                                            ['class' => VC::FM_CT, 'placeholder' => 'Meta Description','rows' => 7]
                                        ) }}
                                    </div>
                                </div>

                                <div class="{{ VC::CM6 }}">
                                    <div class="{{ VC::FM_G }} {{ VC::MB0 }}">
                                        {{ Form::label('Meta Image', __('Meta Image'), ['class' => VC::FM_LB]) }}
                                    </div>

                                    <div class="setting-card">
                                        <div class="logo-content">
                                            <img id="image2" src="{{ $meta_image . '/' . (!empty($settings['meta_image']) ? $settings['meta_image'] : 'meta_image.png') }}" class="img_setting seo_image">
                                        </div>
                                        <div class="choose-files mt-4">
                                            <label for="meta_image">
                                                <div class="bg-primary company_favicon_update">
                                                    <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                </div>
                                                <input type="file" class="{{ VC::FM_CT }} file" id="meta_image" name="meta_image" data-filename="meta_image">
                                            </label>
                                        </div>

                                        @error('meta_image')
                                            <div class="{{ VC::RW }}">
                                                <span class="invalid-logo" role="alert">
                                                    <strong class="text-danger">{{ !empty($message) ? $message : __('No message available')  }}</strong>
                                                </span>
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="{{ VC::CD }}-footer text-end">
                            <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                        </div>
                        {{ Form::close() }}
                    </div>
                    <div class="{{ VC::CD }}" id="cookie-settings">
                        {!! Form::model($settings, [
                            'route'          => $settingsCookiesStoreRouteArr,
                            'method'         => 'post',
                            'id'             => $settingsCookiesStoreFormId,
                            'data-url'       => $settingsCookiesStoreUrl,
                            'data-guard-msg' => $settingsCookiesStoreGuardMsg
                        ]) !!}
                        <div class="{{ VC::CD }}-header flex-column flex-lg-row {{ VC::DFL_AIC_JCB }}">
                            <h5>{{ __('Cookie Settings') }}</h5>
                            <div class="{{ VC::DFL_AIC }}">
                                {{ Form::label('enable_cookie', __('Enable cookie'), ['class' => VC::FM_LB . ' p-0 fw-bold me-3']) }}
                                <div class="{{ VC::CST_CTL }} custom-switch me-2" onclick="enablecookie()">
                                    <input type="checkbox" data-toggle="switchbutton" data-onstyle="primary" name="enable_cookie" class="form-check-input input-primary" id="enable_cookie" {{ ($settings['enable_cookie'] ?? 'off') === 'on' ? 'checked' : '' }}>
                                    <label class="custom-control-label mb-1" for="enable_cookie"></label>
                                </div>
                            </div>
                        </div>

                        <div class="{{ VC::CD }}-body cookieDiv {{ ($settings['enable_cookie'] ?? 'off') === 'off' ? 'disabledCookie' : '' }}">
                            <div class="{{ VC::RW }}">
                                <div class="text-end">
                                    @if(!empty($settings['chat_gpt_key']))
                                        <div class="mt-0">
                                            <a
                                                id="{{ $generateCookieLinkId }}"
                                                data-size="md"
                                                class="{{ VC::BT_SM_PM }} text-white"
                                                data-ajax-popup-over="true"
                                                data-url="{{ $generateCookieUrl }}"
                                                data-guard-msg="{{ $generateCookieGuardMsg }}"
                                                data-bs-placement="top"
                                                data-title="{{ __('Generate content with AI') }}"
                                            >
                                                <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CM6 }}">
                                    <div class="form-check form-switch custom-switch-v1" id="cookie_log">
                                        <input type="checkbox" name="cookie_logging" class="form-check-input input-primary cookie_setting" id="cookie_logging" {{ ($settings['cookie_logging'] ?? 'off') === 'on' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="cookie_logging">{{ __('Enable logging') }}</label>
                                    </div>

                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('cookie_title', __('Cookie Title'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('cookie_title', null, ['class' => VC::FM_CT . ' cookie_setting']) }}
                                    </div>

                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('cookie_description', __('Cookie Description'), ['class' => VC::FM_LB]) }}
                                        {!! Form::textarea('cookie_description', null, ['class' => VC::FM_CT . ' cookie_setting', 'rows' => 3]) !!}
                                    </div>
                                </div>

                                <div class="{{ VC::CM6 }}">
                                    <div class="form-check form-switch custom-switch-v1">
                                        <input type="checkbox" name="necessary_cookies" class="form-check-input input-primary" id="necessary_cookies" checked onclick="return false">
                                        <label class="form-check-label" for="necessary_cookies">{{ __('Strictly necessary cookies') }}</label>
                                    </div>

                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('strictly_cookie_title', __(' Strictly Cookie Title'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('strictly_cookie_title', null, ['class' => VC::FM_CT . ' cookie_setting']) }}
                                    </div>

                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('strictly_cookie_description', __('Strictly Cookie Description'), ['class' => VC::FM_LB]) }}
                                        {!! Form::textarea('strictly_cookie_description', null, ['class' => VC::FM_CT . ' cookie_setting', 'rows' => 3]) !!}
                                    </div>
                                </div>

                                <div class="{{ VC::C12 }}">
                                    <h5>{{ __('More Information') }}</h5>
                                </div>

                                <div class="{{ VC::CM6 }}">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('more_information_description', __('Contact Us Description'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('more_information_description', null, ['class' => VC::FM_CT . ' cookie_setting']) }}
                                    </div>
                                </div>

                                <div class="{{ VC::CM6 }}">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('contactus_url', __('Contact Us URL'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('contactus_url', null, ['class' => VC::FM_CT . ' cookie_setting']) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="{{ VC::CD }}-footer {{ VC::MB3 }}">
                            <div class="{{ VC::RW }}">
                                <div class="col-6">
                                    @if(($settings['cookie_logging'] ?? 'off') === 'on')
                                        <label for="file" class="{{ VC::FM_LB }}">{{ __('Download cookie accepted data') }}</label>
                                        <a href="{{ asset(Storage::url('uploads/sample')) . '/data.csv' }}" class="{{ VC::BT_PRM }} mr-3">
                                            <i class="{{ VC::TI_DWN }}"></i>
                                        </a>
                                    @endif
                                </div>
                                <div class="col-6 text-end">
                                    <input class="{{ VC::BT_PR_PR }} cookie_btn" type="submit" value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                    <div id="chat-gpt-settings" class="{{ VC::CD }}">
                        <div class="{{ VC::CD }}-header">
                            <h5>{{ __('Chat GPT Settings') }}</h5>
                        </div>
                        {!! Form::model($settings, [
                            'route'          => $settingsChatGptRouteArr,
                            'method'         => 'post',
                            'id'             => $settingsChatGptFormId,
                            'data-url'       => $settingsChatGptUrl,
                            'data-guard-msg' => $settingsChatGptGuardMsg
                        ]) !!}
                        <div class="{{ VC::CD }}-body">
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::FM_G }} {{ VC::CM12 }}">
                                    {{ Form::label('chat_gpt_key', __('Chat GPT API Key'), ['class' => VC::FM_LB]) }}
                                    {{ Form::text('chat_gpt_key', $settings['chat_gpt_key'] ?? '', ['class' => VC::FM_CT, 'placeholder' => __('Enter Chat GPT API Key')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::CD }}-footer text-end">
                            <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                        </div>
                        {{ Form::close() }}
                    </div>
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer src="{{ asset('assets/js/routes/settings/seo.js') }}"></script>
                    @endpush
                    @php
                        $cacheSettingsStoreBaseName         = 'cache.settings.store';
                        $cacheSettingsStoreKebabName        = Str::kebab($cacheSettingsStoreBaseName);
                        $cacheSettingsStoreResolvedName     = Route::has($cacheSettingsStoreBaseName)
                            ? $cacheSettingsStoreBaseName
                            : (Route::has($cacheSettingsStoreKebabName) ? $cacheSettingsStoreKebabName : null);
                        $cacheSettingsStoreRouteArray       = $cacheSettingsStoreResolvedName ? [$cacheSettingsStoreResolvedName] : ['#'];
                        $cacheSettingsStoreUrl              = $cacheSettingsStoreResolvedName ? route($cacheSettingsStoreResolvedName) : '#';
                        $cacheSettingsStoreGuardMsg         = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'cache_settings_store_route_unavailable') ?? 'Cache settings store route is unavailable. Please contact technical support or your domain administrator.';
                        $cacheSettingsStoreFormId           = 'cache-settings-store-form';
                    @endphp
                    <div class="{{ VC::CD }}" id="cache-settings">
                        <div class="{{ VC::CD }}-header">
                            <h5>{{ __('Cache Settings') }}</h5>
                            <small class="text-secondary font-weight-bold">
                                {{ __("This is a page meant for technically advanced users. If you are not familiar with caching, please consult the documentation or your technical support.") }}
                            </small>
                        </div>
                        {!! Form::open([
                            'route'          => $cacheSettingsStoreRouteArray,
                            'method'         => 'post',
                            'accept-charset' => 'UTF-8',
                            'id'             => $cacheSettingsStoreFormId,
                            'data-url'       => $cacheSettingsStoreUrl,
                            'data-guard-msg' => $cacheSettingsStoreGuardMsg
                        ]) !!}
                            @csrf
                            <div class="{{ VC::CD }}-body">
                                <div class="{{ VC::RW }}">
                                    <div class="col-12 {{ VC::FM_G }}">
                                        {{ Form::label('Current cache size', __('Current cache size'), ['class' => VC::FM_LB]) }}
                                        <div class="input-group mb-5">
                                            <input type="text" class="{{ VC::FM_CT }}" value="{{ $file_size }}" readonly>
                                            <div class="input-group-append">
                                                <span class="{{ VC::INP_GP_TXT }}" id="basic-addon6">{{ __('MB') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::CD }}-footer text-end">
                                <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Cache Clear') }}">
                            </div>
                        {{ Form::close() }}
                    </div>
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer src="{{ asset('assets/js/routes/settings/cache.js') }}"></script>
                    @endpush
                    {{--  End for all settings tab --}}
                </div>
            </div>
        </div>
    </div>
@endsection

