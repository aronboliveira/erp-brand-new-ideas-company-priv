@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Illuminate\Support\Collection;

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
            <div class="col-md-12">
                <div class="form-group">
                    {{ __('Total Unpaid Employee') }} <b>{{ $unpaidCount }}</b> {{ __('out of') }} <b>{{ $totalCount }}</b>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Bulk Payment') }}" class="btn btn-primary">
    </div>
    <script defer src="{{ asset('assets/js/routes/payslips/bulkPayment.js') }}"></script>
{!! Form::close() !!}

