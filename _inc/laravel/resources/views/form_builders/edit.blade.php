@php
    use App\Config\Constants\{ViewClassNamesConstants as VC, ViewsConstants as VW, StacksConstants as ST};
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Collective\Html\FormFacade as Form;

    $lang       = Utility::fetchUserLang();
    $hasEntity  = !empty($formBuilder ?? null) && data_get($formBuilder, 'id');
    $formId     = 'fm-bd-update-form';

    if ($hasEntity) {
        $updBase   = VW::FM_BD . '.update';
        $updKebab  = Str::kebab($updBase);
        $updRes    = Route::has($updBase) ? $updBase : (Route::has($updKebab) ? $updKebab : null);
        $updUrl    = ($updRes && ($formBuilder->id ?? null)) ? route($updRes, $formBuilder->id) : '#';
        $updGuard  = Utility::fetchLinkMessage($lang, VW::FM_BD, 'update_route_unavailable') ?? __('Form update route is unavailable. Please contact technical support or your domain administrator.');
        $activeVal = (int)($formBuilder->is_active ?? 1);
    }
@endphp

@if(!$hasEntity)
    <div class="alert alert-danger mb-0" role="alert">{{ __('The requested form builder was not found or is unavailable.') }}</div>
@else
    {{ Form::model($formBuilder, [
        'url'               => $updUrl,
        'method'            => 'PUT',
        'id'                => $formId,
        'data-url'          => $updUrl,
        'data-guard-msg'    => $updGuard,
        'data-sv-localized' => 'true',
    ]) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-12 form-group">
                {{ Form::label('name', __('Name') ?: __('Failed to get label: Name'), ['class'=>'form-label']) }}
                {{ Form::text('name', null, ['class'=>'form-control','required'=>'required','placeholder'=>__('Enter name') ?: __('Failed to get placeholder: name')]) }}
            </div>
            <div class="col-12 form-group">
                <label class="form-label">{{ __('Active') ?: __('Failed to get label: Active') }}</label>
                <div class="d-flex radio-check">
                    <div class="{{ VC::FM_CHK_IL }}">
                        <input type="radio" id="on" value="1" name="is_active" class="form-check-input" {{ $activeVal === 1 ? 'checked' : '' }}>
                        <label class="custom-control-label form-label" for="on">{{ __('On') ?: __('Failed to get label: On') }}</label>
                    </div>
                    <div class="{{ VC::FM_CHK_IL }}">
                        <input type="radio" id="off" value="0" name="is_active" class="form-check-input" {{ $activeVal === 0 ? 'checked' : '' }}>
                        <label class="custom-control-label form-label" for="off">{{ __('Off') ?: __('Failed to get label: Off') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') ?: __('Failed to get label: Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') ?: __('Failed to get label: Update') }}" class="btn btn-primary">
    </div>
    <script defer src="{{ asset('assets/js/routes/formBuilders/update.js') }}"></script>
    {{ Form::close() }}

@endif
