@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        SettingsConstants as SC,
        StacksConstants,
        UsersConstants,
        ViewClassNamesConstants as VC,
        ViewsConstants as VW,
        YieldingConstants,
    };
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    $lang = Utility::fetchUserLang(user: $user);

    $ns = VW::DL;

    $kanbanName  = "{$ns}.index";
    $createName  = "{$ns}.create";
    $showName    = "{$ns}.show";
    $editName    = "{$ns}.edit";
    $destroyName = "{$ns}.destroy";

    $hasKanban  = Route::has($kanbanName);
    $hasCreate  = Route::has($createName);
    $hasShow    = Route::has($showName);
    $hasEdit    = Route::has($editName);
    $hasDelete  = Route::has($destroyName);

    $kanbanGuard  = Utility::fetchLinkMessage($lang, $ns, 'deals_index_route_unavailable')
        ?? 'Deals Kanban route is unavailable. Please contact technical support or your domain administrator.';
    $createGuard  = Utility::fetchLinkMessage($lang, $ns, 'deals_create_route_unavailable')
        ?? 'Deal create route is unavailable. Please contact technical support or your domain administrator.';
    $showGuard    = Utility::fetchLinkMessage($lang, $ns, 'deal_show_route_unavailable')
        ?? 'Deal show route is unavailable. Please contact technical support or your domain administrator.';
    $editGuard    = Utility::fetchLinkMessage($lang, $ns, 'deals_edit_route_unavailable')
        ?? 'Deal edit route is unavailable. Please contact technical support or your domain administrator.';
    $deleteGuard  = Utility::fetchLinkMessage($lang, $ns, 'deal_destroy_route_unavailable')
        ?? 'Delete deal route is unavailable. Please contact technical support or your domain administrator.';

    $cntRaw = $cnt_deal ?? $cntDeal ?? null;
    if (is_array($cntRaw ?? null)) {
        $totals = $cntRaw;
    } else {
        $currencySymbol = $settings[SC::CR_SB] ?? '';
        $position       = $settings[SC::CR_SB_P] ?? '';
        $amount         = '—';
        $display = match ($position) {
            'pre' => $currencySymbol . $amount,
            'pos' => $amount . ($currencySymbol ? ' ' . $currencySymbol : ''),
            default => $amount,
        };
        $totals = [
            'total'       => $display,
            'this_month'  => $display,
            'this_week'   => $display,
            'last_30days' => $display,
        ];
    }

    $dealsList = (isset($deals) && (is_array($deals) || $deals instanceof \Illuminate\Support\Collection))
        ? $deals
        : [];

    $isPriceFormatAvailable = method_exists($user, 'priceFormat');
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Deals') }} @if(!empty($pipeline?->name)) - {{ $pipeline->name }} @endif
@endsection

@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@endpush

