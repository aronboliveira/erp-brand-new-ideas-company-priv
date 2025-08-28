@php
    use App\Config\Constants\{ViewsConstants, StacksConstants, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route, Str};

    $lang = Utility::fetchUserLang();
    $terminationTypeId   = $terminationtype->id ?? null;
    $updateBaseName      = ViewsConstants::TMN_TP.'.update';
    $updateKebabName     = Str::kebab($updateBaseName);
    $updateResolvedName  = Route::has($updateBaseName)
        ? $updateBaseName
        : (Route::has($updateKebabName) ? $updateKebabName : null);
    $updateUrl           = ($updateResolvedName && $terminationTypeId)
        ? route($updateResolvedName, [$terminationTypeId])
        : '#';
    $formId              = 'termination-type-update-form';
    $guardMsg            = Utility::fetchLinkMessage($lang, ViewsConstants::TMN_TP, 'update_termination_type_route_unavailable')
        ?? 'Update termination type route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::model($terminationtype, [
    'url'    => $updateUrl,
    'method' => 'PUT',
    'id'     => $formId,
    'data-url' => $updateUrl,
    'data-guard-msg' => $guardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Termination Type Name')]) }}
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
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
    </div>
    <script src="{{ asset('assets/js/routes/terminations/types/update.js') }}" defer></script>
{{ Form::close() }}

