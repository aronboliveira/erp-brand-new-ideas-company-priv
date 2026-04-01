@php
    try {
$lang = Utility::fetchUserLang();

        $formId    = 'job-app-store-form';
        $base      = VW::JB_APP;
        $baseKebab = Str::kebab($base);
        $routeRes  = Route::has($base) ? $base : (Route::has($baseKebab) ? $baseKebab : null);
        $actionUrl = $routeRes ? route($routeRes) : '#';
        $guardMsg  = Utility::fetchLinkMessage($lang, VW::JB_APL, 'store_route_unavailable') ?? __('Job application store route is unavailable. Please contact technical support or your domain administrator.');

        $jobsIsList  = (is_array($jobs ?? null) && count($jobs ?? []) > 0) || (($jobs ?? null) instanceof Collection && $jobs->isNotEmpty());
        $jobsOptions = $jobsIsList ? (is_array($jobs) ? $jobs : $jobs->toArray()) : ['' => __('No job options available')];

        $questionsIsList = (is_iterable($questions ?? null)) && (collect($questions)->count() > 0);
    } catch (\Throwable $e) {
        \Log::error('job_applications/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'               => $actionUrl,
    'method'            => 'post',
    'enctype'           => 'multipart/form-data',
    'id'                => $formId,
    'data-url'          => $actionUrl,
    'data-guard-msg'    => $guardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('job', __('Job'), ['class' => VC::FM_LB]) }}
                {{ Form::select('job', $jobsOptions, null, array_merge(['class' => VC::FM_CT_SL.' select2','id'=>'jobs'], $jobsIsList ? [] : ['disabled'=>'disabled'])) }}
                @unless($jobsIsList)
                    <small class="{{ VC::TXT_MT }}">{{ __('No jobs available') }}</small>
                @endunless
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT.' name']) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('email', __('Email'), ['class' => VC::FM_LB]) }}
                {{ Form::text('email', null, ['class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('phone', __('Phone'), ['class' => VC::FM_LB]) }}
                {{ Form::text('phone', null, ['class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::FM_GCB6 }} dob d-none">
                {{ Form::label('dob', __('Date of Birth'), ['class' => VC::FM_LB]) }}
                {{ Form::date('dob', old('dob'), ['class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::FM_GCB6 }} gender d-none">
                {{ Form::label('gender', __('Gender'), ['class' => VC::FM_LB]) }}
                <div class="{{ VC::DFL }} radio-check">
                    <div class="{{ VC::FM_CHK_IL_GP }}">
                        <input type="radio" id="g_male" value="Male" name="gender" class="form-check-input">
                        <label class="form-check-label" for="g_male">{{ __('Male') }}</label>
                    </div>
                    <div class="{{ VC::FM_CHK_IL_GP }}">
                        <input type="radio" id="g_female" value="Female" name="gender" class="form-check-input">
                        <label class="form-check-label" for="g_female">{{ __('Female') }}</label>
                    </div>
                </div>
            </div>
            <div class="{{ VC::FM_GCB6 }} country d-none">
                {{ Form::label('country', __('Country'), ['class' => VC::FM_LB]) }}
                {{ Form::text('country', null, ['class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::FM_GCB6 }} country d-none">
                {{ Form::label('state', __('State'), ['class' => VC::FM_LB]) }}
                {{ Form::text('state', null, ['class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::FM_GCB6 }} country d-none">
                {{ Form::label('city', __('City'), ['class' => VC::FM_LB]) }}
                {{ Form::text('city', null, ['class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::FM_GCB6 }} profile d-none">
                {{ Form::label('profile', __('Profile'), ['class' => VC::FM_LB]) }}
                <div class="choose-file {{ VC::FM_G }}">
                    <input type="file" class="{{ VC::FM_CT }}" name="profile" id="profile" data-filename="profile_create">
                    <p class="profile_create {{ VC::MB0 }}"></p>
                </div>
            </div>
            <div class="{{ VC::FM_GCB6 }} resume d-none">
                {{ Form::label('resume', __('CV / Resume'), ['class' => VC::FM_LB]) }}
                <div class="choose-file {{ VC::FM_G }}">
                    <input type="file" class="{{ VC::FM_CT }}" name="resume" id="resume" data-filename="resume_create">
                    <p class="resume_create {{ VC::MB0 }}"></p>
                </div>
            </div>
            <div class="{{ VC::FM_GCB12 }} letter d-none">
                {{ Form::label('cover_letter', __('Cover Letter'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('cover_letter', null, ['class' => VC::FM_CT]) }}
            </div>

            @if($questionsIsList)
                @foreach($questions as $idx => $q)
                    @php
                        try {
                            $qid   = (string) data_get($q, 'id', '');
                            $qtext = (string) data_get($q, 'question', __('Question text unavailable'));
                            $req   = (string) data_get($q, 'is_required', 'no');
                            $forId = $qid !== '' ? 'question-'.$qid : 'question-x-'.$idx;
                            $name  = $qid !== '' ? 'question['.$qid.']' : 'question[x_'.$idx.']';
                        } catch (\Throwable $e) {
                            \Log::error('job_applications/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <div class="{{ VC::FM_GCB12 }} question question_{{ $qid !== '' ? $qid : 'x-'.$idx }} d-none">
                        {{ Form::label($forId, $qtext, ['class' => VC::FM_LB]) }}
                        <input type="text" class="{{ VC::FM_CT }}" id="{{ $forId }}" name="{{ $name }}" {{ ($req === 'yes') ? 'required' : '' }}>
                    </div>
                @endforeach
            @else
                <div class="{{ VC::FM_GCB12 }}">
                    <small class="{{ VC::TXT_MT }}">{{ __('No questions available for this job.') }}</small>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/jobs/applications/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/jobs/applications/filename.js') }}"></script>
{{ Form::close() }}
