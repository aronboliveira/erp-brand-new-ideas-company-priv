@php
    try {
$user = Auth::user();
        $hasUser = (bool) $user;
        $hasUserDateFormat = $hasUser && method_exists($user, 'dateFormat');
        $hasUserTimeFormat = $hasUser && method_exists($user, 'timeFormat');
        $hasFetchUserLang = is_callable([Utility::class, 'fetchUserLang']);
        $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
        $lang = $hasFetchUserLang ? Utility::fetchUserLang(user:$user) : app()->getLocale();
        $settings = Utility::settings();
    } catch (\Throwable $e) {
        \Log::error('interview_schedules/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Interview Schedule') }}
@endsection

@push(ST::ADM_CSS)
@endpush

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Interview Schedule') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can(PERM::CR_ITV_SCHD)
            @php
                try {
                    $createName = VW::ITV_SCD.'.create';
                    $createUrl = Route::has($createName) ? route($createName) : '#';
                    $createGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::ITV_SCD, 'create_interview_schedule_route_unavailable') : null)
                        ?? __('Create interview schedule route is unavailable. Please contact technical support or your domain administrator.');
                } catch (\Throwable $e) {
                    \Log::error('interview_schedules/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="{{ $createUrl }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-size="lg"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-title="{{ __('Create New Interview Schedule') }}"
               class="{{ VC::BT_SM_PM }}"
               data-guard-msg="{{ base64_encode($createGuardMsg) }}"
               data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    @if(!$hasUser)
        <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('Failed to load interview schedules for the current user.') }}</div>
    @else
        <div class="row">
            <div class="{{ VC::CL8 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_HD }}">
                        <div class="row">
                            <div class="{{ VC::CL6 }}">
                                <h5>{{ __('Calendar') }}</h5>
                            </div>
                            <div class="{{ VC::CL6 }}">
                                @if (!empty($settings) && !empty($settings['google_calendar_enable']) && $settings['google_calendar_enable'] === 'on')
                                    <select class="{{ VC::FM_CT }}" name="calendar_type" id="calendar_type" style="float:right;width:150px;" onchange="get_data()">
                                        <option value="google_calendar">{{ __('Google calendar') }}</option>
                                        <option value="local_calendar" selected="true">{{ __('Local calendar') }}</option>
                                    </select>
                                @endif
                                <input type="hidden" id="interview_calendar" value="{{ url('/') }}">
                            </div>
                        </div>
                    </div>
                    <div class="{{ VC::CD_BD }}">
                        <div id="calendar" class="calendar"></div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CL4 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        <h4 class="{{ VC::MB4 }}">{{ __('Schedule List') }}</h4>
                        <ul class="{{ VC::LG_FLSH_W }}">
                            <li class="{{ VC::LGI }} {{ VC::CD }} {{ VC::MB3 }}">
                                <div class="row {{ VC::ALC }} {{ VC::JCB }}">
                                    <div class="{{ VC::ALC }}">
                                        @if(!$schedules->isEmpty())
                                            @foreach ($schedules as $schedule)
                                                @php
                                                    try {
                                                        $sid = isset($schedule->id) ? (string) $schedule->id : '';
                                                        $jobTitle = (isset($schedule->applications) && isset($schedule->applications->jobs) && !empty($schedule->applications->jobs->title))
                                                            ? $schedule->applications->jobs->title
                                                            : __('Job title was not available.');
                                                        $applicant = (isset($schedule->applications) && !empty($schedule->applications->name))
                                                            ? $schedule->applications->name
                                                            : __('Applicant name was not available.');
                                                        $rawDate = $schedule->date ?? null;
                                                        $rawTime = $schedule->time ?? null;
                                                        $dateTxt = $rawDate
                                                            ? ($hasUserDateFormat ? $user->dateFormat($rawDate) : __('Failed to format date.'))
                                                            : __('Date was not available.');
                                                        $timeTxt = $rawTime
                                                            ? ($hasUserTimeFormat ? $user->timeFormat($rawTime) : __('Failed to format time.'))
                                                            : __('Time was not available.');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('interview_schedules/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::CD }} {{ VC::MB3 }} {{ VC::SNN }} {{ VC::BD }}">
                                                    <div class="{{ VC::PX3 }}">
                                                        <div class="row {{ VC::ALC }}">
                                                            <div class="col ml-n2">
                                                                <h5 class="text-sm {{ VC::MB0 }}">
                                                                    <a href="#!">{{ $jobTitle }}</a>
                                                                </h5>
                                                                <p class="card-text small {{ VC::TXT_MT }}">{{ $applicant }}</p>
                                                                <p class="card-text small {{ VC::TXT_MT }}">{{ $dateTxt }} {{ $timeTxt }}</p>
                                                            </div>
                                                            <div class="col-auto text-right {{ VC::DFL }}">
                                                                @can('edit interview schedule')
                                                                    @php
                                                                        try {
                                                                            $editName = VW::ITV_SCD.'.edit';
                                                                            $editUrl = (Route::has($editName) && $sid !== '') ? route($editName, $sid) : '#';
                                                                            $editGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::ITV_SCD, 'edit_interview_schedule_route_unavailable') : null)
                                                                                ?? __('Edit interview schedule route is unavailable. Please contact technical support or your domain administrator.');
                                                                        } catch (\Throwable $e) {
                                                                            \Log::error('interview_schedules/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                        }
@endphp
                                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                        <a href="{{ $editUrl }}"
                                                                           data-url="{{ $editUrl }}"
                                                                           data-title="{{ __('Edit Interview Schedule') }}"
                                                                           data-ajax-popup="true"
                                                                           class="{{ VC::BT_SM_CT }}"
                                                                           data-bs-toggle="tooltip"
                                                                           title="{{ __('Edit') }}"
                                                                           data-guard-msg="{{ base64_encode($editGuardMsg) }}"
                                                                           data-sv-localized="true">
                                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                                        </a>
                                                                    </div>
                                                                @endcan
                                                                @can('delete interview schedule')
                                                                    @php
                                                                        try {
                                                                            $destroyName = VW::ITV_SCD.'.destroy';
                                                                            $destroyUrl = (Route::has($destroyName) && $sid !== '') ? route($destroyName, $sid) : '#';
                                                                            $destroyGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::ITV_SCD, 'destroy_interview_schedule_route_unavailable') : null)
                                                                                ?? __('Delete interview schedule route is unavailable. Please contact technical support or your domain administrator.');
                                                                            $delFormId = 'delete-form-'.($sid === '' ? 'x' : $sid);
                                                                            $confirmTitle = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?';
                                                                            $confirmBody  = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?';
                                                                        } catch (\Throwable $e) {
                                                                            \Log::error('interview_schedules/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                        }
@endphp
                                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                        {!! Form::open([
                                                                            'method'            => 'DELETE',
                                                                            'url'               => $destroyUrl,
                                                                            'id'                => $delFormId,
                                                                            'data-url'          => $destroyUrl,
                                                                            'data-guard-msg'    => $destroyGuardMsg,
                                                                            'data-sv-localized' => 'true',
                                                                        ]) !!}
                                                                            <a href="#"
                                                                               class="{{ VC::BT_SM_CT_PR }}"
                                                                               data-bs-toggle="tooltip"
                                                                               title="{{ __('Delete') }}"
                                                                               data-confirm="{{ __($confirmTitle) }}|{{ __($confirmBody) }}"
                                                                               data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();">
                                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                            </a>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endcan
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="{{ VC::TXCT }}">{{ __('No interview schedules were available to display.') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push(ST::ADM_SCR_PG)
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/interviewSchedules/lang/index.js') }}"></script>
    <script defer>
        (() => {
        const RG = window.RouteGuard || {};
        const toast = RG.showToast || (m => alert(m));
        const csrf='{{ csrf_token() }}';
        const msgCalendarUnavailable='{{ __("Interview schedule calendar data was not available.") }}';
        const msgRouteUnavailable='{{ __("Requested route is unavailable. Please contact technical support or your domain administrator.") }}';
        const dayTxt='{{ __("Day") }}';
        const weekTxt='{{ __("Week") }}';
        const monthTxt='{{ __("Month") }}';
        const dataListenerAdded='data-listener-added';

        const bindLinkGuard=(el)=>{
            if(!el||el.getAttribute('data-listener-active')==='true')return;
            el.setAttribute('data-listener-active','true');
            el.addEventListener('click',(e)=>{
            const href=(el.getAttribute('href')??'#').trim();
            const url=(el.getAttribute('data-url')??href??'#').trim();
            if(url!=='#'&&href!=='#')return;
            e.preventDefault();
            toast(el.getAttribute('data-guard-msg')||msgRouteUnavailable);
            el.setAttribute('data-failed-route','true');
            });
        };

        const bindFormGuard=(fm)=>{
            if(!fm||fm.getAttribute('data-submit-guarded')==='true')return;
            fm.setAttribute('data-submit-guarded','true');
            fm.addEventListener('submit',(e)=>{
            const action=(fm.getAttribute('action')??'#').trim();
            const url=(fm.getAttribute('data-url')??action??'#').trim();
            if(url!=='#'&&action!=='#')return;
            e.preventDefault();
            toast(fm.getAttribute('data-guard-msg')||msgRouteUnavailable);
            fm.setAttribute('data-failed-route','true');
            });
        };

        const applyCalendarTypeClass=()=>{
            const ctSel=document.getElementById('calendar_type');
            const ct=ctSel?ctSel.value:'';
            const cal=document.getElementById('calendar');
            if(!cal)return;
            cal.classList.remove('local_calendar','google_calendar');
            if(!ct)cal.classList.add('local_calendar');
            if(ct)cal.classList.add(ct);
        };

        const renderCalendar=(events)=>{
            const el=document.getElementById('calendar');
            if(!el){toast(msgCalendarUnavailable);return;}
            if(!(window.FullCalendar&&FullCalendar.Calendar)){attachErrorClick(el);return;}
            el.innerHTML='';
            const calendar=new FullCalendar.Calendar(el,{
            headerToolbar:{left:'prev,next today',center:'title',right:'dayGridMonth,timeGridWeek,timeGridDay'},
            buttonText:{timeGridDay:dayTxt,timeGridWeek:weekTxt,dayGridMonth:monthTxt},
            slotLabelFormat:{hour:'2-digit',minute:'2-digit',hour12:false},
            themeSystem:'bootstrap',
            allDaySlot:false,
            navLinks:true,
            droppable:true,
            selectable:true,
            selectMirror:true,
            editable:false,
            dayMaxEvents:true,
            handleWindowResize:true,
            height:'auto',
            events:Array.isArray(events)?events:[]
            });
            calendar.render();
        };

        const attachErrorClick=(el)=>{
            if(!el||el.getAttribute(dataListenerAdded)==='true')return;
            el.addEventListener('click',()=>toast(msgCalendarUnavailable));
            el.setAttribute(dataListenerAdded,'true');
            const obs=new MutationObserver((_,o)=>{if(!document.body.contains(el)){el.removeEventListener('click',()=>toast(msgCalendarUnavailable));o.disconnect();}});
            obs.observe(document.body,{childList:true,subtree:true});
        };

        const fetchViaAjax=(base,ct)=>new Promise((resolve,reject)=>{
            if(typeof $==='undefined'){reject();return;}
            $.ajax({
            url:`${base}/{{ VW::ITV_SCD }}/data`,
            method:'POST',
            data:{_token:csrf,calendar_type:ct}
            }).done(resolve).fail(reject);
        });

        const fetchViaFetch=(base,ct)=>fetch(`${base}/interview-schedule/get_event_data`,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
            body:JSON.stringify({calendar_type:ct})
        }).then(r=>r.ok?r.json():Promise.reject());

        const get_data=()=>{
            try{
            applyCalendarTypeClass();
            const base=document.getElementById('interview_calendar')?.value||'';
            const ctSel=document.getElementById('calendar_type');
            const ct=ctSel?ctSel.value:'local_calendar';
            const calEl=document.getElementById('calendar');
            if(!base||!calEl)throw 0;
            const tryAjax=()=>fetchViaAjax(base,ct).then(renderCalendar).catch(()=>Promise.reject());
            const tryFetch=()=>fetchViaFetch(base,ct).then(renderCalendar).catch(()=>Promise.reject());
            const chain=typeof $!=='undefined'?tryAjax().catch(tryFetch):tryFetch().catch(tryAjax);
            chain.catch(()=>toast(msgCalendarUnavailable));
            }catch{toast(msgCalendarUnavailable);}
        };

        window.get_data=get_data;

        document.addEventListener('DOMContentLoaded',()=>{
            document.querySelectorAll("a[data-guard-msg], a[data-url]").forEach(bindLinkGuard);
            document.querySelectorAll("form[data-guard-msg], form[data-url]").forEach(bindFormGuard);
            try{document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el=>{try{bootstrap.Tooltip.getOrCreateInstance(el);}catch(_){}});}catch(_){}
            get_data();
        });
        })();
    </script>
@endpush
