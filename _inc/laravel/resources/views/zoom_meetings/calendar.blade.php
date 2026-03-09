@php
    try {
$settings = Utility::settings();
        $lang     = Utility::fetchUserLang();

        $dashResolved = Route::has('dashboard') ? 'dashboard' : (Route::has(Str::kebab('dashboard')) ? Str::kebab('dashboard') : null);
        $dashUrl      = $dashResolved ? route($dashResolved) : '#';

        $listBase   = 'zoom-meeting.index';
        $listKebab  = Str::kebab($listBase);
        $listName   = Route::has($listBase) ? $listBase : (Route::has($listKebab) ? $listKebab : null);
        $listUrl    = $listName ? route($listName) : '#';
        $listGuard  = Utility::fetchLinkMessage($lang, VW::ZMM, 'list_view_route_unavailable') ?? 'List view route is unavailable. Please contact technical support or your domain administrator.';
        $listId     = 'zoom-list-link';

        $createBase  = 'zoom-meeting.create';
        $createKebab = Str::kebab($createBase);
        $createName  = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
        $createUrl   = $createName ? route($createName) : '#';
        $createGuard = Utility::fetchLinkMessage($lang, VW::ZMM, 'create_meeting_route_unavailable') ?? 'Create meeting route is unavailable. Please contact technical support or your domain administrator.';
        $createId    = 'zoom-create-link';

        $currentMonth = now()->format('m');
    } catch (\Throwable $e) {
        \Log::error('zoom_meetings/calendar — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Zoom Meeting') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ $dashUrl }}" {{ $dashUrl === '#' ? 'aria-disabled=true' : '' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Zoom Meeting') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        <a id="{{ $listId }}"
           href="{{ $listUrl }}"
           data-url="{{ $listUrl }}"
           class="{{ VC::BT_SM_PM }}"
           data-bs-toggle="tooltip"
           title="{{ __('List View') }}"
           data-original-title="{{ __('List View') }}"
           data-guard-msg="{{ base64_encode($listGuard) }}"
           data-sv-localized="true">
            <i class="{{ VC::TI_LT }}"></i>
        </a>

        <a id="{{ $createId }}"
           href="{{ $createUrl }}"
           data-size="lg"
           data-url="{{ $createUrl }}"
           data-ajax-popup="true"
           data-bs-toggle="tooltip"
           title="{{ __('Create') }}"
           data-title="{{ __('Create New Meeting') }}"
           class="{{ VC::BT_SM_PM }}"
           data-guard-msg="{{ base64_encode($createGuard) }}"
           data-sv-localized="true">
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM8 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_HD }}">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CL6 }}">
                            <h5>{{ __('Calendar') }}</h5>
                        </div>
                        <div class="{{ VC::CL6 }}">
                            @php
 $gcEnabled = (is_array($settings ?? null) && ($settings['google_calendar_enable'] ?? '') === 'on');
@endphp
                            @if($gcEnabled)
                                <select class="{{ VC::FM_CT }}" name="calendar_type" id="calendar_type" style="float: right;width: 150px;" onchange="get_data()">
                                    <option value="google_calendar">{{ __('Google calendar') }}</option>
                                    <option value="local_calendar" selected="true">{{ __('Local calendar') }}</option>
                                </select>
                            @endif
                            <input type="hidden" id="zoom_calendar" value="{{ url('/') }}">
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CD_BD }}">
                    <div id="calendar" class="calendar" data-toggle="calendar"></div>
                </div>
            </div>
        </div>

        <div class="{{ VC::CL4 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD }}">
                    <h4 class="{{ VC::MB4 }}">{{ __('Mettings') }}</h4>
                    <ul class="{{ VC::LG_FLSH_W }}">
                        @forelse(((($calandar ?? null) instanceof Collection) || is_array($calandar ?? null)) ? $calandar : [] as $event)
                            @php
                                try {
                                    $evtStart = (string) (data_get($event, 'start') ?? __('Failed to retrieve start date'));
                                    $evtTitle = (string) (data_get($event, 'title') ?? __('No title available'));
                                    $evtUrl   = (string) (data_get($event, 'url') ?? '#');
                                    $evtMonth = $evtStart !== '' ? date('m', strtotime($evtStart)) : null;
                                } catch (\Throwable $e) {
                                    \Log::error('zoom_meetings/calendar — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            @if($evtMonth === $currentMonth)
                                <li class="{{ VC::LGI }} {{ VC::CD }} {{ VC::MB3 }}">
                                    <div class="{{ VC::R_ALC }} {{ VC::JCB }}">
                                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                                            <div class="{{ VC::DFL_AIC }}">
                                                <div class="theme-avatar {{ VC::BG_P }}">
                                                    <i class="ti ti-video"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <h6 class="m-0">
                                                        <a href="{{ $evtUrl }}"
                                                           class="fc-daygrid-event"
                                                           style="white-space: inherit;"
                                                           {{ $evtUrl === '#' ? 'aria-disabled=true' : '' }}>
                                                            <div class="fc-event-title-container">
                                                                <div class="fc-event-title {{ VC::TX_DK }}">{{ $evtTitle }}</div>
                                                            </div>
                                                        </a>
                                                    </h6>
                                                    <small class="{{ VC::TXT_MT }}">{{ $evtStart !== '' ? $evtStart : __('No start date available') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        @empty
                            <li class="{{ VC::LGI }} {{ VC::CD }} {{ VC::MB3 }}">
                                <div class="{{ VC::TXCT_MT }}">{{ __('No meetings available') }}</div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/zoom/create.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/zoomMeetings/lang/calendar.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/zoomMeetings/calendar.js') }}"></script>
@endpush
