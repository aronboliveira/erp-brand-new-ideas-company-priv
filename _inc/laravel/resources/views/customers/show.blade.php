@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Proposal,Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Customer-Detail')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        $indexRoute = Route::has(ViewsConstants::CST . '.index')
            ? route(ViewsConstants::CST . '.index')
            : '#';
        $indexGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CST,
            'customers_index_route_unavailable'
        ) ?? 'Customer index route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <li class="breadcrumb-item">
        <a
            id="customer-index-breadcrumb"
            href="{{ $indexRoute }}"
            data-url="{{ $indexRoute }}"
            data-guard-msg="{{ $indexGuardMsg }}"
        >
            {{ __('Customer') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCRP_PG)
        <script defer>
            (() => {
                const el = document.getElementById('customer-index-breadcrumb');
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener('click', e => {
                    try {
                        const url = el.getAttribute('data-url') ?? '#';
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bs) {
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
                            bootstrap.Toast.getOrCreateInstance(toast).show();
                        } else {
                            alert(msg);
                        }
                        el.setAttribute('data-failed-route','true');
                    } catch {}
                });
            })();
        </script>
    @endpush
    <li class="breadcrumb-item">{{$customer['name']}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar: {
                url_copy_success: 'تم نسخ الرابط إلى الحافظة.',
                url_copy_failed: 'فشل نسخ الرابط.'
            },
            da: {
                url_copy_success: 'URL kopieret til udklipsholder.',
                url_copy_failed: 'Kunne ikke kopiere URL.'
            },
            de: {
                url_copy_success: 'URL in die Zwischenablage kopiert.',
                url_copy_failed: 'Konnte URL nicht kopieren.'
            },
            en: {
                url_copy_success: 'URL copied to clipboard.',
                url_copy_failed: 'Failed to copy URL.'
            },
            es: {
                url_copy_success: 'URL copiada al portapapeles.',
                url_copy_failed: 'Error al copiar la URL.'
            },
            fr: {
                url_copy_success: 'URL copiée dans le presse-papier.',
                url_copy_failed: 'Échec de la copie de l’URL.'
            },
            he: {
                url_copy_success: 'הכתובת הועתקה ללוח.',
                url_copy_failed: 'העתקת הכתובת נכשלה.'
            },
            it: {
                url_copy_success: 'URL copiata negli appunti.',
                url_copy_failed: 'Impossibile copiare l’URL.'
            },
            ja: {
                url_copy_success: 'URL をクリップボードにコピーしました。',
                url_copy_failed: 'URL のコピーに失敗しました。'
            },
            nl: {
                url_copy_success: 'URL gekopieerd naar klembord.',
                url_copy_failed: 'Kon URL niet kopiëren.'
            },
            pl: {
                url_copy_success: 'URL skopiowany do schowka.',
                url_copy_failed: 'Nie udało się skopiować URL.'
            },
            pt: {
                url_copy_success: 'URL copiada para a área de transferência.',
                url_copy_failed: 'Falha ao copiar a URL.'
            },
            'pt-br': {
                url_copy_success: 'URL copiada para a área de transferência.',
                url_copy_failed: 'Falha ao copiar a URL.'
            },
            ru: {
                url_copy_success: 'URL скопирован в буфер обмена.',
                url_copy_failed: 'Не удалось скопировать URL.'
            },
            tr: {
                url_copy_success: 'URL panoya kopyalandı.',
                url_copy_failed: 'URL kopyalanamadı.'
            },
            zh: {
                url_copy_success: 'URL 已复制到剪贴板。',
                url_copy_failed: '无法复制 URL。'
            }
        };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
     
          })();
    </script>
    <script defer>
        (() => {
        const ERR_FB = '# ERROR';
        const CLIENT_FLAG = 'data-client-localized';
        const GUARD_MSG = 'data-guard-msg';
        const LANG_KEY = 'erp-np-lang';
        
        function getLocalizedMessage(key, el) {
            let msg = ERR_FB;
            if (el.getAttribute(CLIENT_FLAG) === 'true') {
            msg = el.getAttribute(GUARD_MSG) || msg;
            } else {
            let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0,2);
            msg = window.translations?.[lang]?.[key] ||
                    el.getAttribute(GUARD_MSG) ||
                    window.translations?.['en']?.[key] ||
                    msg;
            if (msg !== ERR_FB) {
                el.setAttribute(GUARD_MSG, msg);
                el.setAttribute(CLIENT_FLAG, 'true');
            }
            }
            return msg;
        }
        
        function showToast(message, isError = false) {
            try {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }
            const hasBs = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if (hasBs) {
                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.setAttribute('role','alert');
                toast.setAttribute('aria-live','assertive');
                toast.setAttribute('aria-atomic','true');
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
        }
        
        let errorMessage = '';
        const onPointerUp = () => {
            if (errorMessage) {
            showToast(errorMessage, true);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onPointerUp);
        new MutationObserver((m, obs) => {
            m.forEach(mut => Array.from(mut.removedNodes).forEach(node => {
            if (node === document.documentElement) {
                document.removeEventListener('pointerup', onPointerUp);
                obs.disconnect();
            }
            }));
        }).observe(document.body, { childList:true, subtree:true });
        
        window.copyToClipboard = (element) => {
            try {
            const text = element?.id ?? '';
            if (!navigator.clipboard) throw new Error('url_copy_failed');
            navigator.clipboard.writeText(text)
                .then(() => {
                const msg = getLocalizedMessage('url_copy_success', element);
                showToast(msg);
                })
                .catch(() => {
                throw new Error('url_copy_failed');
                });
            } catch (e) {
            errorMessage = getLocalizedMessage(e.message, element || document.body);
            }
        };
        })();
    </script>    
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create invoice')
            @php
                $invoiceRoute = Route::has(ViewsConstants::INV . '.create')
                    ? route(ViewsConstants::INV . '.create', $customer->id)
                    : '#';
                $invoiceGuardMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::INV,
                    'invoice_create_route_unavailable'
                ) ?? 'Invoice create route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                id="invoice-create-btn-{{ $customer->id }}"
                href="{{ $invoiceRoute }}"
                data-url="{{ $invoiceRoute }}"
                data-guard-msg="{{ $invoiceGuardMsg }}"
                class="{{ VC::BT_SM_PM }}"
            >
                {{ __('Create Invoice') }}
            </a>
            @push(StacksConstants::ADM_SCRP_PG)
                <script defer>
                    (() => {
                        const btn = document.getElementById('invoice-create-btn-{{ $customer->id }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active', 'true');
                        btn.addEventListener('click', e => {
                            try {
                                const url = btn.getAttribute('data-url') ?? '#';
                                if (url !== '#') return;
                                e.preventDefault();
                                const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bs) {
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
                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                } else {
                                    alert(msg);
                                }
                                btn.setAttribute('data-failed-route','true');
                            } catch {}
                        });
                    })();
                </script>
            @endpush
        @endcan
        @can('create proposal')
            @php
                $createProposalRoute = Route::has(ViewsConstants::PPS . '.create')
                    ? route(ViewsConstants::PPS . '.create', $customer->id)
                    : '#';
                $createProposalGuardMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::PPS,
                    'create_proposal_route_unavailable'
                ) ?? 'Create proposal route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                id="create-proposal-btn-{{ $customer->id }}"
                href="{{ $createProposalRoute }}"
                data-url="{{ $createProposalRoute }}"
                data-guard-msg="{{ $createProposalGuardMsg }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create Proposal') }}"
            >
                {{ __('Create Proposal') }}
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const btn = document.getElementById('create-proposal-btn-{{ $customer->id }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active', 'true');
                        btn.addEventListener('click', e => {
                            try {
                                const url = btn.getAttribute('data-url') || '#';
                                if (url !== '#') return;
                                e.preventDefault();
                                const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bs) {
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
                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                } else {
                                    alert(msg);
                                }
                                btn.setAttribute('data-failed-route', 'true');
                            } catch (error) {}
                        });
                    })();
                </script>
            @endpush
        @endcan
        @can('edit customer')
        @php
            $editRoute = Route::has(ViewsConstants::CST . '.edit')
                ? route(ViewsConstants::CST . '.edit', $customer['id'])
                : '#';
            $editGuardMsg = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::CST,
                'customers_edit_route_unavailable'
            ) ?? 'Customer edit route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a
            id="customer-edit-btn-{{ $customer['id'] }}"
            href="{{ $editRoute }}"
            data-url="{{ $editRoute }}"
            data-guard-msg="{{ $editGuardMsg }}"
            data-size="lg"
            data-ajax-popup="true"
            class="{{ VC::BT_SM_PM }}"
            data-bs-toggle="tooltip"
            title="{{ __('Edit Customer') }}"
            data-title="{{ __('Edit Customer') }}"
        >
            <i class="{{ VC::TI_PC }}"></i>
        </a>
        @push(StacksConstants::ADM_SCRP_PG)
            <script defer>
                (() => {
                    const btn = document.getElementById('customer-edit-btn-{{ $customer['id'] }}');
                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                    btn.setAttribute('data-listener-active', 'true');
                    btn.addEventListener('click', e => {
                        try {
                            const url = btn.getAttribute('data-url') ?? '#';
                            if (url !== '#') return;
                            e.preventDefault();
                            const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                            const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                            let container = document.getElementById('toast-container');
                            if (!container) {
                                container = document.createElement('div');
                                container.id = 'toast-container';
                                document.body.appendChild(container);
                            }
                            if (bs) {
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
                                bootstrap.Toast.getOrCreateInstance(toast).show();
                            } else {
                                alert(msg);
                            }
                            btn.setAttribute('data-failed-route','true');
                        } catch {}
                    });
                })();
            </script>
        @endpush
        @endcan
        @can('delete customer')
            @php
                $deleteRoute = Route::has(ViewsConstants::CST . '.destroy')
                    ? route(ViewsConstants::CST . '.destroy', $customer['id'])
                    : (Route::has(Str::kebab(ViewsConstants::CST . '.destroy'))
                        ? route(Str::kebab(ViewsConstants::CST . '.destroy', $customer['id']))
                        : '#');
                $deleteGuardMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::CST,
                    'customers_destroy_route_unavailable'
                ) ?? 'Delete customer route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            {!! Form::open([
                'route'    => $deleteRoute,
                'method' => 'DELETE',
                'id'     => 'delete-form-' . $customer['id'],
                'class'  => 'delete-form-btn'
            ]) !!}
                <a
                    id="delete-customer-btn-{{ $customer['id'] }}"
                    href="{{ $deleteRoute }}"
                    data-url="{{ $deleteRoute }}"
                    data-guard-msg="{{ $deleteGuardMsg }}"
                    class="{{ VC::BT_SM_CT_PR }}"
                    data-bs-toggle="tooltip"
                    title="{{ __('Delete Customer') }}"
                    data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                    data-confirm-yes="document.getElementById('delete-form-{{ $customer['id'] }}').submit();"
                >
                    <i class="{{ VC::TI_TRS_WT }}"></i>
                </a>
            {!! Form::close() !!}
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const btn = document.getElementById('delete-customer-btn-{{ $customer['id'] }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active', 'true');
                        btn.addEventListener('click', e => {
                            try {
                                const url = btn.getAttribute('data-url') || '#';
                                if (url !== '#') return;
                                e.preventDefault();
                                const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bs) {
                                    const toast = document.createElement('div');
                                    toast.className = 'toast';
                                    toast.setAttribute('role', 'alert');
                                    toast.setAttribute('aria-live', 'assertive');
                                    toast.setAttribute('aria-atomic', 'true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toast.appendChild(body);
                                    container.appendChild(toast);
                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                } else {
                                    alert(msg);
                                }
                                btn.setAttribute('data-failed-route', 'true');
                            } catch (error) {}
                        });
                    })();
                </script>
            @endpush
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        @foreach($customerInfoSections as $section)
            <div class="{{ VC::CL4 }} {{ VC::MB4 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <h5 class="card-title">{{ __($section['title']) }}</h5>
                        @foreach($section['fields'] as $field)
                            @if($field)
                                <p class="{{ VC::MB0 }}">{{ $field }}</p>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }} pb-0">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Company Info') }}</h5>
                    <div class="{{ VC::RW }}">
                        @foreach($companyInfoStats as $stat)
                            <div class="{{ VC::CL3 }} {{ VC::CS6 }}">
                                <div class="{{ VC::P4 }}">
                                    <p class="{{ VC::MB0 }}">{{ __($stat['label']) }}</p>
                                    <h6 class="report-text {{ VC::MB3 }}">{{ $stat['value'] }}</h6>
                                    @if($stat['secondLabel'])
                                        <p class="{{ VC::MB0 }}">{{ __($stat['secondLabel']) }}</p>
                                        <h6 class="report-text {{ VC::MB0 }}">{{ $stat['secondValue'] }}</h6>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <h5 class="{{ VC::MB4 }}">{{ __('Proposal') }}</h5>
                    <div class="table-responsive">
                        <table class="{{ VC::TB }}">
                            <thead>
                                <tr>
                                    <th>{{ __('Proposal') }}</th>
                                    <th>{{ __('Issue Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                        <th width="10%">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->customerProposal($customer->id) as $proposal)
                                    <tr>
                                        <td>
                                            <a href="{{ route(ViewsConstants::PPS . '.show', Crypt::encrypt($proposal->id)) }}"
                                               class="{{ VC::BT_OUTPM }}">
                                                {{ $user?->proposalNumberFormat($proposal->proposal_id) }}
                                            </a>
                                        </td>
                                        <td>{{ $user?->dateFormat($proposal->issue_date) }}</td>
                                        <td>{{ $user?->priceFormat($proposal->getTotal()) }}</td>
                                        @php
                                            $statusBadgeClasses = match(true) {
                                                $proposal->status === 0 => 'bg-primary',
                                                $proposal->status === 1 => 'bg-warning',
                                                $proposal->status === 2 => 'bg-danger',
                                                $proposal->status === 3 => 'bg-info',
                                                $proposal->status === 4 => 'bg-primary',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <td>
                                            @if(!empty($proposal->status) && is_numeric($proposal->status) && $proposal->status >= 0 && $proposal->status < 4)
                                                <span class="badge {{ $statusBadgeClasses }} p-2 px-3 rounded">
                                                    {{ __(Proposal::$statuses[$proposal->status]) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary p-2 px-3 rounded">
                                                    {{ __('Unknown status') }}
                                                </span>
                                            @endif
                                        </td>
                                        @if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                            <td class="action">
                                                <span>
                                                    @include('partials.proposal_actions', compact('proposal'))
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <h5 class="{{ VC::MB4 }}">{{ __('Invoice') }}</h5>
                    <div class="table-responsive">
                        <table class="{{ VC::TB }}">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Issue Date') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Due Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if(Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                        <th width="10%">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->customerInvoice($customer->id) as $invoice)
                                    <tr>
                                        <td>
                                            @php
                                                $showRoute = Route::has(ViewsConstants::INV . '.show')
                                                    ? route(ViewsConstants::INV . '.show', Crypt::encrypt($invoice->id))
                                                    : '#';
                                                $showGuardMsg = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::INV,
                                                    'invoice_show_route_unavailable'
                                                ) ?? 'Invoice show route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <a
                                                id="invoice-show-btn-{{ $invoice->id }}"
                                                href="{{ $showRoute }}"
                                                data-url="{{ $showRoute }}"
                                                data-guard-msg="{{ $showGuardMsg }}"
                                                class="{{ VC::BT_OUTPM }}"
                                            >
                                                {{ $user?->invoiceNumberFormat($invoice->invoice_id) }}
                                            </a>
                                            @push(StacksConstants::ADM_SCRP_PG)
                                            <script defer>
                                                (() => {
                                                document.querySelectorAll('[id^="invoice-show-btn-"]').forEach(btn => {
                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                    btn.setAttribute('data-listener-active', 'true');
                                                    btn.addEventListener('click', e => {
                                                    try {
                                                        const url = btn.getAttribute('data-url') ?? '#';
                                                        if (url !== '#') return;
                                                        e.preventDefault();
                                                        const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                        const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                        let container = document.getElementById('toast-container');
                                                        if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                        }
                                                        if (bs) {
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
                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                        } else {
                                                        alert(msg);
                                                        }
                                                        btn.setAttribute('data-failed-route','true');
                                                    } catch {}
                                                    });
                                                });
                                                })();
                                            </script>
                                            @endpush
                                        </td>
                                        <td>{{ $user?->dateFormat($invoice->issue_date) }}</td>
                                        <td>
                                            @if($invoice->due_date < date('Y-m-d'))
                                                <span class="text-danger">{{ $user?->dateFormat($invoice->due_date) }}</span>
                                            @else
                                                {{ $user?->dateFormat($invoice->due_date) }}
                                            @endif
                                        </td>
                                        <td>{{ $user?->priceFormat($invoice->getDue()) }}</td>
                                        <td>
                                            <span class="badge {{ $statusBadgeClasses[$invoice->status] ?? 'bg-secondary' }} p-2 px-3 rounded">
                                                {{ __(\App\Models\Invoice::$statuses[$invoice->status]) }}
                                            </span>
                                        </td>
                                        @if(Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                            <td class="action">
                                                <span>
                                                    @include('partials.invoice_actions', compact('invoice'))
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
