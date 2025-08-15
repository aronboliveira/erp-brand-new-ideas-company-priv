@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Chart of Accounts') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Chart of Account') }}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script>
        window.translations = {
        ar: {
            char_of_account_subtype_unavailable: 'فشل جلب الأنواع الفرعية للحساب.',
            date_callback_failed: 'فشل نسخ قيم التاريخ.'
        },
        da: {
            char_of_account_subtype_unavailable: 'Kunne ikke hente underkategorier.',
            date_callback_failed: 'Kunne ikke kopiere datoværdier.'
        },
        de: {
            char_of_account_subtype_unavailable: 'Unterkategorien konnten nicht geladen werden.',
            date_callback_failed: 'Konnte Datumswerte nicht kopieren.'
        },
        en: {
            char_of_account_subtype_unavailable: 'Failed to fetch account sub-types.',
            date_callback_failed: 'Failed to copy date values.'
        },
        es: {
            char_of_account_subtype_unavailable: 'Error al obtener subtipos de cuenta.',
            date_callback_failed: 'Error al copiar los valores de fecha.'
        },
        fr: {
            char_of_account_subtype_unavailable: 'Échec de la récupération des sous-types de compte.',
            date_callback_failed: 'Échec de la copie des valeurs de date.'
        },
        he: {
            char_of_account_subtype_unavailable: 'נכשל קבלת תת־סוגי חשבון.',
            date_callback_failed: 'העתקת ערכי התאריך נכשלה.'
        },
        it: {
            char_of_account_subtype_unavailable: 'Recupero dei sottotipi di conto non riuscito.',
            date_callback_failed: 'Impossibile copiare i valori della data.'
        },
        ja: {
            char_of_account_subtype_unavailable: '勘定科目のサブタイプの取得に失敗しました。',
            date_callback_failed: '日付値のコピーに失敗しました。'
        },
        nl: {
            char_of_account_subtype_unavailable: 'Kon subtypes van rekening niet ophalen.',
            date_callback_failed: 'Kon datumwaarden niet kopiëren.'
        },
        pl: {
            char_of_account_subtype_unavailable: 'Nie udało się pobrać podtypów konta.',
            date_callback_failed: 'Nie udało się skopiować wartości daty.'
        },
        pt: {
            char_of_account_subtype_unavailable: 'Falha ao obter subtipos de conta.',
            date_callback_failed: 'Falha ao copiar os valores de data.'
        },
        'pt-br': {
            char_of_account_subtype_unavailable: 'Falha ao obter subtipos de conta.',
            date_callback_failed: 'Falha ao copiar os valores de data.'
        },
        ru: {
            char_of_account_subtype_unavailable: 'Не удалось получить подтипы счета.',
            date_callback_failed: 'Не удалось скопировать значения даты.'
        },
        tr: {
            char_of_account_subtype_unavailable: 'Hesap alt türleri alınamadı.',
            date_callback_failed: 'Tarih değerleri kopyalanamadı.'
        },
        zh: {
            char_of_account_subtype_unavailable: '获取账户子类型失败。',
            date_callback_failed: '复制日期值失败。'
        }
        };
    </script>
    <script defer>
        (() => {
        const errFb = '# ERROR';
        const clientFlag = 'data-client-localized';
        const guardMsgKey = 'data-guard-msg';
        const langKey = 'erp-np-lang';
        let errorMessage = '';
        
        const getLocalizedMessage = (key, el) => {
            let msg = errFb;
            if (el.getAttribute(clientFlag) === 'true') {
            msg = el.getAttribute(guardMsgKey) || msg;
            } else {
            let lang = (sessionStorage.getItem(langKey) || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg = translations?.[lang]?.[key]
                || el.getAttribute(guardMsgKey)
                || translations?.['en']?.[key]
                || msg;
            if (msg !== errFb) {
                el.setAttribute(guardMsgKey, msg);
                el.setAttribute(clientFlag, 'true');
            }
            }
            return msg;
        };
        
        const showError = message => {
            try {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }
            const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if (bs) {
                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                const body = document.createElement('div');
                body.className = 'toast-body';
                body.textContent = message;
                toast.appendChild(body);
                container.appendChild(toast);
                bootstrap.Toast.getOrCreateInstance(toast).show();
            } else {
                alert(message);
            }
            } catch {
            alert(message);
            }
        };
        
        const onErrorPointerUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onErrorPointerUp);
        new MutationObserver((muts, obs) => {
            muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
            if (n === document.documentElement) {
                document.removeEventListener('pointerup', onErrorPointerUp);
                obs.disconnect();
            }
            }));
        }).observe(document.body, { childList: true, subtree: true });
        
        document.addEventListener('DOMContentLoaded', () => {
            const typeEl = document.getElementById('type');
            if (typeEl && typeEl.dataset.listenerAttached !== 'true') {
            typeEl.dataset.listenerAttached = 'true';
            const onTypeChange = () => {
                try {
                const url = '{{ route("charofAccount.subType") }}';
                if (!url) throw new Error('char_of_account_subtype_unavailable');
                const val = typeEl.value ?? '';
                $.ajax({
                    url,
                    type: 'POST',
                    dataType: 'json',
                    data: { type: val, _token: '{{ csrf_token() }}' }
                })
                .done(data => {
                    const sub = document.getElementById('sub_type');
                    if (!sub) return;
                    sub.innerHTML = '';
                    Object.entries(data).forEach(([k,v]) => {
                    const o = document.createElement('option');
                    o.value = k;
                    o.textContent = v;
                    sub.appendChild(o);
                    });
                })
                .fail(() => {
                    throw new Error('char_of_account_subtype_unavailable');
                });
                } catch (e) {
                errorMessage = getLocalizedMessage(e.message, typeEl);
                }
            };
            typeEl.addEventListener('change', onTypeChange);
            new MutationObserver((ms, obs) => {
                ms.forEach(m => Array.from(m.removedNodes).forEach(n => {
                if (n === typeEl) {
                    typeEl.removeEventListener('change', onTypeChange);
                    obs.disconnect();
                }
                }));
            }).observe(document.body, { childList: true, subtree: true });
            }
        
            try {
            const copyDates = () => {
                const start = document.querySelector('.startDate')?.value ?? '';
                const end = document.querySelector('.endDate')?.value ?? '';
                document.querySelectorAll('.start_date').forEach(el => el.value = start);
                document.querySelectorAll('.end_date').forEach(el => el.value = end);
            };
            copyDates();
            } catch {
            errorMessage = getLocalizedMessage('date_callback_failed', document.body);
            }
        });
        })();
    </script>
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can(PermissionsConstants::CR_COA)
            <a href="#" data-url="{{ route(ViewsConstants::COA.'.create') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
                data-size="lg" data-ajax-popup="true" data-title="{{ __('Create New Account') }}" class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };

    $lang               = Utility::fetchUserLang();

    // index route guard
    $indexName          = ViewsConstants::COA . '.index';
    $indexRoute         = Route::has($indexName)
        ? route($indexName)
        : (Route::has(Str::kebab($indexName))
            ? route(Str::kebab($indexName))
            : '#');
    $indexGuardMsg      = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::COA,
        'chart_of_account_index_route_unavailable'
    ) ?? 'Chart of Account index route is unavailable. Please contact technical support or your domain administrator.';
