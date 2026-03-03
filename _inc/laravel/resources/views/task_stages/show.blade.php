@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $taskStage ??= null;

    $stageName        = data_get($taskStage, 'name') ?: __('Unnamed Stage');
    $stageDescription = data_get($taskStage, 'description') ?: '';
    $stageColor       = data_get($taskStage, 'color') ?: 'CCCCCC';
    $stagePriority    = data_get($taskStage, 'priority_label') ?: __('None');
    $stageStatus      = data_get($taskStage, 'status_label') ?: __('None');
    $stageProgress    = data_get($taskStage, 'progress') ?? 0;
    $stageComplete    = data_get($taskStage, 'complete') ? __('Yes') : __('No');
    $stageOrder       = data_get($taskStage, 'order') ?? 0;
    $stageDueDate     = data_get($taskStage, 'due_date') ? data_get($taskStage, 'due_date')->format('Y-m-d') : __('No due date');
    $stageProjectName = data_get($taskStage, 'project_name') ?: __('N/A');
    $stageTaskName    = data_get($taskStage, 'task_name') ?: __('N/A');
    $stageResponsible = data_get($taskStage, 'responsible_name') ?: __('Unassigned');
    $stageId          = data_get($taskStage, 'id');
    $stageIsOverdue   = data_get($taskStage, 'is_overdue');
    $stageInvolved    = data_get($taskStage, 'involved') ?? [];
    $stageTags        = data_get($taskStage, 'tags') ?? [];

    $editBaseName = VW::PRJ_TSK_STG . '.edit';
    $editResolved = null;
    $editUrl = '#';
    try {
        $editResolved = Route::has($editBaseName)
            ? $editBaseName
            : (Route::has(Str::kebab($editBaseName)) ? Str::kebab($editBaseName) : null);
        $editUrl = ($editResolved && $stageId) ? route($editResolved, $stageId) : '#';
    } catch (\Throwable $e) {
        $editUrl = '#';
    }

    $indexBaseName = VW::PRJ_TSK_STG . '.index';
    $indexResolved = null;
    $indexUrl = '#';
    try {
        $indexResolved = Route::has($indexBaseName)
            ? $indexBaseName
            : (Route::has(Str::kebab($indexBaseName)) ? Str::kebab($indexBaseName) : null);
        $indexUrl = $indexResolved ? route($indexResolved) : '#';
    } catch (\Throwable $e) {
        $indexUrl = '#';
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ $stageName }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
            {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ $indexUrl }}">{{ __('Project Task Stages') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $stageName }}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        <a href="{{ $indexUrl }}"
            class="{{ VC::BT_SM_PM }}"
            data-bs-toggle="tooltip"
            title="{{ __('Back') }}">
            <i class="ti ti-arrow-left text-white"></i>
        </a>
        @can('edit project task stage')
            <a href="{{ $editUrl }}"
                data-url="{{ $editUrl }}"
                data-ajax-popup="true"
                data-title="{{ __('Edit Project Task Stage') }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Edit') }}">
                <i class="{{ VC::TI_PC_WT }}"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }} justify-content-center">
        <div class="col-sm-12 col-md-10 col-xxl-8">
            <div class="{{ VC::CD }} mt-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="{{ VC::MB0 }}">
                        <span class="badge me-2" style="background-color: #{{ e($stageColor) }};">&nbsp;&nbsp;</span>
                        {{ $stageName }}
                    </h5>
                    @if ($stageIsOverdue)
                        <span class="badge bg-danger">{{ __('Overdue') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="{{ VC::RW }}">
                        {{-- Left column: core details --}}
                        <div class="col-md-6">
                            <table class="table table-striped mb-0">
                                <tbody>
                                    <tr>
                                        <th class="w-40">{{ __('Project') }}</th>
                                        <td>{{ $stageProjectName }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Task') }}</th>
                                        <td>{{ $stageTaskName }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Responsible') }}</th>
                                        <td>{{ $stageResponsible }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Priority') }}</th>
                                        <td>{{ $stagePriority }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Status') }}</th>
                                        <td>{{ $stageStatus }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        {{-- Right column: progress & dates --}}
                        <div class="col-md-6">
                            <table class="table table-striped mb-0">
                                <tbody>
                                    <tr>
                                        <th class="w-40">{{ __('Due Date') }}</th>
                                        <td>{{ $stageDueDate }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Order') }}</th>
                                        <td>{{ $stageOrder }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Complete') }}</th>
                                        <td>{{ $stageComplete }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Progress') }}</th>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar"
                                                        role="progressbar"
                                                        style="width: {{ $stageProgress }}%; background-color: #{{ e($stageColor) }};"
                                                        aria-valuenow="{{ $stageProgress }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span>{{ $stageProgress }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Color') }}</th>
                                        <td>
                                            <span class="badge" style="background-color: #{{ e($stageColor) }};">#{{ e($stageColor) }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Description --}}
                    @if (!empty($stageDescription))
                        <hr>
                        <h6>{{ __('Description') }}</h6>
                        <p>{!! e($stageDescription) !!}</p>
                    @endif

                    {{-- Tags --}}
                    @if (is_array($stageTags) && count($stageTags) > 0)
                        <hr>
                        <h6>{{ __('Tags') }}</h6>
                        <div>
                            @foreach ($stageTags as $tag)
                                <span class="badge bg-secondary me-1">{{ e($tag) }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Involved --}}
                    @if (is_array($stageInvolved) && count($stageInvolved) > 0)
                        <hr>
                        <h6>{{ __('Involved') }} ({{ count($stageInvolved) }})</h6>
                        <div>
                            @foreach ($stageInvolved as $person)
                                <span class="badge bg-info me-1">{{ e($person) }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
