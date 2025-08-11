@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewsConstants,
        StacksConstants,
        YieldingConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@push(StacksConstants::ADM_SCR_PG)
@endpush

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Payment') }}
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    @php
                        $paymentRoute = Route::has(ViewsConstants::CST . '.payment')
                            ? route(ViewsConstants::CST . '.payment')
                            : '#';
                        $paymentGuardMsg = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::CST,
                            'payment_route_unavailable'
                        ) ?? 'Payment route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp
                    {{ Form::open(['url' => $paymentRoute, 'method' => 'GET', 'id' => 'frm_submit']) }}
                        <div class="{{ VC::RW }} justify-content-end mt-2">
                            <div class="{{ VC::CL3 }} {{ VC::CM6 }} {{ VC::CS12 }}">
                                <div class="all-select-box">
                                    <div class="btn-box">
                                        {{ Form::label('date', __('Date'), ['class' => 'text-type']) }}
                                        {{ Form::text('date', request('date'), ['class' => 'form-control datepicker-range']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::CL3 }} {{ VC::CM6 }} {{ VC::CS12 }}">
                                <div class="all-select-box">
                                    <div class="btn-box">
                                        {{ Form::label('category', __('Category'), ['class' => 'text-type']) }}
                                        {{ Form::select('category', ['' => __('All')] + $category, request('category'), ['class' => 'form-control select2']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::C_AT }} my-auto">
                                <a
                                    id="filter-apply-btn"
                                    href="{{ $paymentRoute }}"
                                    data-url="{{ $paymentRoute }}"
                                    data-guard-msg="{{ $paymentGuardMsg }}"
                                    class="apply-btn"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Apply') }}"
                                >
                                    <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                </a>
                                <a
                                    id="filter-reset-btn"
                                    href="{{ $paymentRoute }}"
                                    data-url="{{ $paymentRoute }}"
                                    data-guard-msg="{{ $paymentGuardMsg }}"
                                    class="reset-btn"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Reset') }}"
                                >
                                    <span class="btn-inner--icon"><i class="ti ti-trash-restore-alt"></i></span>
                                </a>
                            </div>
                        </div>
                    {{ Form::close() }}
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer>
                            (() => {
                                const btn = document.getElementById('filter-apply-btn');
                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                btn.setAttribute('data-listener-active', 'true');
                                btn.addEventListener('click', e => {
                                    try {
                                        const url = btn.getAttribute('data-url') || '#';
                                        if (url !== '#') {
                                            document.getElementById('frm_submit').submit();
                                            return;
                                        }
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
                        <script defer>
                            (() => {
                                const btn = document.getElementById('filter-reset-btn');
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
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} dataTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr>
                                        <td>{{ $user?->dateFormat($payment->date) }}</td>
                                        <td>{{ $user?->priceFormat($payment->amount) }}</td>
                                        <td>{{ $payment->category }}</td>
                                        <td>{{ $payment->description }}</td>
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
