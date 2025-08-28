@php
    use App\Config\Constants\ViewClassNamesConstants as VC;

    $progress = $task?->taskProgress($task) ?? ['percentage' => '0%', 'color' => 'secondary'];
    $pct      = $progress['percentage'] ?? '0%';
    $pcolor   = $progress['color'] ?? 'secondary';

    $estimated = isset($task->estimated_hrs) && $task->estimated_hrs !== null ? number_format($task->estimated_hrs) : '-';
    $milestoneTitle = data_get($task, 'milestone.title') ?: '-';

    $users = method_exists($task, 'users') ? ($task->users() ?? []) : [];
    $usersCount = is_countable($users) ? count($users) : 0;
@endphp

<div class="{{ VC::C12 }} {{ VC::RW }}">
    <div class="{{ VC::C12 }} pb-2">
        <b>{{ __(' Estimated Hours') }}</b> : <span>{{ $estimated ?? __('Failed to get estimated hours') }}</span>
    </div>
    <div class="{{ VC::C12 }} pb-2">
        <b>{{ __('Milestone') }}</b> : <span>{{ $milestoneTitle ?? __('No title available') }}</span>
    </div>
    <div class="{{ VC::C12 }}">
        <b>{{ __('Description') }}</b> <br> <span>{{ !empty($task->description) ? $task->description : __('No description available') }}</span>
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
    <div class="col-6">
        @if($usersCount > 0)
            <div class="{{ VC::AV }}-group">
                @foreach($users as $k => $u)
                    @if($k < 3)
                        <a href="#" class="{{ VC::AV_CC_SM }}">
                            <img src="{{ $u?->getImgImageAttribute() }}" title="{{ $u?->name }}">
                        </a>
                    @else
                        @break
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
    <div class="col-6 pt-2">
        <div class="{{ VC::RW }} text-center">
            <div class="col-4" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Attachment') }}">
                <i class="ti ti-paperclip mr-2"></i>{{ is_countable($task->taskFiles) ? count($task->taskFiles) : 0 }}
            </div>
            <div class="col-4" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Comment') }}">
                <i class="ti ti-brand-hipchat mr-2"></i>{{ is_countable($task->comments) ? count($task->comments) : 0 }}
            </div>
            <div class="col-4" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Checklist') }}">
                <i class="ti ti-list-check mr-2"></i>{{ (int) ($task?->countTaskChecklist() ?? 0) }}
            </div>
        </div>
    </div>
</div>
