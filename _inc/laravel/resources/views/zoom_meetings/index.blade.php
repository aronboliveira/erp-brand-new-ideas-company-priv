@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
        UsersConstants,
        PermissionsConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\{Facades\Auth, Facades\Route, Collection, Str};

    $user          = Auth::user();
    $lang          = Utility::fetchUserLang(user: $user);
    $profile       = Utility::getFile('uploads/avatar/');
    $avatarFolder  = trim(config('chatify.user_avatar.folder','uploads/avatar'),'/');
    $defaultAvatar = asset('/storage/'.$avatarFolder.'/avatar.png');

    $calBase  = VW::ZMM . '.calendar';
    $calKebab = Str::kebab($calBase);
    $calName  = Route::has($calBase) ? $calBase : (Route::has($calKebab) ? $calKebab : null);
    $calUrl   = $calName ? route($calName) : '#';
    $calGuard = Utility::fetchLinkMessage($lang, VW::ZMM, 'calendar_zoom_meeting_unavailable') ?? 'Calendar route is unavailable. Please contact technical support or your domain administrator.';
    $calId    = 'zoom-calendar-link';
    $createBase  = VW::ZMM . '.create';
    $createKebab = Str::kebab($createBase);
    $createName  = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
    $createUrl   = $createName ? route($createName) : '#';
    $createGuard = Utility::fetchLinkMessage($lang, VW::ZMM, 'create_zoom_meeting_unavailable') ?? 'Create zoom meeting route is unavailable. Please contact technical support or your domain administrator.';
    $createId    = 'zoom-create-link';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Zoom Meeting') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Zoom Meeting') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        <a id="{{ $calId }}"
           href="{{ $calUrl }}"
           data-url="{{ $calUrl }}"
           class="{{ VC::BT_SM_PM }}"
           data-bs-toggle="tooltip"
           title="{{ __('Calendar View') }}"
           data-original-title="{{ __('Calendar View') }}"
           data-guard-msg="{{ $calGuard }}"
           data-sv-localized="true">
            <i class="{{ VC::TI_CLD }}"></i>
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
           data-guard-msg="{{ $createGuard }}"
           data-sv-localized="true">
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Meeting Time') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Join URL') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if(($user?->{UsersConstants::COL_TP} ?? null) === PermissionsConstants::CPN)
                                        <th class="text-end">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse((($meetings ?? null) instanceof Collection || is_array($meetings ?? null)) ? $meetings : [] as $item)
                                    @php
                                        $title       = (string) (data_get($item,'title') ?: __('No title available'));
                                        $projectName = (string) (data_get($item,'projectName.project_name') ?: __('No project available'));
                                        $startDate   = data_get($item,'start_date');
                                        $duration    = data_get($item,'duration');
                                        $creatorId   = (string) (data_get($item,'created_by',''));
                                        $itemId      = (string) (data_get($item,'id',''));
                                        $canJoin     = method_exists($item,'checkDateTime') ? (bool) $item->checkDateTime() : false;
                                        $startUrl    = (string) (data_get($item,'start_url','#'));
                                        $joinUrl     = (string) (data_get($item,'join_url','#'));
                                        $status      = (string) (data_get($item,'status',''));
                                        $userList    = method_exists($item,'users') ? $item->users(data_get($item,'user_id')) : null;
                                        $isIterable  = ($userList instanceof Collection && $userList->isNotEmpty()) || (is_array($userList) && !empty($userList));
                                    @endphp
                                    <tr>
                                        <td>{{ $title }}</td>
                                        <td>{{ $projectName }}</td>
                                        <td>
                                            @if($isIterable)
                                                <div class="avatar-group">
                                                    @foreach($userList as $projectUser)
                                                        @php
                                                            $puName   = (string) (data_get($projectUser,'name') ?: __('Unknown user'));
                                                            $puAvatar = data_get($projectUser,'avatar');
                                                            $src      = $puAvatar ? ($profile . $puAvatar) : $defaultAvatar;
                                                        @endphp
                                                        <img alt="{{ __('image') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $puName }}" src="{{ $src }}" class="{{ VC::AV_CC_SM }} avatar-group" width="25" height="25">
                                                    @endforeach
                                                </div>
                                            @else
                                                {{ __('No users available') }}
                                            @endif
                                        </td>
                                        <td>{{ $startDate ? ($user?->dateFormat($startDate) ?? __('Failed to get meeting time')) : __('No meeting time available') }}</td>
                                        <td>{{ is_numeric($duration) ? $duration.' '.__('Minutes') : __('No duration available') }}</td>
                                        <td>
                                            @if($canJoin)
                                                @if((string) ($user?->id ?? '') === $creatorId && $startUrl !== '#')
                                                    <a href="{{ $startUrl }}" target="_blank">{{ __('Start meeting') }} <i class="ti ti-external-link-square-alt"></i></a>
                                                @elseif($joinUrl !== '#')
                                                    <a href="{{ $joinUrl }}" target="_blank">{{ __('Join meeting') }} <i class="ti ti-external-link-square-alt"></i></a>
                                                @else
                                                    {{ __('No meeting link available') }}
                                                @endif
                                            @else
                                                {{ __('No meeting link available') }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($canJoin)
                                                @if($status === 'waiting')
                                                    <span class="badge bg-info p-2 px-3 rounded status_badge">{{ ucfirst($status) }}</span>
                                                @else
                                                    <span class="badge bg-success p-2 px-3 rounded status_badge">{{ $status !== '' ? ucfirst($status) : __('No status available') }}</span>
                                                @endif
                                            @else
                                                <span class="badge bg-danger p-2 px-3 rounded status_badge">{{ __('End') }}</span>
                                            @endif
                                        </td>
                                        @if(($user?->{UsersConstants::COL_TP} ?? null) === PermissionsConstants::CPN)
                                            @php
                                                $delBase   = VW::ZMM . '.destroy';
                                                $delKebab  = Str::kebab($delBase);
                                                $delName   = Route::has($delBase) ? $delBase : (Route::has($delKebab) ? $delKebab : null);
                                                $delUrl    = ($delName && $itemId) ? route($delName, [$itemId]) : '#';
                                                $delGuard  = Utility::fetchLinkMessage($lang, VW::ZMM, 'delete_zoom_meeting_unavailable') ?? 'Delete zoom meeting route is unavailable. Please contact technical support or your domain administrator.';
                                                $formId    = 'delete-form-' . $itemId;
                                            @endphp
                                            <td class="text-end">
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method'               => 'DELETE',
                                                        'url'                  => $delUrl,
                                                        'id'                   => $formId,
                                                        'data-resolved-action' => $delUrl,
                                                        'data-guard-msg'       => $delGuard,
                                                        'data-sv-localized'    => 'true',
                                                    ]) !!}
                                                        <a href="#"
                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-original-title="{{ __('Delete') }}"
                                                           data-confirm="{{ __(Utility::fetchLinkMessage($lang ?? null, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang ?? null, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            </td>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script>
                                                    (() => {
                                                        try {
                                                            const f = document.getElementById('{{ $formId }}');
                                                            if (!f || f.getAttribute('data-listener-active') === 'true') return;
                                                            f.setAttribute('data-listener-active', 'true');
                                                            const resolved = f.getAttribute('data-resolved-action') || '#';
                                                            if (f.hasAttribute('action') && (!f.getAttribute('action') || f.getAttribute('action') === '#') && resolved !== '#') {
                                                                f.setAttribute('action', resolved);
                                                            }
                                                            f.addEventListener('submit', (e) => {
                                                                try {
                                                                    const action = f.getAttribute('action') || '#';
                                                                    if (action && action !== '#') return;
                                                                    e.preventDefault();
                                                                    const msg = f.getAttribute('data-guard-msg') || 'Delete zoom meeting route is unavailable. Please contact technical support or your domain administrator.';
                                                                    let container = document.getElementById('toast-container');
                                                                    if (!container) { container = document.createElement('div'); container.id = 'toast-container'; document.body.appendChild(container); }
                                                                    const hasBs = typeof window.bootstrap !== 'undefined' && window.bootstrap?.Toast;
                                                                    if (hasBs) {
                                                                        const toast = document.createElement('div');
                                                                        toast.className = 'toast';
                                                                        toast.setAttribute('role','alert');
                                                                        toast.setAttribute('aria-live','assertive');
                                                                        toast.setAttribute('aria-atomic','true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toast.appendChild(body);
                                                                        container.appendChild(toast);
                                                                        try { window.bootstrap.Toast.getOrCreateInstance(toast).show(); } catch { alert(msg); }
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    f.setAttribute('data-failed-route', 'true');
                                                                } catch {}
                                                            });
                                                        } catch {}
                                                    })();
                                                </script>
                                            @endpush
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">{{ __('No meetings available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/zoomMeetings/index.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/zoomMeetings/lang/actions.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/zoomMeetings/actions.js') }}"></script>
    {{--    <script src="{{url('assets/js/daterangepicker.js')}}"></script>--}}
@endpush