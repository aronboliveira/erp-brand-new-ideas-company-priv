@php
    try {
$lang                    = Utility::fetchUserLang();
        $routeName               = ViewsConstants::CST_FD;
        $createUrl               = Route::has($routeName)
            ? route($routeName)
            : '#';
        $formId                  = 'custom-field-store-form';
        $guardMsg                = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CST_FD,
            'custom_field_index_route_unavailable'
        ) ?? 'Custom Field index route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('custom_fields/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'            => $createUrl,
    'id'             => $formId,
    'data-url'       => $createUrl,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Custom Field Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('type', $types, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('module', __('Module'), ['class' => VC::FM_LB]) }}
                {{ Form::select('module', $modules, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button"
               value="{{ __('Cancel') }}"
               class="{{ VC::BT_LG }}"
               data-bs-dismiss="modal">
        <input type="submit"
               value="{{ __('Create') }}"
               class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/customFields/store.js') }}"></script>
{{ Form::close() }}
