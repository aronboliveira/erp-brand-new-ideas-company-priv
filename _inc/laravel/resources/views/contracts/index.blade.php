@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('contracts/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Contract') }}
@endsection

@push(StacksConstants::ADM_SCR_PG)
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Contract') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            try {
                $gridRoute = VW::CTC . '.grid';
                $gridHref  = Route::has($gridRoute) ? route($gridRoute) : '#';
                $gridGuard = Utility::fetchLinkMessage($lang, VW::CTC, 'grid_route_unavailable')
                            ?? 'Grid view route is unavailable. Please contact technical support or your domain administrator.';
            } catch (\Throwable $e) {
                \Log::error('contracts/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <a href="{{ $gridHref }}"
           class="{{ VC::BT_SM_PM }}"
           data-sv-localized="true"
           data-guard-msg="{{ base64_encode($gridGuard) }}"
           data-bs-toggle="tooltip"
           title="{{ __('Grid View') }}">
            <i class="ti ti-layout-grid"></i>
        </a>
        @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN)
            @php
                try {
                    $createRoute = VW::CTC . '.create';
                    $createHref  = Route::has($createRoute) ? route($createRoute) : '#';
                    $createGuard = Utility::fetchLinkMessage($lang, VW::CTC, 'create_route_unavailable')
                                   ?? 'Create route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('contracts/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="#"
               data-size="md"
               data-url="{{ $createHref }}"
               data-ajax-popup="true"
               data-sv-localized="true"
               data-guard-msg="{{ base64_encode($createGuard) }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create New Contract') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            <script src="{{ asset('assets/js/core/route-guard.js') }}"></script>
            <script defer>
                (function () {
                    try {
                        var showErr = window.RouteGuard?.showToast || function(msg) { alert(msg); };
                        var gridA = document.querySelector('a[href="{{ $gridHref }}"][data-guard-msg]');
                        if (gridA && "{{ $gridHref }}" === "#") {
                            window.RouteGuard?.guardById?.(gridA) || gridA.addEventListener('click', function (e) {
                                e.preventDefault();
                                showErr(gridA.getAttribute('data-guard-msg'));
                            });
                        }
                        var createA = document.querySelector('a[data-url="{{ $createHref }}"][data-guard-msg]');
                        if (createA && "{{ $createHref }}" === "#") {
                            window.RouteGuard?.guardById?.(createA) || createA.addEventListener('click', function (e) {
                                e.preventDefault();
                                showErr(createA.getAttribute('data-guard-msg'));
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
        <div class="{{ VC::CXL12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                            <tr>
                                <th scope="col">{{ __('#') }}</th>
                                <th scope="col">{{ __('Subject') }}</th>
                                @if(($user?->{UsersConstants::COL_TP} ?? '') !== PermissionsConstants::CL)
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
                                    try {
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
                                    } catch (\Throwable $e) {
                                        \Log::error('contracts/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <tr class="font-style" data-id="{{ $cid }}">
                                    <td>
                                        <a href="{{ $showHref }}"
                                           class="{{ VC::BT_OUTPM }}"
                                           data-sv-localized="true"
                                           data-guard-msg="{{ base64_encode($showGuard) }}">
                                            {{ $user?->contractNumberFormat($cid) ?? ('#'.$cid) }}
                                        </a>
                                    </td>
                                    <td>{{ $subject }}</td>
                                    @if(($user?->{UsersConstants::COL_TP} ?? '') !== PermissionsConstants::CL)
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
                                        @if((($user?->{UsersConstants::COL_TP} ?? '') === PermissionsConstants::CPN || ($user?->{UsersConstants::COL_TP} ?? '') === PermissionsConstants::SA) && $status === 'accept')
                                            @php
                                                $copyHref   ??= '#';
                                                try {
                                                    $copyGuard  = Utility::fetchLinkMessage($lang, VW::CTC, 'copy_route_unavailable')
                                                                ?? 'Copy route is unavailable. Please contact technical support or your domain administrator.';
                                                    if((($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN || $user->{UsersConstants::COL_TP} === PermissionsConstants::SA) && $status === 'accept')) {
                                                        $copyRoute = VW::CTC . '.copy';
                                                        $copyHref  = (Route::has($copyRoute) && $cid !== '') ? route($copyRoute, $cid) : '#';
                                                    }
                                                } catch (\Throwable $e) {
                                                    \Log::error('contracts/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                                   data-guard-msg="{{ base64_encode($copyGuard) }}"
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
                                                   data-guard-msg="{{ base64_encode($showGuard) }}"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-original-title="{{ __('View') }}">
                                                    <span class="{{ VC::TXT_WT }}"><i class="{{ VC::TI_EYE }}"></i></span>
                                                </a>
                                            </div>
                                        @endcan
                                        @can('edit contract')
                                            @php
                                                $editHref   ??= '#';
                                                try {
                                                    $editGuard  = Utility::fetchLinkMessage($lang, VW::CTC, 'edit_route_unavailable')
                                                                ?? 'Edit route is unavailable. Please contact technical support or your domain administrator.';
                                                    if(Gate::check('edit contract')) {
                                                        $editRoute = VW::CTC . '.edit';
                                                        $editHref  = (Route::has($editRoute) && $cid !== '') ? route($editRoute, $cid) : '#';
                                                    }
                                                } catch (\Throwable $e) {
                                                    \Log::error('contracts/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <div class="{{ VC::ACT_BTN_INF }}">
                                                <a href="#"
                                                   class="{{ VC::BT_SM_FL_CT }}"
                                                   data-url="{{ $editHref }}"
                                                   data-ajax-popup="true"
                                                   data-size="md"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ base64_encode($editGuard) }}"
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
                                                    try {
                                                        $destroyFormId = 'delete-form-' . $cid;
                                                        $openParams = ['method' => 'DELETE', 'id' => $destroyFormId, 'data-sv-localized'=>'true', 'data-guard-msg'=>$deleteGuard];
                                                        $destroyRoute = VW::CTC . '.destroy';
                                                        if (Route::has($destroyRoute) && $cid !== '') {
                                                            $openParams['route'] = [$destroyRoute, $cid];
                                                        } else {
                                                            $openParams['url'] = '#';
                                                        }
                                                    } catch (\Throwable $e) {
                                                        \Log::error('contracts/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                {!! Form::open($openParams) !!}
                                                    <a href="#"
                                                       class="{{ VC::BT_SM_CT_PR }}"
                                                       data-delete-for="{{ $cid }}"
                                                       data-sv-localized="true"
                                                       data-guard-msg="{{ base64_encode($deleteGuard) }}"
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
                                                var showErr = window.RouteGuard?.showToast || function(msg) { alert(msg); };
                                                var row = document.querySelector('tr[data-id="{{ $cid }}"]');

                                                // show links (both number link and action view)
                                                var showLinks = row ? row.querySelectorAll('a[data-guard-msg][href="{{ $showHref }}"]') : [];
                                                if (showLinks && "{{ $showHref }}" === "#") {
                                                    for (var i=0;i<showLinks.length;i++){
                                                        (function(a){
                                                            window.RouteGuard?.attachGuard?.(a) || a.addEventListener('click', function (e) {
                                                                e.preventDefault();
                                                                showErr(a.getAttribute('data-guard-msg'));
                                                            });
                                                        })(showLinks[i]);
                                                    }
                                                }

                                                // copy button
                                                var copyA = row ? row.querySelector('a[data-url="{{ $copyHref }}"][data-guard-msg]') : null;
                                                if (copyA && "{{ $copyHref }}" === "#") {
                                                    window.RouteGuard?.attachGuard?.(copyA) || copyA.addEventListener('click', function (e) {
                                                        e.preventDefault();
                                                        showErr(copyA.getAttribute('data-guard-msg'));
                                                    });
                                                }

                                                // edit button
                                                var editA = row ? row.querySelector('a[data-url="{{ $editHref }}"][data-guard-msg]') : null;
                                                if (editA && "{{ $editHref }}" === "#") {
                                                    window.RouteGuard?.attachGuard?.(editA) || editA.addEventListener('click', function (e) {
                                                        e.preventDefault();
                                                        showErr(editA.getAttribute('data-guard-msg'));
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
                                                            window.RouteGuard?.attachGuard?.(delA) || delA.addEventListener('click', function (e) {
                                                                e.preventDefault();
                                                                showErr(delA.getAttribute('data-guard-msg'));
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
                                    <td colspan="9" class="{{ VC::TXCT_MT }}">{{ __('No contracts available') }}</td>
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
