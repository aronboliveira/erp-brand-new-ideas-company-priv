@php
    use App\Config\Constants\{
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};

    $lang      = Utility::fetchUserLang();
    $hasModel  = !empty($unit ?? null) && data_get($unit, 'id');

    $updateBase     = VW::PRD_SV_UNT . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasModel) ? route($updateResolved, $unit->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::PRD_SV_UNT, 'update_route_unavailable')
                        ?? __('Update Product Service Unit route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@if($hasModel)
    {{ Form::model($unit, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'productServiceUnit-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true'
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('name', __('Unit Name'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                    @error('name')
                        <small class="invalid-name" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/products/services/units/update.js') }}"></script>
    {{ Form::close() }}
@else
    <div>{{ __('No product service unit could be found.') }}</div>
@endif