@endphp

<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card" id="show_filter">
                <div class="card-body">
                    {{ Collective\Html\FormFacade::open([
                        'route'          => $indexRoute,
                        'method'         => 'GET',
                        'id'             => 'report_bill_summary',
                        'data-url'       => $indexRoute,
                        'data-guard-msg' => $indexGuardMsg,
                    ]) }}
                    <div class="{{ VC::R_ALC_JCE }}">
                        <div class="col-xl-10">
                            <div class="{{ VC::RW }}">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box"></div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box"></div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Collective\Html\FormFacade::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                        {{ Collective\Html\FormFacade::date('start_date', $filter['startDateRange'], ['class' => VC::FM_CT]) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Collective\Html\FormFacade::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                        {{ Collective\Html\FormFacade::date('end_date', $filter['endDateRange'], ['class' => VC::FM_CT]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="{{ VC::DFL_JCB }}">
                                <a
                                    href="#"
                                    class="{{ VC::BT_SM_PM }}"
                                    id="applyFilter"
                                    data-listener-alias="apply-filter"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Apply') }}"
                                >
                                    <span class="btn-inner--icon">
                                        <i class="{{ VC::TI_SRC }}"></i>
                                    </span>
                                </a>
                                <a
                                    href="{{ $indexRoute }}"
                                    class="{{ VC::BT_SM_DG }}"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Reset') }}"
                                >
                                    <span class="btn-inner--icon">
                                        <i class="{{ VC::TI_TRS_OFF }}"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                    {{ Collective\Html\FormFacade::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @foreach ($chartAccounts as $type => $accounts)
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6>{{ $type }}</h6>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="10%">{{ __('Code') }}</th>
                                    <th width="30%">{{ __('Name') }}</th>
                                    <th width="20%">{{ __('Type') }}</th>
                                    <th width="20%">{{ __('Balance') }}</th>
                                    <th width="10%">{{ __('Status') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accounts as $account)
                                    @php
                                        // ledger route guard
                                        $ledgerName    = ViewsConstants::RPT . '.ledger';
                                        $ledgerRoute   = Route::has($ledgerName)
                                            ? route($ledgerName, $account->id) . '?account=' . $account->id
                                            : (Route::has(Str::kebab($ledgerName))
                                                ? route(Str::kebab($ledgerName), $account->id) . '?account=' . $account->id
                                                : '#');
                                        $ledgerGuardMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::RPT,
                                            'ledger_route_unavailable'
                                        ) ?? 'Transaction Summary route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <tr>
                                        <td>{{ $account->code }}</td>
                                        <td>
                                            <a
                                                href="#"
                                                class="{{ VC::BT_SM_CT }}"
                                                data-url="{{ $ledgerRoute }}"
                                                data-guard-msg="{{ $ledgerGuardMsg }}"
                                                data-listener-alias="ledger-link"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Transaction Summary') }}"
                                            >
                                                {{ $account->name }}
                                            </a>
                                        </td>
                                        <td>{{ $account->subType->name ?? '-' }}</td>
                                        <td>
                                            @php($totalBalance = Utility::getAccountBalance($account->id, $filter['startDateRange'], $filter['endDateRange']))
                                            {{ $totalBalance ? \Auth::user()->priceFormat($totalBalance) : '-' }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $account->is_enabled ? 'bg-primary' : 'bg-danger' }} p-2 px-3 rounded">
                                                {{ $account->is_enabled ? __('Enabled') : __('Disabled') }}
                                            </span>
                                        </td>
                                        <td class="Action">
                                            <div class="{{ VC::ACT_BTN_WRN }}">
                                                {{-- ledger icon --}}
                                                <a
                                                    href="#"
                                                    class="{{ VC::BT_SM_CT }}"
                                                    data-url="{{ $ledgerRoute }}"
                                                    data-guard-msg="{{ $ledgerGuardMsg }}"
                                                    data-listener-alias="ledger-link"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Transaction Summary') }}"
                                                >
                                                    <i class="ti ti-wave-sine text-white"></i>
                                                </a>
                                            </div>
                                            @can('edit chart of account')
                                                @php
                                                    $editName    = ViewsConstants::COA . '.edit';
                                                    $editRoute   = Route::has($editName)
                                                        ? route($editName, $account->id)
                                                        : (Route::has(Str::kebab($editName))
                                                            ? route(Str::kebab($editName), $account->id)
                                                            : '#');
                                                    $editGuardMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::COA,
                                                        'chart_of_account_edit_route_unavailable'
                                                    ) ?? 'Edit Account route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a
                                                        href="#"
                                                        class="{{ VC::BT_SM_CT }}"
                                                        data-url="{{ $editRoute }}"
                                                        data-guard-msg="{{ $editGuardMsg }}"
                                                        data-listener-alias="edit-account"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Account') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can(PermissionsConstants::DEL_COA)
                                                @php
                                                    $destroyName    = ViewsConstants::COA . '.destroy';
                                                    $destroyRoute   = Route::has($destroyName)
                                                        ? route($destroyName, $account->id)
                                                        : (Route::has(Str::kebab($destroyName))
                                                            ? route(Str::kebab($destroyName), $account->id)
                                                            : '#');
                                                    $destroyGuardMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::COA,
                                                        'chart_of_account_destroy_route_unavailable'
                                                    ) ?? 'Delete Account route is unavailable. Please contact technical support or your domain administrator.';
                                                    $deleteFormId   = 'delete-form-' . $account->id;
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method'         => 'DELETE',
                                                        'route'          => [ViewsConstants::COA . '.destroy', $account->id],
                                                        'id'             => $deleteFormId,
                                                        'data-url'       => $destroyRoute,
                                                        'data-guard-msg' => $destroyGuardMsg,
                                                    ]) !!}
                                                    <a
                                                        href="#"
                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                        data-listener-alias="delete-account"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                        data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                    >
                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                    </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const bindGuard = (el, event, urlAttr = 'data-url', msgAttr = 'data-guard-msg') => {
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener(event, e => {
                    try {
                        const url = el.getAttribute(urlAttr) ?? '#';
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg           = el.getAttribute(msgAttr) ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl      = document.createElement('div');
                            toastEl.className  = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body         = document.createElement('div');
                            body.className     = 'toast-body';
                            body.textContent   = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        el.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            };
            const filterForm = document.getElementById('report_bill_summary');
            bindGuard(filterForm, 'submit');
            const applyBtn = document.getElementById('applyFilter');
            bindGuard(applyBtn, 'click');
            document.querySelectorAll('[data-listener-alias="ledger-link"]').forEach(el => bindGuard(el, 'click'));
            document.querySelectorAll('[data-listener-alias="edit-account"]').forEach(el => bindGuard(el, 'click'));
            document.querySelectorAll('[data-listener-alias="delete-account"]').forEach(el => bindGuard(el, 'click'));
        })();
    </script>
@endpush
