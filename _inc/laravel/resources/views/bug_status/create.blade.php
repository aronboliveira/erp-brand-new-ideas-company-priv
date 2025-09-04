@php
    use App\Models\Utility;
    use App\Config\Constants\{
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang                   = Utility::fetchUserLang();
    $routeName              = ViewsConstants::BUG_STT;
    $bugstatusStoreRoute    = Route::has($routeName)
        ? route($routeName)
        : (Route::has(Str::kebab($routeName))
            ? route(Str::kebab($routeName))
            : '#');
    $formId                 = 'bugstatus-store-form';
    $guardMsg               = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::BUG_STT,
        'bug_status_store_route_unavailable'
    ) ?? 'Bug Status store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'url'            => $bugstatusStoreRoute,
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $bugstatusStoreRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('title', __('Bug Status Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', '', [
                    'class'    => VC::FM_CT,
                    'required' => 'required',
                ]) }}
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
    <script defer src="{{ asset('assets/js/routes/bugStatus/store.js') }}"></script>
{{ Form::close() }}
