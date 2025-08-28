@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route, Str};
    $lang = Utility::fetchUserLang();
    $ipCreateBaseName = ViewsConstants::SYS . '.ip.create';
    $ipCreateKebabName = Str::kebab($ipCreateBaseName);
    $ipCreateResolvedName = Route::has($ipCreateBaseName) ? $ipCreateBaseName : (Route::has($ipCreateKebabName) ? $ipCreateKebabName : null);
    $ipCreateUrl = $ipCreateResolvedName ? route($ipCreateResolvedName) : '#';
    $ipCreateGuardMsg = Utility::fetchLinkMessage($lang, 'ip', 'create_ip_route_unavailable') ?? 'Create IP route is unavailable. Please contact technical support or your domain administrator.';
    $ipFormId = 'ip-create-form';
@endphp

{!! Form::open([
    'url'  => $ipCreateUrl,
    'method' => 'post',
    'id' => $ipFormId,
    'data-action-url' => $ipCreateUrl,
    'data-form-guard-msg' => $ipCreateGuardMsg,
    'data-sv-localized' => 'true',
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('ip', __('IP'), ['class' => VC::FM_LB]) }}
                {{ Form::text('ip', null, ['class' => VC::FM_CT]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Close') }}</button>
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
{!! Form::close() !!}

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/ips/create.js') }}"></script>
@endpush