@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script defer>
        (() => {
            const ERR_FB = '# ERROR';
            const FL_CLIENT = 'data-client-localized';
            const FL_GUARD  = 'data-guard-msg';
            const LANG_KEY  = 'erp-np-lang';
            let errorMessage = '';

            const getMsg = (key, el) => {
                let msg = ERR_FB;
                try {
                    if (el.getAttribute(FL_CLIENT) === 'true') {
                        msg = el.getAttribute(FL_GUARD) || msg;
                    } else {
                        let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                          .toLowerCase().replace(/_/g,'-');
                        lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                        msg = (window.translations?.[lang]?.[key])
                          ?? el.getAttribute(FL_GUARD)
                          ?? window.translations?.['en']?.[key]
                          ?? msg;
                        if (msg !== ERR_FB) {
                            el.setAttribute(FL_GUARD, msg);
                            el.setAttribute(FL_CLIENT, 'true');
                        }
                    }
                } catch {}
                return msg;
            };

            const ensureToastContainer = () => {
                let c = document.getElementById('toast-container');
                if (!c) {
                    c = document.createElement('div');
                    c.id = 'toast-container';
                    document.body.appendChild(c);
                }
                return c;
            };

            const showError = message => {
                try {
                    const c = ensureToastContainer();
                    const hasBootstrap = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                    if (hasBootstrap) {
                        const t = document.createElement('div');
                        t.className = 'toast';
                        t.setAttribute('role','alert');
                        t.setAttribute('aria-live','assertive');
                        t.setAttribute('aria-atomic','true');
                        const b = document.createElement('div');
                        b.className = 'toast-body';
                        b.textContent = message;
                        t.appendChild(b);
                        c.appendChild(t);
                        bootstrap.Toast.getOrCreateInstance(t).show();
                    } else {
                        alert(message);
                    }
                } catch {
                    alert(message);
                }
            };

            const guardedClick = (btn) => {
                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                btn.setAttribute('data-listener-active', 'true');
                btn.addEventListener('click', e => {
                    try {
                        const url = (btn.getAttribute('data-url') || '#').trim();
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                        const hasBootstrap = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                        ensureToastContainer();
                        if (hasBootstrap) {
                            const toast = document.createElement('div');
                            toast.className = 'toast';
                            toast.setAttribute('role','alert');
                            toast.setAttribute('aria-live','assertive');
                            toast.setAttribute('aria-atomic','true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toast.appendChild(body);
                            document.getElementById('toast-container').appendChild(toast);
                            bootstrap.Toast.getOrCreateInstance(toast).show();
                        } else {
                            alert(msg);
                        }
                        btn.setAttribute('data-failed-route', 'true');
                    } catch {}
                });
            };

            document.addEventListener('DOMContentLoaded', () => {
                try {
                    document.querySelectorAll('[data-url][data-guard-msg]').forEach(guardedClick);
                } catch {}
            });

            const onUp = () => {
                if (errorMessage) {
                    showError(errorMessage);
                    errorMessage = '';
                }
            };
            document.addEventListener('pointerup', onUp);
            new MutationObserver((m, obs) => {
                m.forEach(mut => Array.from(mut.removedNodes).forEach(n => {
                    if (n === document.documentElement) {
                        document.removeEventListener('pointerup', onUp);
                        obs.disconnect();
                    }
                }));
            }).observe(document.body,{ childList:true, subtree:true });
        })();
    </script>
    <script defer src="{{ asset('assets/js/routes/deals/list.js') }}"></script>
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Deal') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $kanbanUrl = $hasKanban ? route($kanbanName) : '#';
            $createUrl = $hasCreate ? route($createName) : '#';
        @endphp
        <a
            id="deals-kanban-btn"
            href="{{ $kanbanUrl }}"
            data-url="{{ $kanbanUrl }}"
            data-guard-msg="{{ $kanbanGuard }}"
            data-bs-toggle="tooltip"
            title="{{ __('Kanban View') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="ti ti-layout-grid"></i>
        </a>

        <a
            id="deals-create-btn"
            href="{{ $createUrl }}"
            data-url="{{ $createUrl }}"
            data-guard-msg="{{ $createGuard }}"
            data-size="lg"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="{{ __('Create New Deal') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    @if(!empty($pipeline))
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CS3 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD }}-body">
                        <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                            <div class="{{ VC::C_AT }} {{ VC::MB3 }} {{ VC::MB0 }}">
                                <small class="{{ VC::TXT_MT }}">{{ __('Total Deals') }}</small>
                                <h4 class="{{ VC::MB0 }}">{{ $totals['total'] ?? '—' }}</h4>
                            </div>
                            <div class="{{ VC::C_AT }}">
                                <div class="theme-avatar bg-info">
                                    <i class="ti ti-layers-difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CS3 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD }}-body">
                        <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                            <div class="{{ VC::C_AT }} {{ VC::MB3 }} {{ VC::MB0 }}">
                                <small class="{{ VC::TXT_MT }}">{{ __('This Month Total Deals') }}</small>
                                <h4 class="{{ VC::MB0 }}">{{ $totals['this_month'] ?? '—' }}</h4>
                            </div>
                            <div class="{{ VC::C_AT }}">
                                <div class="theme-avatar bg-primary">
                                    <i class="ti ti-layers-difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CS3 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD }}-body">
                        <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                            <div class="{{ VC::C_AT }} {{ VC::MB3 }} {{ VC::MB0 }}">
                                <small class="{{ VC::TXT_MT }}">{{ __('This Week Total Deals') }}</small>
                                <h4 class="{{ VC::MB0 }}">{{ $totals['this_week'] ?? '—' }}</h4>
                            </div>
                            <div class="{{ VC::C_AT }}">
                                <div class="theme-avatar bg-warning">
                                    <i class="ti ti-layers-difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CS3 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD }}-body">
                        <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                            <div class="{{ VC::C_AT }} {{ VC::MB3 }} {{ VC::MB0 }}">
                                <small class="{{ VC::TXT_MT }}">{{ __('Last 30 Days Total Deals') }}</small>
                                <h4 class="{{ VC::MB0 }}">{{ $totals['last_30days'] ?? '—' }}</h4>
                            </div>
                            <div class="{{ VC::C_AT }}">
                                <div class="theme-avatar bg-danger">
                                    <i class="ti ti-layers-difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="col-xl-12">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD }}-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Stage') }}</th>
                                    <th>{{ __('Tasks') }}</th>
                                    <th>{{ __('Users') }}</th>
                                    <th width="300">{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if( (is_countable($dealsList) ? count($dealsList) : 0) > 0 )
                                    @foreach ($dealsList as $deal)
                                        @php
                                            $dealId      = $deal->id ?? null;
                                            $dealName    = $deal->name ?? __('No deal name available');
                                            $priceRaw    = isset($deal->price) && is_numeric($deal->price) ? (float)$deal->price : null;
                                            $stageName   = $deal->stage->name ?? __('—');
                                            $tasksCount  = is_countable($deal->tasks ?? []) ? count($deal->tasks) : 0;
                                            $doneCount   = is_countable($deal->complete_tasks ?? []) ? count($deal->complete_tasks) : 0;

                                            $viewUrl     = ($hasShow && !empty($deal->is_active) && $dealId) ? route($showName, $dealId) : '#';
                                            $editUrl     = ($hasEdit && $dealId) ? route($editName, $dealId) : '#';
                                            $deleteUrl   = ($hasDelete && $dealId) ? route($destroyName, $dealId) : '#';
                                        @endphp
                                        <tr>
                                            <td>{{ $dealName }}</td>
                                            <td>
                                                {{ $priceRaw !== null ? ($isPriceFormatAvailable ? ($user?->priceFormat($priceRaw)) : $priceRaw) : '—' }}
                                            </td>
                                            <td>{{ $stageName }}</td>
                                            <td>{{ $tasksCount }}/{{ $doneCount }}</td>
                                            <td>
                                                @php $dealUsers = $deal->users ?? []; @endphp
                                                @if(Utility::isFilled($dealUsers))
                                                    @foreach($dealUsers as $assignee)
                                                        @php
                                                            $avatar = !empty($assignee->avatar)
                                                                ? asset('storage/uploads/avatar/'.$assignee->avatar)
                                                                : asset('storage/uploads/avatar/avatar.png');
                                                            $assigneeName = $assignee->name ?? '';
                                                        @endphp
                                                        <a href="#" class="btn btn-sm p-0 rounded-circle" tabindex="-1" aria-label="{{ $assigneeName }}">
                                                            <img alt="avatar" data-bs-toggle="tooltip" title="{{ $assigneeName }}"
                                                                 src="{{ $avatar }}" class="rounded-circle" width="25" height="25">
                                                        </a>
                                                    @endforeach
                                                @else
                                                    <span class="{{ VC::TXT_MT }}">{{ __('No deal users available') }}</span>
                                                @endif
                                            </td>

                                            @if(($user?->{UsersConstants::COL_TP} ?? null) !== PermissionsConstants::CL)
                                                <td class="Action">
                                                    <span class="{{ VC::DFL_AIC }}">
                                                        @can('view deal')
                                                            @if(!empty($deal->is_active))
                                                                <div class="action-btn bg-warning ms-2">
                                                                    <a
                                                                        id="deal-view-btn-{{ $dealId }}"
                                                                        href="{{ $viewUrl }}"
                                                                        data-url="{{ $viewUrl }}"
                                                                        data-guard-msg="{{ $showGuard }}"
                                                                        class="{{ VC::BT_SM_FL_CT }}"
                                                                        data-size="xl"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('View') }}"
                                                                        data-title="{{ __('Lead Detail') }}"
                                                                    >
                                                                        <i class="ti ti-eye text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @endcan

                                                        @can('edit deal')
                                                            <div class="action-btn bg-info ms-2">
                                                                <a
                                                                    id="deal-edit-btn-{{ $dealId }}"
                                                                    href="{{ $editUrl }}"
                                                                    data-url="{{ $editUrl }}"
                                                                    data-guard-msg="{{ $editGuard }}"
                                                                    class="{{ VC::BT_SM_FL_CT }}"
                                                                    data-ajax-popup="true"
                                                                    data-size="xl"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Edit') }}"
                                                                    data-title="{{ __('Lead Edit') }}"
                                                                >
                                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                                </a>
                                                            </div>
                                                        @endcan

                                                        @can('delete deal')
                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open([
                                                                    'route'  => [$destroyName, $dealId],
                                                                    'method' => 'DELETE',
                                                                    'id'     => 'delete-form-' . $dealId
                                                                ]) !!}
                                                                    <a
                                                                        id="deal-delete-btn-{{ $dealId }}"
                                                                        href="{{ $deleteUrl }}"
                                                                        data-url="{{ $deleteUrl }}"
                                                                        data-guard-msg="{{ $deleteGuard }}"
                                                                        class="{{ VC::BT_SM_FL_CT }} bs-pass-para"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}"
                                                                    >
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>

                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    ['deal-view-btn-{{ $dealId }}','deal-edit-btn-{{ $dealId }}','deal-delete-btn-{{ $dealId }}']
                                                        .map(id => document.getElementById(id))
                                                        .filter(Boolean)
                                                        .forEach(btn => {
                                                            if (btn.getAttribute('data-listener-active') === 'true') return;
                                                            btn.setAttribute('data-listener-active','true');
                                                            btn.addEventListener('click', e => {
                                                                try {
                                                                    const url = (btn.getAttribute('data-url') || '#').trim();
                                                                    if (url !== '#') return;
                                                                    e.preventDefault();
                                                                    const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                    const hasBootstrap = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                    let c = document.getElementById('toast-container');
                                                                    if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                    if (hasBootstrap) {
                                                                        const toast = document.createElement('div');
                                                                        toast.className = 'toast';
                                                                        toast.setAttribute('role','alert');
                                                                        toast.setAttribute('aria-live','assertive');
                                                                        toast.setAttribute('aria-atomic','true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toast.appendChild(body);
                                                                        c.appendChild(toast);
                                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                    } else { alert(msg); }
                                                                    btn.setAttribute('data-failed-route','true');
                                                                } catch {}
                                                            });
                                                        });
                                                })();
                                            </script>
                                        @endpush
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="{{ VC::TXCT }} {{ VC::TXT_MT }}">{{ __('No data available in table') }}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="{{ VC::P4 }} {{ VC::TXCT }} {{ VC::TXT_MT }}">{{ __('No pipeline available') }}</div>
    @endif
@endsection
