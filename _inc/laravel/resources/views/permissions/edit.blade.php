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
    $hasModel  = !empty($permission ?? null) && data_get($permission, 'id');

    $updateBase     = VW::PMS . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasModel) ? route($updateResolved, $permission->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::PMS, 'update_route_unavailable')
                        ?? __('Update Permission route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@if($hasModel)
    {{ Form::model($permission, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'permission-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="card-body">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Permission Name')]) }}
                @error('name')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/permissions/update.js') }}"></script>
    {{ Form::close() }}

@else
    <p>{{ __('The requested permission record could not be found or is unavailable.') }}</p>
@endif
