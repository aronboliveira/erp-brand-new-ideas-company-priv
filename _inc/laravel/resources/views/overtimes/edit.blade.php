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
    $hasModel  = !empty($overtime ?? null) && data_get($overtime, 'id');

    $updateBase     = VW::OVT . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasModel) ? route($updateResolved, $overtime->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::OVT, 'update_route_unavailable')
                        ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@if($hasModel)
    {{ Form::model($overtime, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'overtime-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="card-body p-0">
                <div class="row">
                    <div class="{{ VC::FM_GCB6 }}">
                        <div class="form-group">
                            {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
                            {{ Form::text('title', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                        </div>
                    </div>
                    <div class="{{ VC::FM_GCB6 }}">
                        <div class="form-group">
                            {{ Form::label('number_of_days', __('Number Of Days'), ['class' => 'form-label']) }}
                            {{ Form::text('number_of_days', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                        </div>
                    </div>
                    <div class="{{ VC::FM_GCB6 }}">
                        <div class="form-group">
                            {{ Form::label('hours', __('Hours'), ['class' => 'form-label']) }}
                            {{ Form::text('hours', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                        </div>
                    </div>
                    <div class="{{ VC::FM_GCB6 }}">
                        <div class="form-group">
                            {{ Form::label('rate', __('Rate'), ['class' => 'form-label']) }}
                            {{ Form::number('rate', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/overtimes/update.js') }}"></script>
    {{ Form::close() }}
@else
    <p>{{ __('The requested overtime record could not be found or is unavailable.') }}</p>
@endif
