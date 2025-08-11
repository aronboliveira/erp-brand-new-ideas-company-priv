@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants
    };
    use App\Models\{ProjectTask, Utility};
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Bug Report') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Project') }}</li>
    <li class="breadcrumb-item">{{ __('Bug Report') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $otherView = $view === 'grid' ? 'list' : 'grid';
        $icon      = $view === 'grid' ? 'ti-list' : 'ti-table';
        $title     = $view === 'grid' ? __('List View') : __('Card View');
    @endphp
    <div class="{{ ViewClassNamesConstants::FEND }}">
        <a href="{{ route(ViewsConstants::BUG . '.view', $otherView) }}"
        class="{{ ViewClassNamesConstants::BT_SM_PM }}"
        data-bs-toggle="tooltip"
        title="{{ $title }}">
            <span class="btn-inner--text"><i class="ti {{ $icon }}"></i></span>
        </a>
        @can(PermissionsConstants::MNG_PRJ)
            <a href="{{ route(ViewsConstants::PRJ . '.index') }}"
            class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            data-bs-toggle="tooltip"
            title="{{ __('Back') }}">
                <span class="btn-inner--icon"><i class="ti ti-arrow-left"></i></span>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table align-items-center">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('Name') }}</th>
                                    <th scope="col">{{ __('Bug Status') }}</th>
                                    <th scope="col">{{ __('Priority') }}</th>
                                    <th scope="col">{{ __('End Date') }}</th>
                                    <th scope="col">{{ __('created By') }}</th>
                                    <th scope="col">{{ __('Assigned To') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @if(count($bugs)>0)
                                    @foreach($bugs as $bug)
                                        @php $checkProject=$user?->checkProject($bug->project_id); @endphp
                                        <tr>
                                            <td>
                                                <span class="{{ ViewClassNamesConstants::H6 }} {{ ViewClassNamesConstants::TXSM }} {{ ViewClassNamesConstants::FW600 }} {{ ViewClassNamesConstants::MB0 }}">
                                                    <a href="{{ route(ViewsConstants::TSK.'.bug',$bug->project_id) }}">{{ $bug->title }}</a>
                                                </span>
                                                <span class="{{ ViewClassNamesConstants::DFL_JCB }} {{ ViewClassNamesConstants::TXSM }} {{ ViewClassNamesConstants::TXT_MT }}">
                                                    <p class="m-0">{{ $bug->project->project_name ?? '' }}</p>
                                                    <span class="me-5 {{ ViewClassNamesConstants::BDG }} p-2 px-3 rounded bg-{{ $checkProject==='Owner'?'success':'warning' }}">
                                                        {{ __($checkProject) }}
                                                    </span>
                                                </span>
                                            </td>
                                            <td>{{ $bug->bug_status->title }}</td>
                                            <td>
                                                <span class="status_badge {{ ViewClassNamesConstants::BDG }} p-2 px-3 rounded bg-{{ __(ProjectTask::$priority_color[$bug->priority]) }}">
                                                    {{ __(ProjectTask::$priority[$bug->priority]) }}
                                                </span>
                                            </td>
                                            <td class="{{ strtotime($bug->due_date)<time()?'text-danger':'' }}">
                                                {{ Utility::getDateFormated($bug->due_date) }}
                                            </td>
                                            <td>
                                                <div class="{{ ViewClassNamesConstants::DFL_AIC }}">
                                                    {{ $bug->createdBy->name }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="avatar-group">
                                                    @php $assignees=$bug->users(); @endphp
                                                    @if($assignees?->count()>0)
                                                        <a href="#" class="{{ ViewClassNamesConstants::AV_CC_SM }}">
                                                            <img data-original-title="{{ $assignees[0]->name ?? '' }}"
                                                                 src="{{ $assignees[0]->avatar?asset('/storage/uploads/avatar/'.$assignees[0]->avatar):asset('/storage/uploads/avatar/avatar.png') }}"
                                                                 title="{{ $assignees[0]->name }}" class="hweb">
                                                        </a>
                                                        @foreach($assignees as $key=>$assignee)
                                                            @if($key>=3) @break @endif
                                                        @endforeach
                                                        @if(count($assignees)>3)
                                                            <a href="#" class="{{ ViewClassNamesConstants::AV_CC_SM }}">
                                                                <img src="{{ $user?->getImgImageAttribute() }}">
                                                            </a>
                                                        @endif
                                                    @else
                                                        {{ __('-') }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-end w-15">
                                                <div class="actions">
                                                    <a class="action-item px-1" data-bs-toggle="tooltip" title="{{ __('Attachment') }}" data-original-title="{{ __('Attachment') }}">
                                                        <i class="{{ ViewClassNamesConstants::TI }} ti-paperclip {{ ViewClassNamesConstants::MR2 }}"></i>{{ count($bug->bugFiles) }}
                                                    </a>
                                                    <a class="action-item px-1" data-bs-toggle="tooltip" title="{{ __('Comment') }}" data-original-title="{{ __('Comment') }}">
                                                        <i class="{{ ViewClassNamesConstants::TI }} ti-brand-hipchat {{ ViewClassNamesConstants::MR2 }}"></i>{{ count($bug->comments) }}
                                                    </a>
                                                    <a class="action-item px-1" data-toggle="tooltip" data-original-title="{{ __('Checklist') }}"></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th scope="col" colspan="7">
                                            <h6 class="text-center">{{ __('No tasks found') }}</h6>
                                        </th>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
