@php
    try {
$lang                  = Utility::fetchUserLang();
        $emailStoreBase        = VW::EML_TMP;
        $emailStoreKebab       = Str::kebab($emailStoreBase);
        $emailStoreResolved    = Route::has($emailStoreBase) ? $emailStoreBase : (Route::has($emailStoreKebab) ? $emailStoreKebab : null);
        $emailStoreUrl         = $emailStoreResolved ? route($emailStoreResolved) : '#';
        $emailStoreFormId      = 'email-template-store-form';
        $emailStoreGuardMsg    = Utility::fetchLinkMessage($lang, VW::EML_TMP, 'store_email_template_route_unavailable') ?? 'Store email template route is unavailable. Please contact technical support or your domain administrator.';
        $nameHasError          = $errors->has('name');
        $nameAttrs             = [
            'id'               => 'name',
            'class'            => trim(VC::FM_CT.' font-style'.($nameHasError ? ' is-invalid' : '')),
            'required'         => 'required',
            'autocomplete'     => 'off',
            'aria-invalid'     => $nameHasError ? 'true' : 'false',
            'aria-describedby' => $nameHasError ? 'name-error' : null,
        ];
    } catch (\Throwable $e) {
        \Log::error('email_templates/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $lang ??= 'en';
    $emailStoreUrl ??= '#';
    $emailStoreFormId ??= 'email-template-store-form';
    $emailStoreGuardMsg ??= '';
    $nameAttrs ??= ['class' => 'form-control font-style', 'required' => 'required'];
@endphp

{{ Form::open([
    'url'               => $emailStoreUrl,
    'method'            => 'POST',
    'id'                => $emailStoreFormId,
    'data-url'          => $emailStoreUrl,
    'data-guard-msg'    => $emailStoreGuardMsg,
    'data-sv-localized' => 'true',
]) }}
    @csrf
    <div class="{{ VC::RW }}">
        <div class="{{ VC::FM_GCB12 }}">
            {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
            {{ Form::text('name', null, $nameAttrs) }}
            @error('name')
                <span id="name-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
            @enderror
        </div>
        <div class="{{ VC::FM_GCB12 }} text-end">
            {{ Form::submit(__('Create'), ['class' => VC::BT_PRM]) }}
        </div>
    </div>
    <script defer src="{{ asset('assets/js/routes/emailTemplates/store.js') }}"></script>
{{ Form::close() }}
