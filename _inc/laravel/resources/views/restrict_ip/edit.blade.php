@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route, Str};
    $lang = Utility::fetchUserLang();
    $ipEditBaseName     = VW::SYS . '.ip.edit';
    $ipEditKebabName    = Str::kebab($ipEditBaseName);
    $ipEditResolvedName = Route::has($ipEditBaseName)
        ? $ipEditBaseName
        : (Route::has($ipEditKebabName) ? $ipEditKebabName : null);
    $ipIdValue          = isset($ip) && !empty($ip->id) ? $ip->id : null;
    $ipEditUrl          = ($ipEditResolvedName && $ipIdValue) ? route($ipEditResolvedName, [$ipIdValue]) : '#';
    $ipEditGuardMsg     = Utility::fetchLinkMessage($lang, 'ip', 'edit_ip_route_unavailable') ?? 'Edit IP route is unavailable. Please contact technical support or your domain administrator.';
    $ipEditFormId       = 'ip-edit-form';
@endphp

{!! Form::model($ip, [
    'url'                => $ipEditUrl,
    'method'             => 'POST',
    'id'                 => $ipEditFormId,
    'data-action-url'    => $ipEditUrl,
    'data-form-guard-msg'=> $ipEditGuardMsg,
    'data-sv-localized'  => 'true',
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Collective\Html\FormFacade::label('ip', __('IP'), ['class' => VC::FM_LB]) }}
                {{ Collective\Html\FormFacade::text('ip', null, ['class' => VC::FM_CT]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Close') }}</button>
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
{!! Collective\Html\FormFacade::close() !!}

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/ips/update.js') }}"></script>
@endpush
