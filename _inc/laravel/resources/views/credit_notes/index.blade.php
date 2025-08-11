@php
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth,Crypt,Route};
    use Illuminate\Support\Str;
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $createName     = ViewsConstants::INV . '.custom.credit.note';
    $createRoute    = Route::has($createName)
        ? route($createName)
        : '#';
    $createGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::INV,
        'custom_credit_note_route_unavailable'
    ) ?? 'Create custom credit note route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Credit Notes')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async>
        window.translations = {
        ar: {
            invoice_fetch_unavailable: 'فشل جلب بيانات الفاتورة.'
        },
        da: {
            invoice_fetch_unavailable: 'Kunne ikke hente fakturadata.'
        },
        de: {
            invoice_fetch_unavailable: 'Abruf der Rechnungsdaten fehlgeschlagen.'
        },
        en: {
            invoice_fetch_unavailable: 'Failed to fetch invoice data.'
        },
        es: {
            invoice_fetch_unavailable: 'Error al obtener los datos de la factura.'
        },
        fr: {
            invoice_fetch_unavailable: 'Échec de la récupération des données de la facture.'
        },
        he: {
            invoice_fetch_unavailable: 'הנתונים של החשבונית לא נטענו.'
        },
        it: {
            invoice_fetch_unavailable: 'Impossibile recuperare i dati della fattura.'
        },
        ja: {
            invoice_fetch_unavailable: '請求書データの取得に失敗しました。'
        },
        nl: {
            invoice_fetch_unavailable: 'Kon factuurgegevens niet ophalen.'
        },
        pl: {
            invoice_fetch_unavailable: 'Nie udało się pobrać danych faktury.'
        },
        pt: {
            invoice_fetch_unavailable: 'Falha ao obter dados da fatura.'
        },
        'pt-br': {
            invoice_fetch_unavailable: 'Falha ao obter dados da fatura.'
        },
        ru: {
            invoice_fetch_unavailable: 'Не удалось получить данные счета.'
        },
        tr: {
            invoice_fetch_unavailable: 'Fatura verileri alınamadı.'
        },
        zh: {
            invoice_fetch_unavailable: '获取发票数据失败。'
        }
        };
    </script>
    <script defer>
        (() => {
        const ERR_FB = '# ERROR';
        const CLIENT_FLAG = 'data-client-localized';
        const GUARD_MSG = 'data-guard-msg';
        const LANG_KEY = 'erp-np-lang';
        let errorMessage = '';
        
        const getMsg = (key, el) => {
            let msg = ERR_FB;
            if (el.getAttribute(CLIENT_FLAG) === 'true') {
            msg = el.getAttribute(GUARD_MSG) || msg;
            } else {
            let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0,2);
            msg = window.translations?.[lang]?.[key]
                || el.getAttribute(GUARD_MSG)
                || window.translations?.['en']?.[key]
                || msg;
            if (msg !== ERR_FB) {
                el.setAttribute(GUARD_MSG, msg);
                el.setAttribute(CLIENT_FLAG, 'true');
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
        };
        
        const onPointerUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onPointerUp);
        new MutationObserver((muts, obs) => {
            muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
            if (n === document.documentElement) {
                document.removeEventListener('pointerup', onPointerUp);
                obs.disconnect();
            }
            }));
        }).observe(document.body, { childList: true, subtree: true });
        
        document.addEventListener('DOMContentLoaded', () => {
            const invEl = document.getElementById('invoice');
            if (!invEl || invEl.dataset.listenerAttached === 'true') return;
            invEl.dataset.listenerAttached = 'true';
        
            const onInvoiceChange = () => {
            try {
                const id = invEl.value ?? '';
                const url = '{{ route(ViewsConstants::INV + ".get") }}';
                if (!url || url === '#') throw new Error('invoice_fetch_unavailable');
        
                $.ajax({
                url,
                type: 'GET',
                dataType: 'json',
                data: { id },
                })
                .done(data => {
                const amt = document.getElementById('amount');
                if (amt) amt.value = data;
                })
                .fail(() => {
                throw new Error('invoice_fetch_unavailable');
                });
            } catch (e) {
                errorMessage = getMsg(e.message, invEl);
            }
            };
        
            invEl.addEventListener('change', onInvoiceChange);
        
            new MutationObserver((muts, obs) => {
            muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
                if (n === invEl) {
                invEl.removeEventListener('change', onInvoiceChange);
                obs.disconnect();
                }
            }));
            }).observe(document.body, { childList: true, subtree: true });
        });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Credit Note') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create credit note')
            <a
                href="#"
                id="createCreditNoteBtn"
                data-url="{{ $createRoute }}"
                data-guard-msg="{{ $createGuardMsg }}"
                data-listener-alias="create-credit-note"
                data-ajax-popup="true"
                data-title="{{ __('Create New Credit Note') }}"
                data-size="lg"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ VC::BT_SM_PM }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style mt-2">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                    @if(!empty($invoice->creditNote))
                                        @foreach($invoice->creditNote as $creditNote)
                                            @php
                                                $editName     = ViewsConstants::INV . '.edit.credit.note';
                                                $editRoute    = Route::has($editName)
                                                    ? route($editName, [$creditNote->invoice, $creditNote->id])
                                                    : '#';
                                                $editGuardMsg = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::INV,
                                                    'edit_credit_note_route_unavailable'
                                                ) ?? 'Edit credit note route is unavailable. Please contact technical support or your domain administrator.';
                                                $editBtnId    = 'edit-cn-' . $creditNote->id;
                                                $delName      = ViewsConstants::INV . '.delete.credit.note';
                                                $delRoute     = Route::has($delName)
                                                    ? route($delName, [$creditNote->invoice, $creditNote->id])
                                                    : '#';
                                                $delGuardMsg  = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::INV,
                                                    'delete_credit_note_route_unavailable'
                                                ) ?? 'Delete credit note route is unavailable. Please contact technical support or your domain administrator.';
                                                $deleteFormId = 'delete-cn-' . $creditNote->id;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <a href="{{ route(ViewsConstants::INV . '.show', Crypt::encrypt($creditNote->invoice)) }}"
                                                       class="{{ VC::BT_OUTPM }}">
                                                        {{ $user?->invoiceNumberFormat($invoice->invoice_id) }}
                                                    </a>
                                                </td>
                                                <td>{{ $invoice->customer->name ?? '-' }}</td>
                                                <td>{{ $user?->dateFormat($creditNote->date) }}</td>
                                                <td>{{ $user?->priceFormat($creditNote->amount) }}</td>
                                                <td>{{ $creditNote->description ?? '-' }}</td>
                                                <td class="text-end">
                                                    @can('edit credit note')
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a
                                                                href="#"
                                                                id="{{ $editBtnId }}"
                                                                data-url="{{ $editRoute }}"
                                                                data-guard-msg="{{ $editGuardMsg }}"
                                                                data-listener-alias="edit-credit-note"
                                                                data-ajax-popup="true"
                                                                data-size="md"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Edit') }}"
                                                                class="{{ VC::BT_SM_FL_CT }}"
                                                            >
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete credit note')
                                                        {!! Collective\Html\FormFacade::open([
                                                            'method'         => 'DELETE',
                                                            'route'          => [ViewsConstants::INV . '.delete.credit.note', $creditNote->invoice, $creditNote->id],
                                                            'id'             => $deleteFormId,
                                                            'data-url'       => $delRoute,
                                                            'data-guard-msg' => $delGuardMsg,
                                                        ]) !!}
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            <a
                                                                href="#"
                                                                data-listener-alias="delete-credit-note"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        </div>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const bindGuard = (el, event, urlAttr='data-url', msgAttr='data-guard-msg') => {
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
                    } catch {}
                });
            };

            bindGuard(document.getElementById('createCreditNoteBtn'), 'click');
            document.querySelectorAll('[data-listener-alias="edit-credit-note"]').forEach(el => bindGuard(el, 'click'));
            document.querySelectorAll('[data-listener-alias="delete-credit-note"]').forEach(el => bindGuard(el, 'click'));
        })();
    </script>
@endpush
