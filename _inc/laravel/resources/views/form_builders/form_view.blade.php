@php
$lang = Utility::fetchUserLang();
    $data             ??= [];
    $colorSettings    ??= [];
    $logo             ??= '';
    $company_favicon  ??= '';
    $favicon          ??= '';
    $meta_title       ??= '';
    $meta_desc        ??= '';
    $meta_image       ??= '';
    $meta_logo        ??= '';
    $get_cookie       ??= [];
    $faviconUrl       ??= '';
    $siteRtl          = $siteRtl ?? 'off';

    try {
        $data           = Utility::prepareCommonViewData() ?: [];
        $colorSettings  = $data[STG::CLR_STG] ?? [];
        $logo           = $data[STG::LOGO] ?? '';
        $company_favicon= $data[STG::FAV_ICN] ?? '';
        $favicon        = $data[STG::FAV_ICN] ?? '';
        $meta_title     = $data[STG::MT_TTL_K] ?? '';
        $meta_desc      = $data[STG::MT_DESC_LONG] ?? '';
        $meta_image     = $data[STG::MT_IMG_K] ?? '';
        $meta_logo      = $data[STG::MT_LOGO] ?? '';
        $get_cookie     = $data[STG::CK_STG] ?? [];
        $faviconUrl     = Utility::getCompanyLogo() ?: '';
    } catch (\Throwable $e) {
        Log::error('Failed preparing layout meta/view data', [
            'exception_class' => get_class($e),
            'message'         => $e->getMessage(),
            'file'            => $e->getFile(),
            'line'            => $e->getLine(),
        ]);
    }

    $data = Utility::fallbackSettings($data);

    $hasForm      = !empty($form ?? null) && data_get($form, 'id');
    $formIsActive = $hasForm ? (int) data_get($form, 'is_active', 0) === 1 : false;

    $formName     = $hasForm ? (data_get($form, 'name') ?: __('Untitled Form')) : __('Form unavailable');
    $codeValue    = $code ?? '';

    if ($hasForm) {
        $storeBase     = VW::FM . '.view.store';
        $storeKebab    = Str::kebab($storeBase);
        $storeResolved = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
        $storeUrl      = $storeResolved ? route($storeResolved) : '#';
        $storeGuardMsg = Utility::fetchLinkMessage(Utility::fetchUserLang(), VW::FM, 'view_store_route_unavailable') ?? __('Form submission route is unavailable. Please contact technical support or your domain administrator.');
        $formId        = 'fm-view-store-form';

        $fieldsIsList  = (is_array($objFields ?? null) && count($objFields ?? []) > 0)
                         || (($objFields ?? null) instanceof Collection && $objFields->isNotEmpty());
    }
