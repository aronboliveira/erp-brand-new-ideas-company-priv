@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};

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
@endphp

<div class="modal-body">
    <div class="card">
        <div class="card-body">
            <h5 class="mb-4">{{ __('Schedule Detail') }}</h5>
            <dl class="row mb-0 align-items-center">
                <dt class="col-sm-5 h6 text-sm">{{ __('Job') }}</dt>
                <dd class="col-sm-7 text-sm">{{ $jobTitle }}</dd>

                <dt class="col-sm-5 h6 text-sm">{{ __('Interview On') }}</dt>
                <dd class="col-sm-7 text-sm">{{ $dateTxt }} {{ $timeTxt }}</dd>

                <dt class="col-sm-5 h6 text-sm">{{ __('Assign Employee') }}</dt>
                <dd class="col-sm-7 text-sm">{{ $empName }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="mb-4">{{ __('Candidate Detail') }}</h5>
            <dl class="row mb-0 align-items-center">
                <dt class="col-sm-5 h6 text-sm">{{ __('Name') }}</dt>
                <dd class="col-sm-7 text-sm">{{ $candName }}</dd>

                <dt class="col-sm-5 h6 text-sm">{{ __('Email') }}</dt>
                <dd class="col-sm-7 text-sm">{{ $candMail }}</dd>

                <dt class="col-sm-5 h6 text-sm">{{ __('Phone') }}</dt>
                <dd class="col-sm-7 text-sm">{{ $candPhone }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="mb-4">{{ __('Candidate Status') }}</h5>
            @foreach(($stages ?? []) as $stage)
                @php
                    $sid     = isset($stage->id) ? (string)$stage->id : '';
                    $stitle  = isset($stage->title) && $stage->title !== '' ? $stage->title : __('Untitled stage');
                    $checked = $app && isset($app->stage) && $sid !== '' && (string)$app->stage === $sid;
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
           data-guard-msg="{{ $onbMsg }}"
           data-sv-localized="true">
            {{ __('Add to Job OnBoard') }}
        </a>
    </div>
    <script defer src="{{ asset('assets/js/routes/interviewSchedules/show.js') }}"></script>
</div>

