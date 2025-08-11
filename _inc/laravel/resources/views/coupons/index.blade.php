@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
    $couponCreateRoute  = Route::has(ViewsConstants::CPN . '.create')
        ? route(ViewsConstants::CPN . '.create')
        : '#';
    $couponCreateBtnId  = 'coupon-create-btn';
    $couponCreateMsg    = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPN,
        'coupon_create_route_unavailable'
    ) ?? 'Coupon create route is unavailable. Please contact technical support or your domain administrator.';
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
        
        const getLocalizedMessage = (key, el) => {
            let msg = ERR_FB;
            if (el.getAttribute(CLIENT_FLAG) === 'true') {
            msg = el.getAttribute(GUARD_MSG) || msg;
            } else {
            let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg = translations?.[lang]?.[key] ||
                    el.getAttribute(GUARD_MSG) ||
                    translations?.['en']?.[key] ||
                    msg;
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
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                const body = document.createElement('div');
                body.className = 'toast-body';
                toast.appendChild(body);
                container.appendChild(toast);
                if (toast.getAttribute('data-click-listener') !== 'true') {
                toast.addEventListener('click', () => body.textContent = message);
                toast.setAttribute('data-click-listener', 'true');
                }
                body.textContent = message;
                new bootstrap.Toast(toast).show();
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
            // Toggle manual/auto sections
            document.querySelectorAll('.code').forEach(el => {
            if (el.dataset.listenerAttached === 'true') return;
            el.dataset.listenerAttached = 'true';
            const onClick = () => {
                try {
                const val = el.value;
                const manual = document.getElementById('manual');
                const auto = document.getElementById('auto');
                if (!manual || !auto) throw new Error('code_toggle_failed');
                if (val === 'manual') {
                    manual.classList.replace('d-none', 'd-block');
                    auto.classList.replace('d-block', 'd-none');
                } else {
                    auto.classList.replace('d-none', 'd-block');
                    manual.classList.replace('d-block', 'd-none');
                }
                } catch (e) {
                errorMessage = getLocalizedMessage('code_toggle_failed', el);
                }
            };
            el.addEventListener('click', onClick);
            new MutationObserver((m, obs) => {
                m.forEach(mut => Array.from(mut.removedNodes).forEach(node => {
                if (node === el) {
                    el.removeEventListener('click', onClick);
                    obs.disconnect();
                }
                }));
            }).observe(document.body, { childList: true, subtree: true });
            });
        
            const genBtn = document.getElementById('code-generate');
            if (genBtn && genBtn.dataset.listenerAttached !== 'true') {
            genBtn.dataset.listenerAttached = 'true';
            const onGenerate = () => {
                try {
                const length = 10;
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let result = Array.from({ length }, () =>
                    chars.charAt(Math.floor(Math.random() * chars.length))
                ).join('');
                const autoInput = document.getElementById('auto-code');
                if (!autoInput) throw new Error('code_generation_failed');
                autoInput.value = result;
                } catch (e) {
                errorMessage = getLocalizedMessage('code_generation_failed', genBtn);
                }
            };
            genBtn.addEventListener('click', onGenerate);
            new MutationObserver((m, obs) => {
                m.forEach(mut => Array.from(mut.removedNodes).forEach(node => {
                if (node === genBtn) {
                    genBtn.removeEventListener('click', onGenerate);
                    obs.disconnect();
                }
                }));
            }).observe(document.body, { childList: true, subtree: true });
            }
        });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Coupon')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Coupon')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create coupon')
            <a
                id="{{ $couponCreateBtnId }}"
                href="#"
                data-url="{{ $couponCreateRoute }}"
                data-guard-msg="{{ $couponCreateMsg }}"
                data-size="lg"
                data-ajax-popup="true"
                data-title="{{ __('Create New Coupon') }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const btn = document.getElementById('{{ $couponCreateBtnId }}');
            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
            btn.setAttribute('data-listener-active', 'true');
            btn.addEventListener('click', event => {
                try {
                    const url = btn.getAttribute('data-url');
                    if (!url || url === '#') {
                        event.preventDefault();
                        const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
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
                        btn.setAttribute('data-failed-route', 'true');
                        return;
                    }
                    // otherwise proceed with AJAX popup
                } catch (e) {}
            });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Discount (%)') }}</th>
                                    <th>{{ __('Limit') }}</th>
                                    <th>{{ __('Used') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($coupons as $coupon)
                                    <tr class="font-style">
                                        <td>{{ $coupon->name }}</td>
                                        <td>{{ $coupon->code }}</td>
                                        <td>{{ $coupon->discount }}</td>
                                        <td>{{ $coupon->limit }}</td>
                                        <td>{{ $coupon->used_coupon() }}</td>
                                        <td class="action text-end">
                                            <span>
                                                <div class="{{ VC::ACT_BTN_WRN }}">
                                                    <a href="{{ route(ViewsConstants::CPN . '.show', $coupon->id) }}" class="{{ VC::BT_SM_FL_CT }}" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                        <i class="{{ VC::TI_EYE_WT }}"></i>
                                                    </a>
                                                </div>
                                                @can('edit coupon')
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        @php
                                                            $couponEditRoute    = Route::has(ViewsConstants::CPN . '.edit')
                                                                ? route(ViewsConstants::CPN . '.edit', $coupon->id)
                                                                : '#';
                                                            $couponEditBtnId    = 'coupon-edit-btn-' . $coupon->id;
                                                            $couponEditGuardMsg = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::CPN,
                                                                'coupon_edit_route_unavailable'
                                                            ) ?? 'Coupon edit route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <a
                                                            id="{{ $couponEditBtnId }}"
                                                            href="#"
                                                            class="{{ VC::BT_SM_FL_CT }}"
                                                            data-url="{{ $couponEditRoute }}"
                                                            data-guard-msg="{{ $couponEditGuardMsg }}"
                                                            data-ajax-popup="true"
                                                            data-size="md"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            data-title="{{ __('Edit Coupon') }}"
                                                        >
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById('{{ $couponEditBtnId }}');
                                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                    btn.setAttribute('data-listener-active', 'true');
                                                                    btn.addEventListener('click', event => {
                                                                        try {
                                                                            const url = btn.getAttribute('data-url');
                                                                            if (!url || url === '#') {
                                                                                event.preventDefault();
                                                                                const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                                                                btn.setAttribute('data-failed-route', 'true');
                                                                                return;
                                                                            }
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endcan
                                                @can('delete coupon')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        @php
                                                            $destroyRoute           = Route::has(ViewsConstants::CPN . '.destroy')
                                                                ? route(ViewsConstants::CPN . '.destroy', $coupon->id)
                                                                : '#';
                                                            $destroyFormId          = 'delete-form-' . $coupon->id;
                                                            $destroyBtnId           = 'coupon-destroy-btn-' . $coupon->id;
                                                            $destroyGuardMsg        = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::CPN,
                                                                'coupon_destroy_route_unavailable'
                                                            ) ?? 'Coupon delete route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        {!! Form::open([
                                                            'method'  => 'DELETE',
                                                            'route'   => [ViewsConstants::CPN . '.destroy', $coupon->id],
                                                            'id'      => $destroyFormId,
                                                            'data-url'=> $destroyRoute,
                                                            'data-guard-msg' => $destroyGuardMsg,
                                                        ]) !!}
                                                            <a
                                                                id="{{ $destroyBtnId }}"
                                                                href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-url="{{ $destroyRoute }}"
                                                                data-guard-msg="{{ $destroyGuardMsg }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('{{ $destroyFormId }}').submit();"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById('{{ $destroyBtnId }}');
                                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                    btn.setAttribute('data-listener-active', 'true');
                                                                    btn.addEventListener('click', event => {
                                                                        try {
                                                                            const url = btn.getAttribute('data-url');
                                                                            if (!url || url === '#') {
                                                                                event.preventDefault();
                                                                                const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                                                                btn.setAttribute('data-failed-route', 'true');
                                                                                return;
                                                                            }
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
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
