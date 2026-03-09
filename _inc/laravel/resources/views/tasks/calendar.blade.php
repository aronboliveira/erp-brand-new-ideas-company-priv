@include('partials.helpers.route_helpers')
@php
    try {
$settings = Utility::settings();
        $lang = Utility::fetchUserLang();
        $tasks = ensureIterable($arrTasks ?? []);
        $gcEnabled = (string)data_get($settings ?? [], 'google_calendar_enable', '') === 'on';
        $dashRoute = resolveRouteWithGuard('dashboard', $lang, 'dashboard', 'dashboard_route_unavailable', [], 'Dashboard unavailable.');
    } catch (\Throwable $e) {
        \Log::error('tasks/calendar — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Task Calendar') }}
@endsection

@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/dist/fullcalendar.min.css') }}">
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ $dashRoute['url'] }}" data-guard-msg="{{ base64_encode($dashRoute['guardMsg']) }}" data-sv-localized="true" {{ $dashRoute['resolved'] ? '' : 'aria-disabled="true"' }}>{{ __('Dashboard') }}</a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Task Calendar') }}</li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CL8 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_HD }}">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CL6 }}">
                            <h5>{{ __('Calendar') }}</h5>
                        </div>
                        <div class="{{ VC::CL6 }}">
                            @if($gcEnabled)
                                <select class="{{ VC::FM_CT }}" name="calendar_type" id="calendar_type" style="float: right;width: 150px;" onchange="get_data()">
                                    <option value="google_calendar">{{ __('Google calendar') }}</option>
                                    <option value="local_calendar" selected="true">{{ __('Local calendar') }}</option>
                                </select>
                            @endif
                            <input type="hidden" id="task_calendar" value="{{ url('/') }}">
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CD_BD }}">
                    <div id='calendar' class='calendar'></div>
                </div>
            </div>
        </div>
        <div class="{{ VC::CL4 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD }} task-calendar-scroll">
                    <h4 class="{{ VC::MB4 }}">{{ __('Tasks') }}</h4>
                    <ul class="{{ VC::LG_FLSH_W }}">
                        @forelse($tasks as $task)
                            @php
                                try {
                                    $taskTitle = safeDataGet($task, 'title') ?: __('No title available');
                                    $taskStart = safeDataGet($task, 'start') ?: __('No start date available');
                                    $taskEnd = safeDataGet($task, 'end') ?: __('No end date available');
                                } catch (\Throwable $e) {
                                    \Log::error('tasks/calendar — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            <li class="{{ VC::LGI }} {{ VC::CD }} {{ VC::MB3 }}">
                                <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
                                    <div class="{{ VC::C_AT }} mb-3 mb-sm-0">
                                        <div class="{{ VC::DFL_AIC }}">
                                            <div class="theme-avatar {{ VC::BG_P }}">
                                                <i class="ti ti-calendar-event"></i>
                                            </div>
                                            <div class="ms-3 fc-event-title-container">
                                                <h6 class="m-0 {{ VC::TXSM }} fc-event-title text-primary">{{ $taskTitle }}</h6>
                                                <small class="{{ VC::TXT_MT }}">{{ $taskStart }} {{ __('to') }} {{ $taskEnd }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <p class="{{ VC::TX_DK }} {{ VC::TXCT }}">{{ __('No Data Found') }}</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/tasks/lang/calendar.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/tasks/calendar.js') }}"></script>
@endpush
