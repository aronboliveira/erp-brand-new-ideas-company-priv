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
    use Illuminate\Support\Facades\{Auth, Gate, Route};
    use Illuminate\Support\Collection;

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
    <div class="{{ VC::FEND }}">
        @php
            $gridRoute = VW::CTC . '.grid';
            $gridHref  = Route::has($gridRoute) ? route($gridRoute) : '#';
            $gridGuard = Utility::fetchLinkMessage($lang, VW::CTC, 'grid_route_unavailable')
                        ?? 'Grid view route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="{{ $gridHref }}"
           class="{{ VC::BT_SM_PM }}"
           data-sv-localized="true"
           data-guard-msg="{{ $gridGuard }}"
           data-bs-toggle="tooltip"
           title="{{ __('Grid View') }}">
            <i class="ti ti-layout-grid"></i>
        </a>
        @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN)
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
            <script defer>
                (function () {
                    try {
                        if (!window.svToastOrAlert) {
                            window.svToastOrAlert = function (msg) {
                                try {
                                    var ok = !!(window.bootstrap && window.bootstrap.Toast);
                                    if (!ok) { alert(msg); return; }
                                    var t = document.getElementById('route-guard-toast');
                                    if (!t) {
                                        t = document.createElement('div');
                                        t.id = 'route-guard-toast';
                                        t.className = 'toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3';
                                        t.setAttribute('role','alert');
                                        t.setAttribute('aria-live','assertive');
                                        t.setAttribute('aria-atomic','true');
                                        t.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                                        document.body.appendChild(t);
                                    }
                                    var body = t.querySelector('.toast-body');
                                    if (body) body.textContent = msg;
                                    new window.bootstrap.Toast(t, { delay: 4000 }).show();
                                } catch (e) { alert(msg); }
                            };
                        }

                        var gridA = document.querySelector('a[href="{{ $gridHref }}"][data-guard-msg]');
                        if (gridA && "{{ $gridHref }}" === "#") {
                            gridA.addEventListener('click', function (e) {
                                e.preventDefault();
                                window.svToastOrAlert(gridA.getAttribute('data-guard-msg'));
                            });
                        }
                        var createA = document.querySelector('a[data-url="{{ $createHref }}"][data-guard-msg]');
                        if (createA && "{{ $createHref }}" === "#") {
                            createA.addEventListener('click', function (e) {
                                e.preventDefault();
                                window.svToastOrAlert(createA.getAttribute('data-guard-msg'));
                            });
                        }
                    } catch (_) {}
                })();
            </script>
        @endif
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-xl-12">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                            <tr>
                                <th scope="col">{{ __('#') }}</th>
                                <th scope="col">{{ __('Subject') }}</th>
                                @if(($user?->{UsersConstants::COL_TP} ?? '') !== UsersConstants::CL)
                                    <th scope="col">{{ __('Client') }}</th>
                                @endif
                                <th scope="col">{{ __('Project') }}</th>
                                <th scope="col">{{ __('Contract Type') }}</th>
                                <th scope="col">{{ __('Contract Value') }}</th>
                                <th scope="col">{{ __('Start Date') }}</th>
                                <th scope="col">{{ __('End Date') }}</th>
                                <th scope="col">{{ __('Action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $rows = (($contracts ?? null) instanceof Collection || is_array($contracts ?? null)) ? $contracts : [];
                            @endphp
                            @forelse($rows as $contract)
                                @php
                                    $cid        = (string) data_get($contract,'id','');
                                    $subject    = (string) (data_get($contract,'subject') ?: __('No subject available'));
                                    $clientName = (string) (data_get($contract,'clients.name') ?: '-');
                                    $project    = (string) (data_get($contract,'projects.project_name') ?: '-');
                                    $typeName   = (string) (data_get($contract,'types.name') ?: __('No type available'));
                                    $valueRaw   = data_get($contract,'value');
                                    $startRaw   = data_get($contract,'start_date');
                                    $endRaw     = data_get($contract,'end_date');
                                    $status     = (string) (data_get($contract,'status') ?: '');
                                    $showRoute  = VW::CTC . '.show';
                                    $showHref   = Route::has($showRoute) && $cid !== '' ? route($showRoute, $cid) : '#';
                                    $showGuard  = Utility::fetchLinkMessage($lang, VW::CTC, 'show_route_unavailable')
                                                 ?? 'Show route is unavailable. Please contact technical support or your domain administrator.';
                                    $deleteGuard   = Utility::fetchLinkMessage($lang, VW::CTC, 'delete_route_unavailable')
                                                   ?? 'Delete route is unavailable. Please contact technical support or your domain administrator.';
                                    $confirmMsg    = __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?')
                                                    .'|'.
                                                    __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                @endphp
                                <tr class="font-style" data-id="{{ $cid }}">
                                    <td>
                                        <a href="{{ $showHref }}"
                                           class="{{ VC::BT_OUTPM }}"
                                           data-sv-localized="true"
                                           data-guard-msg="{{ $showGuard }}">
                                            {{ $user?->contractNumberFormat($cid) ?? ('#'.$cid) }}
                                        </a>
                                    </td>
                                    <td>{{ $subject }}</td>
                                    @if(($user?->{UsersConstants::COL_TP} ?? '') !== UsersConstants::CL)
                                        <td>{{ $clientName }}</td>
                                    @endif
                                    <td>{{ $project }}</td>
                                    <td>{{ $typeName }}</td>
                                    <td>
                                        {{ is_numeric($valueRaw)
                                            ? ($user?->priceFormat($valueRaw) ?? __('Failed to format value'))
                                            : __('No value available') }}
                                    </td>
                                    <td>{{ $startRaw ? ($user?->dateFormat($startRaw) ?? __('Failed to format date')) : __('No start date available') }}</td>
                                    <td>{{ $endRaw   ? ($user?->dateFormat($endRaw)   ?? __('Failed to format date')) : __('No end date available') }}</td>
                                    <td class="action">
                                        @if(($user?->{UsersConstants::COL_TP} ?? '') === PermissionsConstants::CPN && $status === 'accept')
                                            @php
                                                $copyHref   = '#';
                                                $copyGuard  = Utility::fetchLinkMessage($lang, VW::CTC, 'copy_route_unavailable')
                                                            ?? 'Copy route is unavailable. Please contact technical support or your domain administrator.';
                                                if(($user?->{UsersConstants::COL_TP} ?? '') === PermissionsConstants::CPN && $status === 'accept') {
                                                    $copyRoute = VW::CTC . '.copy';
                                                    $copyHref  = (Route::has($copyRoute) && $cid !== '') ? route($copyRoute, $cid) : '#';
                                                }
                                            @endphp
                                            <div class="{{ VC::ACT_BTN_PRIM }}">
                                                <a href="#"
                                                   data-size="lg"
                                                   data-url="{{ $copyHref }}"
                                                   data-ajax-popup="true"
                                                   data-title="{{ __('Copy Contract') }}"
                                                   class="{{ VC::BT_SM_FL_CT }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ $copyGuard }}"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="{{ __('Duplicate') }}">
                                                    <i class="ti ti-copy {{ VC::TXT_WT }}"></i>
                                                </a>
                                            </div>
                                        @endif
                                        @can('show contract')
                                            <div class="{{ VC::ACT_BTN_WRN }}">
                                                <a href="{{ $showHref }}"
                                                   class="{{ VC::BT_SM_FL_CT }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ $showGuard }}"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-original-title="{{ __('View') }}">
                                                    <span class="{{ VC::TXT_WT }}"><i class="{{ VC::TI_EYE }}"></i></span>
                                                </a>
                                            </div>
                                        @endcan
                                        @can('edit contract')
                                            @php
                                                $editHref   = '#';
                                                $editGuard  = Utility::fetchLinkMessage($lang, VW::CTC, 'edit_route_unavailable')
                                                            ?? 'Edit route is unavailable. Please contact technical support or your domain administrator.';
                                                if(Gate::check('edit contract')) {
                                                    $editRoute = VW::CTC . '.edit';
                                                    $editHref  = (Route::has($editRoute) && $cid !== '') ? route($editRoute, $cid) : '#';
                                                }
                                            @endphp
                                            <div class="{{ VC::ACT_BTN_INF }}">
                                                <a href="#"
                                                   class="{{ VC::BT_SM_FL_CT }}"
                                                   data-url="{{ $editHref }}"
                                                   data-ajax-popup="true"
                                                   data-size="md"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ $editGuard }}"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('Edit') }}"
                                                   data-title="{{ __('Edit Contract') }}">
                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                </a>
                                            </div>
                                        @endcan

                                        @can('delete contract')
                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                @php
                                                    $destroyFormId = 'delete-form-' . $cid;
                                                    $openParams = ['method' => 'DELETE', 'id' => $destroyFormId, 'data-sv-localized'=>'true', 'data-guard-msg'=>$deleteGuard];
                                                    $destroyRoute = VW::CTC . '.destroy';
                                                    if (Route::has($destroyRoute) && $cid !== '') {
                                                        $openParams['route'] = [$destroyRoute, $cid];
                                                    } else {
                                                        $openParams['url'] = '#';
                                                    }
                                                @endphp
                                                {!! Form::open($openParams) !!}
                                                    <a href="#"
                                                       class="{{ VC::BT_SM_CT_PR }}"
                                                       data-delete-for="{{ $cid }}"
                                                       data-sv-localized="true"
                                                       data-guard-msg="{{ $deleteGuard }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Delete') }}"
                                                       data-confirm="{{ $confirmMsg }}"
                                                       data-confirm-yes="document.getElementById('{{ $destroyFormId }}').submit();">
                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                    </a>
                                                {!! Form::close() !!}
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer>
                                        (function () {
                                            try {
                                                if (!window.svToastOrAlert) {
                                                    window.svToastOrAlert = function (msg) {
                                                        try {
                                                            var ok = !!(window.bootstrap && window.bootstrap.Toast);
                                                            if (!ok) { alert(msg); return; }
                                                            var t = document.getElementById('route-guard-toast');
                                                            if (!t) {
                                                                t = document.createElement('div');
                                                                t.id = 'route-guard-toast';
                                                                t.className = 'toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3';
                                                                t.setAttribute('role','alert');
                                                                t.setAttribute('aria-live','assertive');
                                                                t.setAttribute('aria-atomic','true');
                                                                t.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                                                                document.body.appendChild(t);
                                                            }
                                                            var body = t.querySelector('.toast-body');
                                                            if (body) body.textContent = msg;
                                                            new window.bootstrap.Toast(t, { delay: 4000 }).show();
                                                        } catch (e) { alert(msg); }
                                                    };
                                                }

                                                var row = document.querySelector('tr[data-id="{{ $cid }}"]');

                                                // show links (both number link and action view)
                                                var showLinks = row ? row.querySelectorAll('a[data-guard-msg][href="{{ $showHref }}"]') : [];
                                                if (showLinks && "{{ $showHref }}" === "#") {
                                                    for (var i=0;i<showLinks.length;i++){
                                                        (function(a){
                                                            a.addEventListener('click', function (e) {
                                                                e.preventDefault();
                                                                window.svToastOrAlert(a.getAttribute('data-guard-msg'));
                                                            });
                                                        })(showLinks[i]);
                                                    }
                                                }

                                                // copy button
                                                var copyA = row ? row.querySelector('a[data-url="{{ $copyHref }}"][data-guard-msg]') : null;
                                                if (copyA && "{{ $copyHref }}" === "#") {
                                                    copyA.addEventListener('click', function (e) {
                                                        e.preventDefault();
                                                        window.svToastOrAlert(copyA.getAttribute('data-guard-msg'));
                                                    });
                                                }

                                                // edit button
                                                var editA = row ? row.querySelector('a[data-url="{{ $editHref }}"][data-guard-msg]') : null;
                                                if (editA && "{{ $editHref }}" === "#") {
                                                    editA.addEventListener('click', function (e) {
                                                        e.preventDefault();
                                                        window.svToastOrAlert(editA.getAttribute('data-guard-msg'));
                                                    });
                                                }

                                                // delete button/form
                                                var delForm = document.getElementById('{{ $destroyFormId }}');
                                                if (delForm) {
                                                    var hasAction = (delForm.getAttribute('action') || '').trim() !== '';
                                                    var actionIsHash = (delForm.getAttribute('action') || '#') === '#';
                                                    if (!hasAction || actionIsHash) {
                                                        var delA = row ? row.querySelector('a[data-delete-for="{{ $cid }}"][data-guard-msg]') : null;
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
                                @endpush
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">{{ __('No contracts available') }}</td>
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
