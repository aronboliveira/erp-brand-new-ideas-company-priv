@php
    use App\Config\Constants\{
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route, URL};

    $lang = Utility::fetchUserLang();
    $storeRouteName = VW::COA_TP;
    $storeGuard = Utility::fetchLinkMessage($lang, VW::COA_TP, 'chart_of_account_type_store_route_unavailable')
        ?? 'Store chart of account type route is unavailable. Please contact technical support or your domain administrator.';
    $formParams = [
        'method' => 'post',
        'id'     => 'store_chart_of_account_type',
        'data-sv-localized' => 'true',
        'data-guard-msg'    => $storeGuard,
        'data-action-href'  => Route::has($storeRouteName) ? route($storeRouteName) : '#',
    ];
    if (Route::has($storeRouteName))
        $formParams['route'] = [$storeRouteName];
    else
        $formParams['url'] = '#';
@endphp

{!! Form::open($formParams) !!}
    <div class="{{ VC::CD }} bg-none card-box">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => true]) }}
                @error('name')
                    <small class="invalid-name" role="alert"><strong class="text-danger">{{ $message }}</strong></small>
                @enderror
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>

    <script defer src="{{ asset('assets/js/routes/chartOfAccountTypes/store.js') }}"></script>
{!! Form::close() !!}
