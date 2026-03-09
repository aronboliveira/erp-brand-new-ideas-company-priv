@php
    try {
$lang       = Utility::fetchUserLang();
        $routeName  = ViewsConstants::COA . '.store';
        $storeRoute = Route::has($routeName)
            ? route($routeName)
            : (Route::has(Str::kebab($routeName))
                ? route(Str::kebab($routeName))
                : '#');
        $formId     = 'chart_of_accounts_store_form';
        $guardMsg   = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::COA,
            'chart_of_account_store_route_unavailable'
        ) ?? 'Chart of Account store route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('chart_of_accounts/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'            => $storeRoute,
    'method'         => 'POST',
    'id'             => $formId,
    'data-url'       => $storeRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('code', __('Code'), ['class' => VC::FM_LB]) }}
                {{ Form::number('code', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('type', __('Account'), ['class' => VC::FM_LB]) }}
                {{ Form::select('type', $types, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('sub_type', __('Type'), ['class' => VC::FM_LB]) }}
                <select name="sub_type" id="sub_type" class="{{ VC::FM_CT_SL }}" required></select>
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('is_enabled', __('Is Enabled'), ['class' => VC::FM_LB]) }}
                <div class="{{ VC::FM_CHK }} form-switch">
                    <input type="checkbox" name="is_enabled" id="is_enabled" class="form-check-input" checked>
                    <label for="is_enabled" class="form-check-label"></label>
                </div>
            </div>
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'rows' => 2]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input
            type="button"
            value="{{ __('Cancel') }}"
            class="{{ VC::BT_LG }}"
            data-bs-dismiss="modal"
        >
        <input
            type="submit"
            value="{{ __('Create') }}"
            class="{{ VC::BT_PRM }}"
        >
    </div>
    <script defer src="{{ asset('assets/js/routes/chartOfAccounts/store.js') }}"></script>
{{ Form::close() }}
