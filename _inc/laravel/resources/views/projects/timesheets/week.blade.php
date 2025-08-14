@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    $lang                     = Utility::fetchUserLang();
    $timesheetPopupGuardMsg   = Utility::fetchLinkMessage($lang, ViewsConstants::TMS, 'timesheet_popup_route_unavailable')
        ?? 'Timesheet popup route is unavailable. Please contact technical support or your domain administrator.';
    $hoursLabel               = __('Hours');
    $minutesLabel             = __('Minutes');
    $totalLabel               = __('Total');
    $timeLoggedLabel          = __('Time Logged');
    $dateCols                 = is_countable($totalDateTimes ?? null) ? count($totalDateTimes) : 0;
    $projectHeaderColspan     = $dateCols + 2;
@endphp
<table class="{{ VC::TB }} {{ VC::MB0 }}">
    <thead>
        <tr>
            <th class="{{ VC::TXT_MT }}">{{ __('Title') }}</th>
            @foreach (($days['datePeriod'] ?? []) as $perioddate)
                @php
                    $dayAbbr = method_exists($perioddate, 'format') ? $perioddate->format('D')   : '';
                    $dayDate = method_exists($perioddate, 'format') ? $perioddate->format('d M') : '';
                @endphp
                <th scope="col" class="heading">
                    <span>{{ $dayAbbr }}</span>
                    <span>{{ $dayDate }}</span>
                </th>
            @endforeach
            <th class="text-center">{{ $totalLabel }}</th>
        </tr>
    </thead>

    <tbody class="tbody">
        @php($hasAllProjects = !empty($allProjects))
        @if($hasAllProjects)
            @foreach (($timesheetArray ?? []) as $pIdx => $timesheet)
                <tr>
                    <td class="project-name" colspan="{{ $projectHeaderColspan }}" data-bs-toggle="tooltip" title="{{ __('Project') }}">
                        {{ $timesheet['project_name'] ?? __('(Unnamed project)') }}
                    </td>
                </tr>
                @foreach (($timesheet['taskArray'] ?? []) as $tIdx => $taskTimesheet)
                    @foreach (($taskTimesheet['dateArray'] ?? []) as $dateTimeArray)
                        @php
                            $taskName = $taskTimesheet['task_name'] ?? __('(Unnamed task)');
                            $userId   = $dateTimeArray['user_id']   ?? null;
                            $taskId   = $taskTimesheet['task_id']   ?? null;
                            $projId   = $timesheet['project_id']    ?? null;
                            $rowTotal = $dateTimeArray['totaltime'] ?? '00:00';
                            $rowTotal = ($rowTotal && $rowTotal !== '00:00') ? $rowTotal : '00:00';
                        @endphp
                        <tr class="timesheet-user">
                            <td class="task-name" data-bs-toggle="tooltip" title="{{ __('Task') }}">
                                {{ $taskName }}
                            </td>
                            @foreach (($dateTimeArray['week'] ?? []) as $dateSubArray)
                                @php
                                    $cellTime  = $dateSubArray['time'] ?? '00:00';
                                    $cellTime  = ($cellTime && $cellTime !== '00:00') ? $cellTime : '00:00';
                                    $cellType  = $dateSubArray['type'] ?? '';
                                    $cellDate  = $dateSubArray['date'] ?? '';
                                    $cellUrl   = !empty($dateSubArray['url']) ? $dateSubArray['url'] : '#';
                                    $hasValue  = $cellTime !== '00:00';
                                    $borderCls = $hasValue ? 'border-dark' : 'border-white';
                                @endphp
                                <td>
                                    <input
                                        class="{{ VC::FM_CT }} {{ $borderCls }} wid-120 task-time day-time"
                                        data-type="{{ $cellType }}"
                                        data-user-id="{{ $userId }}"
                                        data-project-id="{{ $projId }}"
                                        data-task-id="{{ $taskId }}"
                                        data-date="{{ $cellDate }}"
                                        data-ajax-timesheet-popup="true"
                                        data-url="{{ $cellUrl }}"
                                        data-guard-msg="{{ $timesheetPopupGuardMsg }}"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="^\d{2}:\d{2}$"
                                        aria-label="{{ __('Time (HH:MM)') }}"
                                        value="{{ $cellTime }}"
                                    >
                                </td>
                            @endforeach
                            @php($rowBorder = $rowTotal !== '00:00' ? 'border-dark' : 'border-white')
                            <td class="text-center total-task-time day-time">
                                <input
                                    class="{{ VC::FM_CT }} {{ $rowBorder }} wid-120 total-task-time day-time"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="^\d{2}:\d{2}$"
                                    aria-label="{{ __('Row total time (HH:MM)') }}"
                                    value="{{ $rowTotal }}"
                                >
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        @else
            @foreach (($timesheetArray ?? []) as $k => $timesheet)
                @php
                    $taskName = $timesheet['task_name'] ?? __('(Unnamed task)');
                    $taskId   = $timesheet['task_id']   ?? null;
                    $rowTotal = $timesheet['totaltime'] ?? '00:00';
                    $rowTotal = ($rowTotal && $rowTotal !== '00:00') ? $rowTotal : '00:00';
                @endphp
                <tr>
                    <td class="task-name">{{ $taskName }}</td>
                    @foreach (($timesheet['dateArray'] ?? []) as $day => $datetime)
                        @php
                            $cellTime  = $datetime['time'] ?? '00:00';
                            $cellTime  = ($cellTime && $cellTime !== '00:00') ? $cellTime : '00:00';
                            $cellType  = $datetime['type'] ?? '';
                            $cellDate  = $datetime['date'] ?? '';
                            $cellUrl   = !empty($datetime['url']) ? $datetime['url'] : '#';
                            $hasValue  = $cellTime !== '00:00';
                            $borderCls = $hasValue ? 'border-dark' : 'border-white';
                        @endphp
                        <td>
                            <input
                                class="{{ VC::FM_CT }} {{ $borderCls }} wid-120 task-time day-time1"
                                data-type="{{ $cellType }}"
                                data-task-id="{{ $taskId }}"
                                data-date="{{ $cellDate }}"
                                data-ajax-timesheet-popup="true"
                                data-url="{{ $cellUrl }}"
                                data-guard-msg="{{ $timesheetPopupGuardMsg }}"
                                type="text"
                                inputmode="numeric"
                                pattern="^\d{2}:\d{2}$"
                                aria-label="{{ __('Time (HH:MM)') }}"
                                value="{{ $cellTime }}"
                            >
                        </td>
                    @endforeach
                    @php($rowBorder = $rowTotal !== '00:00' ? 'border-dark' : 'border-white')
                    <td class="text-center total-task-time day-time1">
                        <input
                            class="{{ VC::FM_CT }} {{ $rowBorder }} wid-120 task-time day-time1"
                            type="text"
                            inputmode="numeric"
                            pattern="^\d{2}:\d{2}$"
                            aria-label="{{ __('Row total time (HH:MM)') }}"
                            value="{{ $rowTotal }}"
                        >
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>

    <tfoot>
        @php
            $grandTotal = $calculatedtotaltaskdatetime ?? '00:00';
            $grandTotal = ($grandTotal && $grandTotal !== '00:00') ? $grandTotal : '00:00';
        @endphp
        <tr class="{{ VC::BG_P }}">
            <td>{{ $totalLabel }}</td>
            @foreach (($totalDateTimes ?? []) as $idx => $totaldatetime)
                @php
                    $colTotal  = $totaldatetime ?? '00:00';
                    $colTotal  = ($colTotal && $colTotal !== '00:00') ? $colTotal : '00:00';
                    $borderCls = $colTotal !== '00:00' ? 'border-dark' : 'border-white';
                @endphp
                <td class="total-date-time">
                    <input
                        class="{{ VC::FM_CT }} {{ VC::BG_TPR }} {{ $borderCls }} wid-120"
                        type="text"
                        inputmode="numeric"
                        pattern="^\d{2}:\d{2}$"
                        aria-label="{{ __('Column total time (HH:MM)') }}"
                        value="{{ $colTotal }}"
                    >
                </td>
            @endforeach
            @php($grandBorder = $grandTotal !== '00:00' ? 'border-dark' : 'border-white')
            <td class="text-center total-value1">
                <input
                    class="{{ VC::FM_CT }} {{ VC::BG_TPR }} {{ $grandBorder }} wid-120"
                    type="text"
                    inputmode="numeric"
                    pattern="^\d{2}:\d{2}$"
                    aria-label="{{ __('Grand total time (HH:MM)') }}"
                    value="{{ $grandTotal }}"
                >
            </td>
        </tr>
    </tfoot>
</table>
<div class="text-center {{ VC::DFL }} {{ VC::ALC }} justify-content-center {{ VC::MT4 }} mb-5 timelogged">
    <h5 class="f-w-900 me-2 {{ VC::MB0 }}">{{ $timeLoggedLabel }} :</h5>
    <span class="p-2 f-w-900 rounded {{ VC::BG_P }} d-inline-block {{ VC::BD }} border-dark">
        {{ ($calculatedtotaltaskdatetime ?? '00:00') }} {{ $hoursLabel }}
    </span>
</div>
<script defer>
    (() => {
        try {
            const selector = 'input[data-ajax-timesheet-popup="true"]';
            document.querySelectorAll(selector).forEach((el) => {
                if (el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                const guard = (evt) => {
                    try {
                        const url = (el.getAttribute('data-url') || '').trim();
                        if (url && url !== '#') return;
                        if (evt) {
                            if (typeof evt.preventDefault === 'function') evt.preventDefault();
                            if (typeof evt.stopPropagation === 'function') evt.stopPropagation();
                        }
                        const msg = el.getAttribute('data-guard-msg')
                            || 'Timesheet popup route is unavailable. Please contact technical support or your domain administrator.';
                        const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (hasBootstrap) {
                            const toast = document.createElement('div');
                            toast.className = 'toast';
                            toast.setAttribute('role', 'alert');
                            toast.setAttribute('aria-live', 'assertive');
                            toast.setAttribute('aria-atomic', 'true');

                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;

                            toast.appendChild(body);
                            container.appendChild(toast);

                            bootstrap.Toast.getOrCreateInstance(toast).show();
                        } else {
                            alert(msg);
                        }

                        el.setAttribute('data-failed-route', 'true');
                    } catch (_) {}
                };
                el.addEventListener('click', guard, { passive: false });
                el.addEventListener('keydown', (e) => {
                    if (e && (e.key === 'Enter' || e.keyCode === 13)) guard(e);
                }, { passive: false });
            });
        } catch (_) {}
    })();
</script>
