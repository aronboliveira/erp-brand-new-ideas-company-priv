@php
    try {
$lang = Utility::fetchUserLang();

        $formId   = 'job-ob-store-form-' . (is_numeric($id ?? null) ? $id : 'x');
        $base     = VW::JB.'.on.board.store';
        $baseKb   = Str::kebab($base);
        $resolved = Route::has($base) ? $base : (Route::has($baseKb) ? $baseKb : null);
        $action   = ($resolved && is_numeric($id ?? null)) ? route($resolved, [$id]) : '#';
        $guardMsg = Utility::fetchLinkMessage($lang, VW::JB, 'store_board_route_unavailable') ?? __('Job onboarding creation route is unavailable. Please contact technical support or your domain administrator.');

        $appsIsList  = (is_array($applications ?? null) && count($applications ?? []) > 0) || (($applications ?? null) instanceof Collection && $applications->isNotEmpty());
        $appsOptions = $appsIsList ? (is_array($applications) ? $applications : $applications->toArray()) : ['' => __('No interviewers available')];

        $salaryTypeIsList     = (is_array($salary_type ?? null) && $salary_type) || (($salary_type ?? null) instanceof Collection && $salary_type->isNotEmpty());
        $salaryDurationIsList = (is_array($salary_duration ?? null) && $salary_duration) || (($salary_duration ?? null) instanceof Collection && $salary_duration->isNotEmpty());
        $jobTypeIsList        = (is_array($job_type ?? null) && $job_type) || (($job_type ?? null) instanceof Collection && $job_type->isNotEmpty());
        $statusIsList         = (is_array($status ?? null) && $status) || (($status ?? null) instanceof Collection && $status->isNotEmpty());
    } catch (\Throwable $e) {
        \Log::error('job_applications/onboard_create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'               => $action,
    'method'            => 'post',
    'id'                => $formId,
    'data-url'          => $action,
    'data-guard-msg'    => $guardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            @if(($id ?? null) == 0)
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('application', __('Interviewer'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('application', $appsOptions, null, array_merge(['class' => VC::FM_CT_SL.' select2','required'=>'required'], $appsIsList ? [] : ['disabled'=>'disabled'])) }}
                    @unless($appsIsList)
                        <small class="{{ VC::TXT_MT }}">{{ __('No interviewers available') }}</small>
                    @endunless
                </div>
            @endif

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('joining_date', __('Joining Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('joining_date', null, ['class' => VC::FM_CT, 'autocomplete' => 'off']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('days_of_week', __('Days Of Week'), ['class' => VC::FM_LB]) }}
                {{ Form::number('days_of_week', null, ['class' => VC::FM_CT, 'autocomplete'=>'off', 'min'=>'0']) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('salary', __('Salary'), ['class' => VC::FM_LB]) }}
                {{ Form::number('salary', null, ['class' => VC::FM_CT, 'autocomplete'=>'off', 'min'=>'0']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('salary_type', __('Salary Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('salary_type', $salary_type ?? [], null, array_merge(['class' => VC::FM_CT_SL.' select'], $salaryTypeIsList ? [] : ['disabled'=>'disabled'])) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('salary_duration', __('Salary Duration'), ['class' => VC::FM_LB]) }}
                {{ Form::select('salary_duration', $salary_duration ?? [], null, array_merge(['class' => VC::FM_CT_SL.' select'], $salaryDurationIsList ? [] : ['disabled'=>'disabled'])) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('job_type', __('Job Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('job_type', $job_type ?? [], null, array_merge(['class' => VC::FM_CT_SL.' select'], $jobTypeIsList ? [] : ['disabled'=>'disabled'])) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('status', __('Status'), ['class' => VC::FM_LB]) }}
                {{ Form::select('status', $status ?? [], null, array_merge(['class' => VC::FM_CT_SL.' select'], $statusIsList ? [] : ['disabled'=>'disabled'])) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/jobs/boards/store.js') }}"></script>
{{ Form::close() }}
