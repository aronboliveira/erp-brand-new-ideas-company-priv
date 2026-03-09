@php
    try {
$user = Auth::user();

        $hasFetchUserLang    = is_callable([Utility::class, 'fetchUserLang']);
        $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
        $hasDateFormat       = $user && method_exists($user, 'dateFormat');
        $hasTimeFormat       = $user && method_exists($user, 'timeFormat');

        $lang = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : app()->getLocale();

        $app      = isset($interviewSchedule->applications) ? $interviewSchedule->applications : null;
        $jobTitle = ($app && isset($app->jobs) && isset($app->jobs->title) && $app->jobs->title !== '')
            ? $app->jobs->title
            : __('Job title was not available.');
        $rawDate  = $interviewSchedule->date ?? null;
        $rawTime  = $interviewSchedule->time ?? null;
        $dateTxt  = $rawDate ? ($hasDateFormat ? $user->dateFormat($rawDate) : __('Date failed to format.')) : __('Date was not available.');
        $timeTxt  = $rawTime ? ($hasTimeFormat ? $user->timeFormat($rawTime) : __('Time failed to format.')) : __('Time was not available.');
        $empName  = isset($interviewSchedule->users) && isset($interviewSchedule->users->name) && $interviewSchedule->users->name !== ''
            ? $interviewSchedule->users->name
            : __('Assigned employee was not available.');
        $candName = ($app && isset($app->name) && $app->name !== '') ? $app->name : __('Candidate name was not available.');
        $candMail = ($app && isset($app->email) && $app->email !== '') ? $app->email : __('Candidate email was not available.');
        $candPhone= ($app && isset($app->phone) && $app->phone !== '') ? $app->phone : __('Candidate phone was not available.');

        $candId   = $interviewSchedule->candidate ?? null;
        $onbBase  = VW::JB.'.on.board.create';
        $onbUrl   = (Route::has($onbBase) && $candId) ? route($onbBase, $candId) : '#';
        $onbMsg   = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'job_on_board', 'create_route_unavailable') : null)
                    ?? __('Add to Job OnBoard route is unavailable. Please contact technical support or your domain administrator.');
    } catch (\Throwable $e) {
        \Log::error('interview_schedules/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

<div class="modal-body">
    <div class="card">
        <div class="{{ VC::CD_BD }}">
            <h5 class="{{ VC::MB4 }}">{{ __('Schedule Detail') }}</h5>
            <dl class="row {{ VC::MB0 }} {{ VC::ALC }}">
                <dt class="col-sm-5 h6 {{ VC::TXSM }}">{{ __('Job') }}</dt>
                <dd class="col-sm-7 {{ VC::TXSM }}">{{ $jobTitle }}</dd>

                <dt class="col-sm-5 h6 {{ VC::TXSM }}">{{ __('Interview On') }}</dt>
                <dd class="col-sm-7 {{ VC::TXSM }}">{{ $dateTxt }} {{ $timeTxt }}</dd>

                <dt class="col-sm-5 h6 {{ VC::TXSM }}">{{ __('Assign Employee') }}</dt>
                <dd class="col-sm-7 {{ VC::TXSM }}">{{ $empName }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="{{ VC::CD_BD }}">
            <h5 class="{{ VC::MB4 }}">{{ __('Candidate Detail') }}</h5>
            <dl class="row {{ VC::MB0 }} {{ VC::ALC }}">
                <dt class="col-sm-5 h6 {{ VC::TXSM }}">{{ __('Name') }}</dt>
                <dd class="col-sm-7 {{ VC::TXSM }}">{{ $candName }}</dd>

                <dt class="col-sm-5 h6 {{ VC::TXSM }}">{{ __('Email') }}</dt>
                <dd class="col-sm-7 {{ VC::TXSM }}">{{ $candMail }}</dd>

                <dt class="col-sm-5 h6 {{ VC::TXSM }}">{{ __('Phone') }}</dt>
                <dd class="col-sm-7 {{ VC::TXSM }}">{{ $candPhone }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="{{ VC::CD_BD }}">
            <h5 class="{{ VC::MB4 }}">{{ __('Candidate Status') }}</h5>
            @foreach(($stages ?? []) as $stage)
                @php
                    try {
                        $sid     = isset($stage->id) ? (string)$stage->id : '';
                        $stitle  = isset($stage->title) && $stage->title !== '' ? $stage->title : __('Untitled stage');
                        $checked = $app && isset($app->stage) && $sid !== '' && (string)$app->stage === $sid;
                    } catch (\Throwable $e) {
                        \Log::error('interview_schedules/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <div class="form-check-control custom-radio">
                    <input type="radio"
                           id="stage_{{ $sid }}"
                           name="stage"
                           value="{{ $sid }}"
                           data-scheduleid="{{ $candId ?? '' }}"
                           class="form-check-input stages"
                           {{ $checked ? 'checked' : '' }}>
                    <label class="form-check-label" for="stage_{{ $sid }}">{{ $stitle }}</label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="modal-footer">
        <a href="#"
           class="{{ VC::BT_PM }}"
           data-url="{{ $onbUrl }}"
           data-ajax-popup="true"
           data-bs-toggle="tooltip"
           title="{{ __('Add to Job OnBoard') }}"
           data-guard-msg="{{ base64_encode($onbMsg) }}"
           data-sv-localized="true">
            {{ __('Add to Job OnBoard') }}
        </a>
    </div>
    <script defer src="{{ asset('assets/js/routes/interviewSchedules/show.js') }}"></script>
</div>
