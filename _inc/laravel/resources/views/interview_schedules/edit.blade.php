@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants as ST};
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Route};
    use Illuminate\Support\{Collection, Str};
    use Collective\Html\FormFacade as Form;

    $lang = Utility::fetchUserLang();
    $hasInterviewSchedule = !empty($interviewSchedule ?? null) && data_get($interviewSchedule, 'id');

    $updateBase     = VW::ITV_SCHD . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasInterviewSchedule) ? route($updateResolved, $interviewSchedule->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::ITV_SCHD, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@if(!$hasInterviewSchedule)
    <div class="alert alert-warning mb-0" role="alert">{{ __('The requested interview schedule was not found or is unavailable.') }}</div>
@else
    {{ Form::model($interviewSchedule, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'interviewSchedule-edit-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('candidate', __('Interview To'), ['class'=>'form-label']) }}
                    {{ Form::select('candidate', $candidates ?? [], null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('employee', __('Interviewer'), ['class'=>'form-label']) }}
                    {{ Form::select('employee', $employees ?? [], null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('date', __('Interview Date'), ['class'=>'form-label']) }}
                    {{ Form::date('date', null, ['class'=> VC::FM_CT]) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('time', __('Interview Time'), ['class'=>'form-label']) }}
                    {{ Form::time('time', null, ['class'=> VC::FM_CT . ' timepicker']) }}
                </div>
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('comment', __('Comment'), ['class'=>'form-label']) }}
                    {{ Form::textarea('comment', null, ['class'=> VC::FM_CT]) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/interviewSchedules/edit.js') }}"></script>
    {{ Form::close() }}
@endif
