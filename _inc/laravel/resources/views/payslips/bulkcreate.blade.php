@php
    try {
$lang = Utility::fetchUserLang();
        $dateStr = (string) ($date ?? '');
        $bulkName   = VW::PY_SLP . '.bulk_payment';
        $bulkKebab  = Str::kebab($bulkName);
        $resolved   = Route::has($bulkName) ? route($bulkName, $dateStr)
                   : (Route::has($bulkKebab) ? route($bulkKebab, $dateStr)
                   : (trim($dateStr) !== '' ? url(VW::PY_SLP . '/bulk_payment/' . urlencode($dateStr)) : '#'));
        $guardMsg = Utility::fetchLinkMessage($lang, VW::PY_SLP, 'bulk_payment_route_unavailable')
                ?? 'Bulk payment route is unavailable. Please contact technical support or your domain administrator.';

        $unpaidCount = ((($unpaidEmployees ?? null) instanceof Collection) || is_array($unpaidEmployees ?? null)) ? count($unpaidEmployees) : 0;
        $totalCount  = ((($Employees ?? null) instanceof Collection) || is_array($Employees ?? null)) ? count($Employees) : 0;
    } catch (\Throwable $e) {
        \Log::error('payslips/bulkcreate — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{!! Form::open([
    'url'                  => $resolved,
    'method'               => 'post',
    'id'                   => 'bulk_payment_form',
    'data-resolved-action' => $resolved,
    'data-guard-msg'       => $guardMsg,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ __('Total Unpaid Employee') }} <b>{{ $unpaidCount }}</b> {{ __('out of') }} <b>{{ $totalCount }}</b>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Bulk Payment') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/payslips/bulkPayment.js') }}"></script>
{!! Form::close() !!}
