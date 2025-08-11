@extends('layouts.admin')

@section('page-title')
    {{__('Task Calendar')}}
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/dist/fullcalendar.min.css') }}">
@endpush

@php
    $setting = \App\Models\Utility::settings();
@endphp

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Task Calendar')}}</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <h5>{{ __('Calendar') }}</h5>
                        </div>
                        <div class="col-lg-6">
                            @if (isset($setting['google_calendar_enable']) && $setting['google_calendar_enable'] == 'on')
                                <select class="form-control" name="calendar_type" id="calendar_type" style="float: right;width: 150px;" onchange="get_data()">
                                    <option value="goggle_calendar">{{__('Google calendar')}}</option>
                                    <option value="local_calendar" selected="true">{{__('Local calendar')}}</option>
                                </select>
                            @endif
                            <input type="hidden" id="task_calendar" value="{{url('/')}}">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id='calendar' class='calendar'></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body task-calendar-scroll">
                    <h4 class="mb-4">{{__('Tasks')}}</h4>
                    <ul class="event-cards list-group list-group-flush mt-3 w-100">
                        @forelse($arrTasks as $task)
                            <li class="list-group-item card mb-3">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center">
                                            <div class="theme-avtar bg-primary">
                                                <i class="ti ti-calendar-event"></i>
                                            </div>
                                            <div class="ms-3 fc-event-title-container">
                                                <h6 class="m-0 text-sm fc-event-title text-primary">{{$task['title']}}</h6>
                                                <small class="text-muted">{{$task['start']}}  to {{$task['end']}}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <p class="text-dark text-center">{{__('No Data Found')}}</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection


@push('script-page')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>

    <script type="text/javascript">

        $(document).ready(function()
        {
            get_data();
        });
        function get_data()
        {
            var calendar_type=$('#calendar_type :selected').val();
            $('#calendar').removeClass('local_calendar');
            $('#calendar').removeClass('goggle_calendar');
            if(calendar_type==undefined){
                $('#calendar').addClass('local_calendar');
            }
            $('#calendar').addClass(calendar_type);
            $.ajax({
                url: $("#task_calendar").val()+"/calendar/get_task_data" ,

                method:"POST",
                data: {"_token": "{{ csrf_token() }}",'calendar_type':calendar_type},
                success: function(data) {
                    // console.log(data);
                    (function() {
                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            buttonText: {
                                timeGridDay: "{{ __('Day') }}",
                                timeGridWeek: "{{ __('Week') }}",
                                dayGridMonth: "{{ __('Month') }}"
                            },
                            themeSystem: 'bootstrap',
                            slotDuration: '00:10:00',
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            events: data,
                        });

                        calendar.render();
                    })();
                }
            });
        }
    </script>
@endpush
