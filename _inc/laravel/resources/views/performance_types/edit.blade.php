@php
    use App\Config\Constants\{
        ViewClassNamesConstants as VC,
        ViewsConstants as VW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang      = Utility::fetchUserLang();
    $hasModel  = !empty($performanceType ?? null) && data_get($performanceType, 'id');

    $updateBase     = VW::PFM_TP . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasModel) ? route($updateResolved, $performanceType->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::PFM_TP, 'update_route_unavailable')
                        ?? __('Update Performance Type route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@if($hasModel)
    {{ Form::model($performanceType, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'performanceType-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
    {{ Form::close() }}

    <script defer src="{{ asset('assets/js/routes/performanceTypes/update.js') }}"></script>
@else
    <p>{{ __('The requested performance type record could not be found or is unavailable.') }}</p>
@endif
