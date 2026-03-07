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
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Gate, Route, URL};
    use Illuminate\Support\{Collection, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Contract') }}
@endsection

@push(StacksConstants::ADM_SCR_PG)
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Contract') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $contractsIndexBaseRouteName = VW::CTC.'.index';
            $contractsIndexKebabRouteName = Str::kebab($contractsIndexBaseRouteName);
            $contractsIndexResolvedRouteName = Route::has($contractsIndexBaseRouteName)
                ? $contractsIndexBaseRouteName
                : (Route::has($contractsIndexKebabRouteName) ? $contractsIndexKebabRouteName : null);
            $contractsIndexUrl = $contractsIndexResolvedRouteName ? route($contractsIndexResolvedRouteName) : '#';
            $contractsLangValue = isset($lang) ? $lang : Utility::fetchUserLang();
            $contractsIndexGuardMessage = Utility::fetchLinkMessage($contractsLangValue, VW::CTC, 'index_route_unavailable')
                ?? 'Contracts index route is unavailable. Please contact technical support or your domain administrator.';
            $contractsIndexLinkId = 'contracts-index-list-link';
        @endphp
        <a id="{{ $contractsIndexLinkId }}"
        href="{{ $contractsIndexUrl }}"
        class="{{ VC::BT_SM_PM }}"
        data-sv-localized="true"
        data-url="{{ $contractsIndexUrl }}"
        data-guard-msg="{{ $contractsIndexGuardMessage }}"
        data-bs-toggle="tooltip"
        title="{{ __('List View') }}">
            <i class="ti ti-list"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer src="{{ asset('assets/js/routes/contracts/index.js') }}"></script>
        @endpush
        @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN || $user?->{UsersConstants::COL_TP} == PermissionsConstants::SA)
            @php
                $createRoute = VW::CTC . '.create';
                $createHref  = Route::has($createRoute) ? route($createRoute) : '#';
                $createGuard = Utility::fetchLinkMessage($lang, VW::CTC, 'create_route_unavailable')
                                ?? 'Create route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a href="#"
               data-size="md"
               data-url="{{ $createHref }}"
               data-ajax-popup="true"
               data-sv-localized="true"
               data-guard-msg="{{ $createGuard }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create New Contract') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (function () {
                        try {
                            if (!window.svToastOrAlert) {
                                window.svToastOrAlert = function (msg) {
                                    try {
                                        var hasBootstrap = !!(window.bootstrap && window.bootstrap.Toast);
                                        if (!hasBootstrap) { alert(msg); return; }
                                        var t = document.getElementById('route-guard-toast');
                                        if (!t) {
                                            t = document.createElement('div');
                                            t.id = 'route-guard-toast';
                                            t.className = 'toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3';
                                            t.setAttribute('role','alert');
                                            t.setAttribute('aria-live','assertive');
                                            t.setAttribute('aria-atomic','true');
                                            t.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>';
                                            document.body.appendChild(t);
                                        }
                                        var body = t.querySelector('.toast-body');
                                        if (body) body.textContent = msg;
                                        new window.bootstrap.Toast(t, { delay: 4000 }).show();
                                    } catch (e) { alert(msg); }
                                };
                            }

                            var idxA = document.querySelector('a.btn.btn-sm.btn-primary[href="{{ $contractsIndexUrl }}"]');
                            if (idxA && "{{ $contractsIndexUrl }}" === "#") {
                                idxA.addEventListener('click', function (e) {
                                    e.preventDefault();
                                    window.svToastOrAlert(idxA.getAttribute('data-guard-msg'));
                                });
                            }

                            var createA = document.querySelector('a[data-url="{{ $createHref }}"]');
                            if (createA && "{{ $createHref }}" === "#") {
                                createA.addEventListener('click', function (e) {
                                    e.preventDefault();
                                    window.svToastOrAlert(createA.getAttribute('data-guard-msg'));
                                });
                            }
                        } catch (_) {}
                    })();
                </script>
            @endpush
        @endif
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        @php
            $list = Utility::isFilled($contracts ?? []) ? $contracts : [];
        @endphp
        @forelse($list as $contract)
            @php
                $cid = isset($contract->id) ? (string)$contract->id : '';
                $subject = (isset($contract->subject) && $contract->subject !== '') ? (string)$contract->subject : __('No subject available');
                $desc = (isset($contract->description) && $contract->description !== '') ? (string)$contract->description : __('No description available');
                $typeName = (string)(data_get($contract,'types.name') ?: __('No type available'));
                $clientName = (string)(data_get($contract,'clients.name') ?: __('No client available'));
                $valueRaw = isset($contract->value) ? $contract->value : null;
                $startRaw = isset($contract->start_date) ? $contract->start_date : null;
                $endRaw = isset($contract->end_date) ? $contract->end_date : null;
                $showRoute   = VW::CTC . '.show';
                $showHref    = Route::has($showRoute) && $cid !== '' ? route($showRoute, $cid) : '#';
                $showGuard   = Utility::fetchLinkMessage($lang, VW::CTC, 'show_route_unavailable')
                               ?? 'Show route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <a href="{{ $showHref }}"
                           class="mb-0"
                           data-sv-localized="true"
                           data-guard-msg="{{ $showGuard }}">
                            {{ $subject }}
                        </a>
                        @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN || $user?->{UsersConstants::COL_TP} == PermissionsConstants::SA)
                            @php
                                $editRoute   = VW::CTC . '.edit';
                                $editHref    = (Route::has($editRoute) && $cid !== '') ? route($editRoute, $cid) : '#';
                                $editGuard   = Utility::fetchLinkMessage($lang, VW::CTC, 'edit_route_unavailable')
                                               ?? 'Edit route is unavailable. Please contact technical support or your domain administrator.';
                                $destroyRoute = VW::CTC . '.destroy';
                                $deleteGuard  = Utility::fetchLinkMessage($lang, VW::CTC, 'delete_route_unavailable')
                                                ?? 'Delete route is unavailable. Please contact technical support or your domain administrator.';
                                $confirmMsg   = __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?')
                                                .'|'.
                                                __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                $deleteFormId = 'delete-form-' . $cid;
                                $openParams   = ['method' => 'DELETE', 'id' => $deleteFormId, 'data-sv-localized' => 'true', 'data-guard-msg' => $deleteGuard];
                                if (Route::has($destroyRoute) && $cid !== '') {
                                    $openParams['route'] = [$destroyRoute, $cid];
                                } else {
                                    $openParams['url'] = '#';
                                }
                            @endphp
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="{{ VC::DRP_MN_EM }}">
                                        <a href="#!"
                                           data-size="md"
                                           data-url="{{ $editHref }}"
                                           data-ajax-popup="true"
                                           class="dropdown-item"
                                           data-sv-localized="true"
                                           data-guard-msg="{{ $editGuard }}"
                                           data-bs-original-title="{{ __('Edit User') }}">
                                            <i class="ti ti-pencil"></i>
                                            <span>{{ __('Edit') }}</span>
                                        </a>

                                        {!! Form::open($openParams) !!}
                                            <a href="#!"
                                               class="dropdown-item bs-pass-para"
                                               data-sv-localized="true"
                                               data-guard-msg="{{ $deleteGuard }}"
                                               data-confirm="{{ $confirmMsg }}"
                                               data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();">
                                                <i class="ti ti-archive"></i>
                                                <span>{{ __('Delete') }}</span>
                                            </a>
                                        {!! Form::close() !!}
                                    </div>
                                </div>
                            </div>
                            <script defer>
                                (function () {
                                    try {
                                        var root = document.currentScript && document.currentScript.parentElement ? document.currentScript.parentElement : document;
                                        if (!window.svToastOrAlert) {
                                            window.svToastOrAlert = function (msg) {
                                                try {
                                                    var hasBootstrap = !!(window.bootstrap && window.bootstrap.Toast);
                                                    if (!hasBootstrap) { alert(msg); return; }
                                                    var t = document.getElementById('route-guard-toast');
                                                    if (!t) {
                                                        t = document.createElement('div');
                                                        t.id = 'route-guard-toast';
                                                        t.className = 'toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3';
                                                        t.setAttribute('role','alert');
                                                        t.setAttribute('aria-live','assertive');
                                                        t.setAttribute('aria-atomic','true');
                                                        t.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>';
                                                        document.body.appendChild(t);
                                                    }
                                                    var body = t.querySelector('.toast-body');
                                                    if (body) body.textContent = msg;
                                                    new window.bootstrap.Toast(t, { delay: 4000 }).show();
                                                } catch (e) { alert(msg); }
                                            };
                                        }

                                        var box = document.querySelector('.card a.mb-0[href="{{ $showHref }}"]');
                                        if (box && "{{ $showHref }}" === "#") {
                                            box.addEventListener('click', function (e) {
                                                e.preventDefault();
                                                window.svToastOrAlert(box.getAttribute('data-guard-msg'));
                                            });
                                        }

                                        var editA = document.querySelector('a.dropdown-item[data-url="{{ $editHref }}"]');
                                        if (editA && "{{ $editHref }}" === "#") {
                                            editA.addEventListener('click', function (e) {
                                                e.preventDefault();
                                                window.svToastOrAlert(editA.getAttribute('data-guard-msg'));
                                            });
                                        }

                                        var delForm = document.getElementById('{{ $deleteFormId }}');
                                        if (delForm) {
                                            var hasAction = (delForm.getAttribute('action') || '').trim() !== '';
                                            var actionIsHash = (delForm.getAttribute('action') || '#') === '#';
                                            if (!hasAction || actionIsHash) {
                                                var delA = delForm.parentElement && delForm.parentElement.querySelector('a.dropdown-item.bs-pass-para');
                                                if (delA) {
                                                    delA.addEventListener('click', function (e) {
                                                        e.preventDefault();
                                                        window.svToastOrAlert(delA.getAttribute('data-guard-msg'));
                                                    });
                                                }
                                            }
                                        }
                                    } catch (_) {}
                                })();
                            </script>
                        @endif
                    </div>
                    <div class="card-body py-3 flex-grow-1">
                        <p class="text-sm mb-0">{{ $desc }}</p>
                    </div>
                    <div class="card-footer py-0">
                        <ul class="{{ VC::LG_FLSH }}">
                            <li class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <span class="form-label">{{ __('Contract Type') }}:</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="badge bg-secondary p-2 px-3 rounded">{{ $typeName }}</span>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <span class="form-label">{{ __('Contract Value') }}:</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="badge bg-secondary p-2 px-3 rounded">
                                            {{ is_numeric($valueRaw) && method_exists($user, 'priceFormat') ? ($user?->priceFormat($valueRaw) ?? __('Failed to format value')) : __('No value available') }}
                                        </span>
                                    </div>
                                </div>
                            </li>

                            @if($user?->{UsersConstants::COL_TP} != PermissionsConstants::CL)
                                <li class="list-group-item px-0">
                                    <div class="row align-items-center">
                                        <div class="col-6">
                                            <span class="form-label">{{ __('Client') }}:</span>
                                        </div>
                                        <div class="col-6 text-end">
                                            {{ $clientName }}
                                        </div>
                                    </div>
                                </li>
                            @endif

                            <li class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <small>{{ __('Start Date') }}:</small>
                                        <div class="h6 mb-0">
                                            {{ $startRaw && method_exists($user, 'dateFormat') ? ($user?->dateFormat($startRaw) ?? __('Failed to format date')) : __('No start date available') }}
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <small>{{ __('End Date') }}:</small>
                                        <div class="h6 mb-0">
                                            {{ $endRaw && method_exists($user, 'dateFormat') ? ($user?->dateFormat($endRaw) ?? __('Failed to format date')) : __('No end date available') }}
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-muted">
                        {{ __('No contracts available') }}
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection
