@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants as ST};
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Route};
    use Illuminate\Support\{Collection, Str};
    use Collective\Html\FormFacade as Form;

    $lang = Utility::fetchUserLang();
    $hasJobStage = !empty($jobStage ?? null) && data_get($jobStage, 'id');

    $updateBase     = VW::JB_STG . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasJobStage) ? route($updateResolved, $jobStage->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::JB_STG, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@if(!$hasJobStage)
    <div class="alert alert-warning mb-0" role="alert">{{ __('The requested job stage was not found or is unavailable.') }}</div>
@else
    {{ Form::model($jobStage, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'jobStage-edit-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter stage title')]) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/jobs/stages/edit.js') }}"></script>
    {{ Form::close() }}
@endif
