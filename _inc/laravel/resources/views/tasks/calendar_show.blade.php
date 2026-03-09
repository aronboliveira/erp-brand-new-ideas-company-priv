@include('partials.helpers.route_helpers')
@php
$task ??= null;
    $pct = safeProgress($task);
    $pcolor = 'secondary';
    try { $pcolor = data_get($task?->taskProgress($task), 'color', 'secondary'); }
    catch (\Throwable $e) { \Illuminate\Support\Facades\Log::error('Blade calendar_show: progress error: '.$e->getMessage()); }

    $estimated = isset($task->estimated_hrs) && $task->estimated_hrs !== null ? number_format($task->estimated_hrs) : '-';
    $milestoneTitle = safeDataGet($task, 'milestone.title') ?: '-';
    $description = safeDataGet($task, 'description') ?: __('No description available');

    $users = [];
    try { $users = method_exists($task, 'users') ? ($task->users() ?? []) : []; }
    catch (\Throwable $e) { $users = []; }
    $usersCount = is_countable($users) ? count($users) : 0;

    $filesCount = count(ensureIterable(safeDataGet($task, 'taskFiles')));
    $commentsCount = count(ensureIterable(safeDataGet($task, 'comments')));
    $checklistCount = 0;
    try { $checklistCount = (int)($task?->countTaskChecklist() ?? 0); }
    catch (\Throwable $e) { $checklistCount = 0; }
@endphp

<div class="{{ VC::C12 }} {{ VC::RW }}">
    <div class="{{ VC::C12 }} pb-2">
        <b>{{ __('Estimated Hours') }}</b> : <span>{{ $estimated }}</span>
    </div>
    <div class="{{ VC::C12 }} pb-2">
        <b>{{ __('Milestone') }}</b> : <span>{{ $milestoneTitle }}</span>
    </div>
    <div class="{{ VC::C12 }}">
        <b>{{ __('Description') }}</b><br><span>{{ $description }}</span>
        <hr>
    </div>
    <div class="{{ VC::C12 }} pb-4">
        <span class="{{ VC::TXSM }}">{{ $pct }}</span>
        <div class="{{ VC::PG }}" style="top:0px">
            <div class="progress-bar bg-{{ $pcolor }}" role="progressbar" style="width: {{ $pct }};"></div>
        </div>
    </div>
</div>

<div class="{{ VC::RW }} pb-2">
    <div class="{{ VC::C6 }}">
        @if($usersCount > 0)
            <div class="{{ VC::AV }}-group">
                @foreach($users as $k => $u)
                    @if($k < 3)
                        @php
                            $img ??= '';
                            try { $img = $u?->getImgImageAttribute(); } catch (\Throwable $e) { $img = ''; }
@endphp
                        <a href="#" class="{{ VC::AV_CC_SM }}"><img src="{{ $img }}" title="{{ safeDataGet($u, 'name') }}"></a>
                    @else @break
                    @endif
                @endforeach
                @if($usersCount > 3)
                    <span class="{{ VC::AV_CC_SM }}" title="+ {{ $usersCount - 3 }}">+ {{ $usersCount - 3 }}</span>
                @endif
            </div>
        @else
            <p>{{ __('No User Found.') }}</p>
        @endif
    </div>
    <div class="{{ VC::C6 }} pt-2">
        <div class="{{ VC::RW }} text-center">
            <div class="col-4" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Attachment') }}"><i class="ti ti-paperclip {{ VC::MR2 }}"></i>{{ $filesCount }}</div>
            <div class="col-4" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Comment') }}"><i class="ti ti-brand-hipchat {{ VC::MR2 }}"></i>{{ $commentsCount }}</div>
            <div class="col-4" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Checklist') }}"><i class="ti ti-list-check {{ VC::MR2 }}"></i>{{ $checklistCount }}</div>
        </div>
    </div>
</div>
