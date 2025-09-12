@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants as ST};
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Route};
    use Illuminate\Support\{Collection, Str};
    use Collective\Html\FormFacade as Form;

    $lang = Utility::fetchUserLang();
    $hasJobCategory = !empty($jobCategory ?? null) && data_get($jobCategory, 'id');

    $updateBase     = VW::JB_CAT . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasJobCategory) ? route($updateResolved, $jobCategory->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::JB_CAT, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@if(!$hasJobCategory)
    <div class="alert alert-warning mb-0" role="alert">{{ __('The requested job category was not found or is unavailable.') }}</div>
@else
    {{ Form::model($jobCategory, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'jobCategory-edit-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter category title')]) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/jobs/categories/edit.js') }}"></script>
    {{ Form::close() }}
@endif
