@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};
    use Collective\Html\FormFacade as Form;

    $lang          = Utility::fetchUserLang();
    $hasGoalType   = !empty($goalType ?? null) && data_get($goalType, 'id');

    $updateBase     = VW::GL_TP . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasGoalType) ? route($updateResolved, $goalType->id) : '#';
    $updateGuardMsg = Utility::fetchLinkMessage($lang, VW::GL_TP, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@if(!$hasGoalType)
    <div class="alert alert-warning mb-0" role="alert">{{ __('The requested goal type was not found or is unavailable.') }}</div>
@else
    {{ Form::model($goalType, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'goal-type-edit-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuardMsg,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                        {{ Form::text('name', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Goal Type Name')]) }}
                        @error('name')
                            <span class="invalid-name" role="alert">
                                <strong class="text-danger">{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>

        <script defer src="{{ asset('assets/js/routes/goals/types/edit.js') }}"></script>
    {{ Form::close() }}
@endif
