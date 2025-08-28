@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $profile= Utility::getFile('uploads/avatar/');
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
        const ERR_FB = '# ERROR';
        const CLIENT_FLAG = 'data-client-localized';
        const GUARD_MSG = 'data-guard-msg';
        const LANG_KEY = 'erp-np-lang';
        let errorMessage = '';
        
        function getLocalizedMessage(key, el) {
            let msg = ERR_FB;
            if (el.getAttribute(CLIENT_FLAG) === 'true') {
            msg = el.getAttribute(GUARD_MSG) || msg;
            } else {
            let lang = (
                sessionStorage.getItem(LANG_KEY) ||
                document.documentElement.lang ||
                'en'
            ).toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg =
                translations?.[lang]?.[key] ||
                el.getAttribute(GUARD_MSG) ||
                translations?.['en']?.[key] ||
                msg;
            if (msg !== ERR_FB) {
                el.setAttribute(GUARD_MSG, msg);
                el.setAttribute(CLIENT_FLAG, 'true');
            }
            }
            return msg;
        }
        
        function showError(message) {
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
        }).observe(document.body, { childList:true, subtree:true });
        
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('billing_data');
            if (!btn || btn.dataset.listenerAttached === 'true') return;
            btn.dataset.listenerAttached = 'true';
        
            const handler = () => {
            try {
                const fields = [
                'name','country','state','city','phone','zip','address'
                ];
                fields.forEach(key => {
                const bill = $(`[name='billing_${key}']`);
                const ship = $(`[name='shipping_${key}']`);
                if (!bill.length || !ship.length) {
                    throw new Error('shipping_copy_failed');
                }
                ship.val(bill.val());
                });
            } catch (e) {
                errorMessage = getLocalizedMessage(e.message, btn);
            }
            };
        
            btn.addEventListener('click', handler);
            new MutationObserver((muts, obs) => {
            muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
                if (n === btn) {
                btn.removeEventListener('click', handler);
                obs.disconnect();
                }
            }));
            }).observe(document.body, { childList:true, subtree:true });
        });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Customers')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Customer')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $importRoute = Route::has(ViewsConstants::CST . '.file.import')
            ? route(ViewsConstants::CST . '.file.import')
            : '#';
        $importGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CST,
            'customers_import_route_unavailable'
        ) ?? 'Customer CSV import route is unavailable. Please contact technical support or your domain administrator.';

        $exportRoute = Route::has(ViewsConstants::CST . '.export')
            ? route(ViewsConstants::CST . '.export')
            : '#';
        $exportGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CST,
            'customers_export_route_unavailable'
        ) ?? 'Customer export route is unavailable. Please contact technical support or your domain administrator.';

        $createRoute = Route::has(ViewsConstants::CST . '.create')
            ? route(ViewsConstants::CST . '.create')
            : '#';
        $createGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CST,
            'customers_create_route_unavailable'
        ) ?? 'Customer create route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <div class="{{ VC::FEND }}">
        <a
            id="customer-import-btn"
            href="{{ $importRoute }}"
            data-url="{{ $importRoute }}"
            data-guard-msg="{{ $importGuardMsg }}"
            data-size="md"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="{{ __('Import') }}"
            data-title="{{ __('Import customer CSV file') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_IMP }}"></i>
        </a>

        <a
            id="customer-export-btn"
            href="{{ $exportRoute }}"
            data-url="{{ $exportRoute }}"
            data-guard-msg="{{ $exportGuardMsg }}"
            data-bs-toggle="tooltip"
            title="{{ __('Export') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_EXP }}"></i>
        </a>

        <a
            id="customer-create-btn"
            href="{{ $createRoute }}"
            data-url="{{ $createRoute }}"
            data-guard-msg="{{ $createGuardMsg }}"
            data-size="lg"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="{{ __('Create') }}"
            data-title="{{ __('Create Customer') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
    </div>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                const btn = document.getElementById('customer-import-btn');
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
                    } catch {}
                });
            })();
        </script>
        <script defer>
            (() => {
                const btn = document.getElementById('customer-export-btn');
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
                    } catch {}
                });
            })();
        </script>
        <script defer>
            (() => {
                const btn = document.getElementById('customer-create-btn');
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
                    } catch {}
                });
            })();
        </script>
    @endpush
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Balance') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers as $customer)
                                    @php
                                        $namespace = ViewsConstants::CST;
                                        $showRoute = Route::has("{$namespace}.show")
                                            ? route("{$namespace}.show", $customer['id'])
                                            : '#';
                                        $showEncryptedRoute = Route::has("{$namespace}.show")
                                            ? route("{$namespace}.show", Crypt::encrypt($customer['id']))
                                            : '#';
                                        $showGuardMsg = Utility::fetchLinkMessage($lang, $namespace, 'customers_show_route_unavailable')
                                            ?? 'Customer show route is unavailable. Please contact technical support or your domain administrator.';
                                        $editRoute = Route::has("{$namespace}.edit")
                                            ? route("{$namespace}.edit", $customer['id'])
                                            : '#';
                                        $editGuardMsg = Utility::fetchLinkMessage($lang, $namespace, 'customers_edit_route_unavailable')
                                            ?? 'Customer edit route is unavailable. Please contact technical support or your domain administrator.';
                                        $destroyRoute = Route::has("{$namespace}.destroy")
                                            ? route("{$namespace}.destroy", $customer['id'])
                                            : '#';
                                        $destroyGuardMsg = Utility::fetchLinkMessage($lang, $namespace, 'customers_destroy_route_unavailable')
                                            ?? 'Customer delete route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <tr class="cust_tr" data-url="{{ $showRoute }}" data-id="{{ $customer['id'] }}">
                                        <td>
                                            @can('show customer')
                                                <a
                                                    id="customer-show-btn-{{ $customer['id'] }}"
                                                    href="{{ $showEncryptedRoute }}"
                                                    data-url="{{ $showEncryptedRoute }}"
                                                    data-guard-msg="{{ $showGuardMsg }}"
                                                    class="{{ VC::BT_OUTPM }}"
                                                >
                                                    {{ $user?->customerNumberFormat($customer['customer_id']) }}
                                                </a>
                                            @else
                                                <a
                                                    id="customer-show-btn-{{ $customer['id'] }}"
                                                    href="#"
                                                    data-url="#"
                                                    data-guard-msg="{{ $showGuardMsg }}"
                                                    class="{{ VC::BT_OUTPM }}"
                                                >
                                                    {{ $user?->customerNumberFormat($customer['customer_id']) }}
                                                </a>
                                            @endcan
                                        </td>
                                        <td class="font-style">{{ $customer['name'] }}</td>
                                        <td>{{ $customer['contact'] }}</td>
                                        <td>{{ $customer['email'] }}</td>
                                        <td>{{ $user?->priceFormat($customer['balance']) }}</td>
                                        <td class="action">
                                            @if($customer['is_active'])
                                                @can('show customer')
                                                    <div class="{{ VC::ACT_BTN_INF }}">
                                                        <a
                                                            id="customer-view-btn-{{ $customer['id'] }}"
                                                            href="{{ $showEncryptedRoute }}"
                                                            data-url="{{ $showEncryptedRoute }}"
                                                            data-guard-msg="{{ $showGuardMsg }}"
                                                            class="{{ VC::BT_SM_FL_CT }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('View') }}"
                                                        >
                                                            <i class="{{ VC::TI_EYE_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('edit customer')
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a
                                                            id="customer-edit-btn-{{ $customer['id'] }}"
                                                            href="{{ $editRoute }}"
                                                            data-url="{{ $editRoute }}"
                                                            data-guard-msg="{{ $editGuardMsg }}"
                                                            data-ajax-popup="true"
                                                            data-size="lg"
                                                            class="{{ VC::BT_SM_FL_CT }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            data-title="{{ __('Edit Customer') }}"
                                                        >
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete customer')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open(['method' => 'DELETE', 'url' => $destroyRoute, 'id' => 'delete-form-' . $customer['id']]) !!}
                                                            <a
                                                                id="customer-delete-btn-{{ $customer['id'] }}"
                                                                href="#"
                                                                data-url="{{ $destroyRoute }}"
                                                                data-guard-msg="{{ $destroyGuardMsg }}"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            @else
                                                <i class="ti ti-lock" title="Inactive"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer>
                                        (() => {
                                            document.querySelectorAll('[id^="customer-show-btn-"]').forEach(btn => {
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
                                    <script defer>
                                        (() => {
                                            document.querySelectorAll('[id^="customer-edit-btn-"]').forEach(btn => {
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
                                    <script defer>
                                        (() => {
                                            document.querySelectorAll('[id^="customer-delete-btn-"]').forEach(btn => {
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection