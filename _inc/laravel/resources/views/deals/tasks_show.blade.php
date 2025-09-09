<div class="modal-body">
    @php
        $statusMap    = \App\Models\DealTask::$status ?? [];
        $priorityMap  = \App\Models\DealTask::$priorities ?? [];

        $statusKey    = (isset($task->status) && is_numeric($task->status)) ? (int)$task->status : null;
        $priorityKey  = (isset($task->priority) && is_numeric($task->priority)) ? (int)$task->priority : null;

        $statusLabel  = ($statusKey !== null && isset($statusMap[$statusKey])) ? __($statusMap[$statusKey]) : __('Unknown');
        $priorityLbl  = ($priorityKey !== null && isset($priorityMap[$priorityKey])) ? __($priorityMap[$priorityKey]) : __('Unknown');

        $dealName     = !empty($deal->name) ? $deal->name : '-';

        $authUser     = Auth::user();
        $canDateFmt   = $authUser && method_exists($authUser, 'dateFormat');
        $canTimeFmt   = $authUser && method_exists($authUser, 'timeFormat');

        $rawDate      = $task->date ?? null;
        $rawTime      = $task->time ?? null;

        $dateOut      = $rawDate ? ($canDateFmt ? $authUser->dateFormat($rawDate) : $rawDate) : '-';
        $timeOut      = $rawTime ? ($canTimeFmt ? $authUser->timeFormat($rawTime) : $rawTime) : '-';

        $assignees    = (isset($deal->users) && is_iterable($deal->users)) ? $deal->users : [];
    @endphp

    <div class="row text-sm">
        <div class="col-md-4">
            <div class="mb-4">
                <b>{{ __('Status') }}</b>
                <div class="badge badge-pill {{ ($statusKey ?? 0) ? 'badge-success' : 'badge-warning' }} mb-1">
                    {{ $statusLabel }}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="mb-4">
                <b>{{ __('Priority') }}</b>
                <p>{{ $priorityLbl }}</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="mb-4">
                <b>{{ __('Deal Name') }}</b>
                <p>{{ $dealName }}</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="mb-4">
                <b>{{ __('Date') }}</b>
                <p>{{ $dateOut }}</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="mb-4">
                <b>{{ __('Time') }}</b>
                <p>{{ $timeOut }}</p>
            </div>
        </div>

        <div class="col-md-12">
            <div class="mb-0">
                <b>{{ __('Assigned') }}</b>
                <p class="mt-2">
                    @foreach($assignees as $u)
                        @php
                            $avatar = !empty($u->avatar)
                                ? asset('/storage/uploads/avatar/'.$u->avatar)
                                : asset('/storage/uploads/avatar/avatar.png');
                            $name = $u->name ?? '';
                        @endphp
                        <a href="#" class="btn btn-sm mr-1 p-0 rounded-circle">
                            <img
                                alt="image"
                                data-bs-toggle="tooltip"
                                title="{{ $name }}"
                                src="{{ $avatar }}"
                                class="rounded-circle"
                                width="25"
                                height="25">
                        </a>
                    @endforeach
                </p>
            </div>
        </div>
    </div>
</div>