@endphp
<html lang="{{ $lang ?? (str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DB::DEFAULT_LANG)) }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
    <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
    <head>
        <title>{{ (Utility::getValByName('title_text') ?: config('app.name', 'ERPNovaPrestech')) }} - {{ __('Form Builder') ?: 'Form Builder' }}</title>

        @include('fragments.std', ['meta_title' => $meta_title, 'meta_desc' => $meta_desc])
        @include('fragments.og',  ['meta_title' => $meta_title, 'meta_desc' => $meta_desc, 'meta_image' => $meta_image, 'meta_logo' => $meta_logo])
        @include('fragments.x',   ['meta_title' => $meta_title, 'meta_desc' => $meta_desc, 'meta_image' => $meta_image, 'meta_logo' => $meta_logo])
        @include('fragments.favicon', ['faviconUrl' => $faviconUrl])
        @include('fragments.stylesheets', ['settings' => $colorSettings])
    </head>
    <body class="theme-4">
        <div class="{{ VC::DSH_CTT }}">
            <div class="min-vh-100 py-5 {{ VC::DFL_AIC }}">
                <div class="{{ VC::W100 }}">
                    <div class="row {{ VC::JCC }}">
                        <div class="col-sm-8 {{ VC::CL5 }}">
                            <div class="row {{ VC::JCC }} {{ VC::MB3 }}">
                                <a class="{{ VC::NVB_BR }}" href="#">
                                    <img src="{{ asset(Storage::url('uploads/logo/'.STG::CPN_LG_DK_DEF)) }}" class="navbar-brand-img big-logo" alt="{{ __('Company logo') ?: 'Company logo' }}">
                                </a>
                            </div>

                            <div class="card shadow zindex-100 {{ VC::MB0 }}">
                                @if(!$hasForm)
                                    <div class="{{ VC::CD_BD }} px-md-5 py-5">
                                        <div class="page-title"><h5>{{ __('The requested form was not found or is unavailable.') }}</h5></div>
                                    </div>
                                @elseif(!$formIsActive)
                                    <div class="{{ VC::CD_BD }} px-md-5 py-5">
                                        <div class="page-title"><h5>{{ __('Form is not active.') ?: 'Form is not active.' }}</h5></div>
                                    </div>
                                @else
                                    {{ Form::open([
                                        'url'               => $storeUrl,
                                        'method'            => 'POST',
                                        'id'                => $formId,
                                        'data-url'          => $storeUrl,
                                        'data-guard-msg'    => $storeGuardMsg,
                                        'data-sv-localized' => 'true'
                                    ]) }}
                                        <div class="{{ VC::CD_BD }} px-md-5 py-5">
                                            <div class="{{ VC::MB4 }}">
                                                <h6 class="h3">{{ $formName }}</h6>
                                            </div>

                                            <input type="hidden" value="{{ $codeValue }}" name="code">
                                            @if($fieldsIsList ?? false)
                                                @foreach($objFields as $objField)
                                                    @php
                                                        try {
                                                            $fldId    = data_get($objField, 'id');
                                                            $fldType  = data_get($objField, 'type', 'text');
                                                            $fldName  = data_get($objField, 'name', __('Unnamed field'));
                                                            $inputId  = 'field-' . $fldId;
                                                        } catch (\Throwable $e) {
                                                            \Log::error('form_builders/form_view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp

                                                    @if($fldType === 'text')
                                                        <div class="{{ VC::FM_G }}">
                                                            {{ Form::label($inputId, __($fldName) ?: __('Failed to get label: Field'), ['class'=>'form-label']) }}
                                                            {{ Form::text("field[$fldId]", null, ['class'=>'form-control','required'=>'required','id'=>$inputId, 'placeholder'=>__('Enter value') ?: 'Enter value']) }}
                                                        </div>
                                                    @elseif($fldType === 'email')
                                                        <div class="{{ VC::FM_G }}">
                                                            {{ Form::label($inputId, __($fldName) ?: __('Failed to get label: Field'), ['class'=>'form-label']) }}
                                                            {{ Form::email("field[$fldId]", null, ['class'=>'form-control','required'=>'required','id'=>$inputId, 'placeholder'=>__('Enter email') ?: 'Enter email']) }}
                                                        </div>
                                                    @elseif($fldType === 'number')
                                                        <div class="{{ VC::FM_G }}">
                                                            {{ Form::label($inputId, __($fldName) ?: __('Failed to get label: Field'), ['class'=>'form-label']) }}
                                                            {{ Form::number("field[$fldId]", null, ['class'=>'form-control','required'=>'required','id'=>$inputId, 'step'=>'any', 'placeholder'=>__('Enter number') ?: 'Enter number']) }}
                                                        </div>
                                                    @elseif($fldType === 'date')
                                                        <div class="{{ VC::FM_G }}">
                                                            {{ Form::label($inputId, __($fldName) ?: __('Failed to get label: Field'), ['class'=>'form-label']) }}
                                                            {{ Form::date("field[$fldId]", null, ['class'=>'form-control','required'=>'required','id'=>$inputId]) }}
                                                        </div>
                                                    @elseif($fldType === 'textarea')
                                                        <div class="{{ VC::FM_G }}">
                                                            {{ Form::label($inputId, __($fldName) ?: __('Failed to get label: Field'), ['class'=>'form-label']) }}
                                                            {{ Form::textarea("field[$fldId]", null, ['class'=>'form-control','required'=>'required','id'=>$inputId, 'rows'=>3, 'placeholder'=>__('Enter text') ?: 'Enter text']) }}
                                                        </div>
                                                    @else
                                                        <div class="{{ VC::FM_G }}">
                                                            {{ Form::label($inputId, __($fldName) ?: __('Failed to get label: Field'), ['class'=>'form-label']) }}
                                                            {{ Form::text("field[$fldId]", null, ['class'=>'form-control','required'=>'required','id'=>$inputId, 'placeholder'=>__('Enter value') ?: 'Enter value']) }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                <div class="{{ VC::ALT_INF_MB0 }}" role="alert">{{ __('No fields are available for this form.') }}</div>
                                            @endif

                                            <div class="{{ VC::MT4 }} {{ VC::TX_END }}">
                                                {{ Form::submit(__('Submit') ?: 'Submit', ['class'=>'btn btn-primary']) }}
                                            </div>
                                        </div>
                                    {{ Form::close() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('partials.admin.footer')

        @if(($get_cookie['enable_cookie'] ?? 'off') === 'on')
            @includeIf(EL::CKC)
        @endif

        <script defer src="{{ asset('assets/js/routes/fm/viewStore.js') }}"></script>
    </body>
</html>
