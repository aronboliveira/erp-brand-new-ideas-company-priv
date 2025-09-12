@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Illuminate\Support\Collection;

    $lang = Utility::fetchUserLang();

    $viewKey    = 'interview-schedule';
    $formId     = 'iv-sch-store-form';
    $storeBase  = $viewKey . '.store';
    $storeKebab = Str::kebab($storeBase);
    $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeRes ? route($storeRes) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, $viewKey, 'store_route_unavailable') ?? __('Interview schedule store route is unavailable. Please contact technical support or your domain administrator.');

    $candidatesIsList = (is_array($candidates ?? null) && count($candidates ?? []) > 0) || (($candidates ?? null) instanceof Collection && $candidates->isNotEmpty());
    $candidateOptions = $candidatesIsList ? (is_array($candidates) ? $candidates : $candidates->toArray()) : ['' => __('No candidates available')];
    $candidateErr     = $errors->has('candidate');
    $candidateAttrs   = [
        'id'               => 'candidate',
        'class'            => trim(VC::FM_CT_SL . ' ' . ($candidateErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $candidateErr ? 'true' : 'false',
        'aria-describedby' => $candidateErr ? 'candidate-error' : null,
    ];
    if (!$candidatesIsList) { $candidateAttrs['disabled'] = 'disabled'; }

    $employeesIsList = (is_array($employees ?? null) && count($employees ?? []) > 0) || (($employees ?? null) instanceof Collection && $employees->isNotEmpty());
    $employeeOptions = $employeesIsList ? (is_array($employees) ? $employees : $employees->toArray()) : ['' => __('No interviewers available')];
    $employeeErr     = $errors->has('employee');
    $employeeAttrs   = [
        'id'               => 'employee',
        'class'            => trim(VC::FM_CT_SL . ' ' . ($employeeErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $employeeErr ? 'true' : 'false',
        'aria-describedby' => $employeeErr ? 'employee-error' : null,
    ];
    if (!$employeesIsList) { $employeeAttrs['disabled'] = 'disabled'; }

    $dateErr = $errors->has('date');
    $timeErr = $errors->has('time');
    $cmtErr  = $errors->has('comment');

    $gcalEnabled = (bool) (is_array($settings ?? null) && data_get($settings, 'google_calendar_enable') === 'on');
@endphp

{{ Form::open([
    'url'               => $storeUrl,
    'method'            => 'POST',
    'id'                => $formId,
    'data-url'          => $storeUrl,
    'data-guard-msg'    => $storeGuard,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('candidate', __('Interview To'), ['class' => VC::FM_LB]) }}
                {{ Form::select('candidate', $candidateOptions, null, $candidateAttrs) }}
                @error('candidate')
                    <span id="candidate-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('employee', __('Interviewer'), ['class' => VC::FM_LB]) }}
                {{ Form::select('employee', $employeeOptions, null, $employeeAttrs) }}
                @error('employee')
                    <span id="employee-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('date', __('Interview Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('date', null, [
                    'id'               => 'date',
                    'class'            => trim(VC::FM_CT . ' ' . ($dateErr ? 'is-invalid' : '')),
                    'aria-invalid'     => $dateErr ? 'true' : 'false',
                    'aria-describedby' => $dateErr ? 'date-error' : null,
                ]) }}
                @error('date')
                    <span id="date-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('time', __('Interview Time'), ['class' => VC::FM_LB]) }}
                {{ Form::time('time', null, [
                    'id'               => 'time',
                    'class'            => trim(VC::FM_CT . ' timepicker ' . ($timeErr ? 'is-invalid' : '')),
                    'aria-invalid'     => $timeErr ? 'true' : 'false',
                    'aria-describedby' => $timeErr ? 'time-error' : null,
                ]) }}
                @error('time')
                    <span id="time-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('comment', __('Comment'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('comment', null, [
                    'id'               => 'comment',
                    'class'            => trim(VC::FM_CT . ' ' . ($cmtErr ? 'is-invalid' : '')),
                    'aria-invalid'     => $cmtErr ? 'true' : 'false',
                    'aria-describedby' => $cmtErr ? 'comment-error' : null,
                ]) }}
                @error('comment')
                    <span id="comment-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            @if($gcalEnabled)
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('synchronize_type', __('Synchronize in Google Calendar ?'), ['class' => VC::FM_LB]) }}
                    <div class="form-switch">
                        <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calendar">
                        <label class="form-check-label" for="switch-shadow"></label>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    @if(($candidate ?? 0) != 0)
        <script async src="{{ asset('assets/js/routes/interviewSchedules/lang/store.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/interviewSchedules/storeSelect.js') }}"></script>
    @endif
    <script defer src="{{ asset('assets/js/routes/interviewSchedules/store.js') }}"></script>
{{ Form::close() }}
