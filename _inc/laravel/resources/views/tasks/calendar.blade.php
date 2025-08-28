@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    $settings = Utility::settings();
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{__('Task Calendar')}}
@endsection

@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/dist/fullcalendar.min.css') }}">
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Task Calendar')}}</li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="col-lg-8">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CL6 }}">
                            <h5>{{ __('Calendar') }}</h5>
                        </div>
                        <div class="{{ VC::CL6 }}">
                            @php
                            	$gcEnabled = (string) data_get($settings ?? [], 'google_calendar_enable', '') === 'on';
                            @endphp
                            @if($gcEnabled)
                                <select class="{{ VC::FM_CT }}" name="calendar_type" id="calendar_type" style="float: right;width: 150px;" onchange="get_data()">
                                    <option value="goggle_calendar">{{ __('Google calendar') }}</option>
                                    <option value="local_calendar" selected="true">{{ __('Local calendar') }}</option>
                                </select>
                            @endif
                            <input type="hidden" id="task_calendar" value="{{ url('/') }}">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id='calendar' class='calendar'></div>
                </div>
            </div>
        </div>
        <div class="{{ VC::CL4 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body task-calendar-scroll">
                    <h4 class="{{ VC::MB4 }}">{{ __('Tasks') }}</h4>
                    <ul class="{{ VC::LG_FLSH_W }}">
                        @forelse((($arrTasks ?? null) instanceof \Illuminate\Support\Collection || is_array($arrTasks ?? null)) ? $arrTasks : [] as $task)
                            <li class="{{ VC::LGI }} {{ VC::CD }} {{ VC::MB3 }}">
                                <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
                                    <div class="{{ VC::C_AT }} mb-3 mb-sm-0">
                                        <div class="{{ VC::DFL_AIC }}">
                                            <div class="theme-avatar {{ VC::BG_P }}">
                                                <i class="ti ti-calendar-event"></i>
                                            </div>
                                            <div class="ms-3 fc-event-title-container">
                                                <h6 class="m-0 {{ VC::TXSM }} fc-event-title text-primary">{{ data_get($task,'title') ?: __('No title available') }}</h6>
                                                <small class="{{ VC::TXT_MT }}">{{ data_get($task,'start') ?: __('No start date available') }} {{ __('to') }} {{ data_get($task,'end') ?: __('No end date available') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <p class="text-dark text-center">{{ __('No Data Found') }}</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/tasks/lang/calendar.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/tasks/calendar.js') }}"></script>
@endpush
