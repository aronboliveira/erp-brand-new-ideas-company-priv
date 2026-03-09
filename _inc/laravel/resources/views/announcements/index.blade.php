@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
    } catch (\Throwable $e) {
        \Log::error('announcements/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Announcement')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Announcement')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ ViewClassNamesConstants::FEND }}">
        @can('create announcement')
            @php
                try {
                    $createAnnouncementRoute = Route::has(ViewsConstants::ANC.'.create')
                        ? route(ViewsConstants::ANC.'.create')
                        : '#';
                    $createAnnouncementId = 'announcement-create-link';
                    $createAnnouncementMsg = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::ANC,
                        'announcement_create_route_unavailable'
                    ) ?? 'Create Announcement route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('announcements/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="#"
               id="{{ $createAnnouncementId }}"
               data-url="{{ $createAnnouncementRoute }}"
               data-sv-localized="true"
               data-guard-msg="{{ base64_encode($createAnnouncementMsg) }}"
               data-size="lg"
               data-ajax-popup="true"
               data-title="{{ __('Create New Announcement') }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               class="{{ ViewClassNamesConstants::BT_SM_PM }}">
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="{{ ViewClassNamesConstants::C12 }}">
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if(Gate::check('edit announcement') || Gate::check('delete announcement'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach($announcements as $announcement)
                                    <tr>
                                        <td>{{ $announcement->title ?? __('No title') }}</td>
                                        <td>{{ $user?->dateFormat($announcement->start_date ?? null) ?? '-' }}</td>
                                        <td>{{ $user?->dateFormat($announcement->end_date ?? null) ?? '-' }}</td>
                                        <td>{{ $announcement->description ?? __('No description') }}</td>
                                        @if(Gate::check('edit announcement') || Gate::check('delete announcement'))
                                            <td>
                                                @can('edit announcement')
                                                    @php
                                                        try {
                                                            $editRoute = Route::has(ViewsConstants::ANC.'.edit')
                                                                ? route(ViewsConstants::ANC.'.edit', $announcement->id)
                                                                : '#';
                                                            $editId = 'announcement-edit-' . $announcement->id . '-link';
                                                            $editMsg = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::ANC,
                                                                'announcement_edit_route_unavailable'
                                                            ) ?? 'Edit Announcement route is unavailable. Please contact technical support or your domain administrator.';
                                                        } catch (\Throwable $e) {
                                                            \Log::error('announcements/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                        <a
                                                            id="{{ $editId }}"
                                                            href="#"
                                                            data-url="{{ $editRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ base64_encode($editMsg) }}"
                                                            data-size="lg"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Edit Announcement') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            class="{{ ViewClassNamesConstants::BT_SM_MX3 }} {{ ViewClassNamesConstants::AL_IT_CT }}"
                                                        >
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete announcement')
                                                    @php
                                                        try {
                                                            $deleteRoute = Route::has(ViewsConstants::ANC.'.destroy')
                                                                ? route(ViewsConstants::ANC.'.destroy', $announcement->id)
                                                                : '#';
                                                            $deleteId = 'announcement-delete-' . $announcement->id . '-link';
                                                            $deleteMsg = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::ANC,
                                                                'announcement_destroy_route_unavailable'
                                                            ) ?? 'Delete Announcement route is unavailable. Please contact technical support or your domain administrator.';
                                                        } catch (\Throwable $e) {
                                                            \Log::error('announcements/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                        {!! Collective\Html\FormFacade::open([
                                                            'method' => 'DELETE',
                                                            'url'    => $deleteRoute,
                                                            'id'     => 'delete-form-'.($announcement->id ?? 0)
                                                        ]) !!}
                                                            <a
                                                                id="{{ $deleteId }}"
                                                                href="#"
                                                                class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                                data-url="{{ $deleteRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ base64_encode($deleteMsg) }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone.') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{$announcement->id}}').submit();"
                                                            >
                                                                <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            const ids = ['{{ $editId ?? '' }}', '{{ $deleteId ?? '' }}'].filter(Boolean);
                                                            const flagAttr = 'data-listener-active';
                                                            ids.forEach(id => {
                                                                const el = document.getElementById(id);
                                                                if (!el || el.getAttribute(flagAttr) === 'true') return;
                                                                el.setAttribute(flagAttr, 'true');
                                                                el.addEventListener('click', event => {
                                                                    try {
                                                                        const url = el.getAttribute('data-url');
                                                                        const href = el.href.replace(window.location.origin, '').replace(window.location.pathname, '');
                                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                                            event.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        }
                                                                    } catch {}
                                                                });
                                                                const observer = new MutationObserver(() => {
                                                                    if (!document.getElementById(id)) observer.disconnect();
                                                                });
                                                                observer.observe(document.body, { childList: true, subtree: true });
                                                            });
                                                        })();
                                                    </script>
                                                @endpush
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

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/announcements/lang/index.js') }}"></script>
    <script defer>
        (() => {
            const errFb = '# ERROR';
            const dataClientLocalized = 'data-client-localized';
            const dataGuardMsg = 'data-guard-msg';
            const langSessionKey = 'erp-np-lang';

            const getLocalizedMessage = (msgKey, el) => {
                let msg = errFb;
                if (
                el.getAttribute('data-sv-localized') === 'true' ||
                el.getAttribute(dataClientLocalized) === 'true'
                ) {
                msg = el.getAttribute(dataGuardMsg) ?? errFb;
                } else {
                let lang = (
                    window.sessionStorage.getItem(langSessionKey) ??
                    document.documentElement.lang ??
                    'en'
                )
                    .toLowerCase()
                    .replace(/_/g, '-');
                lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                msg =
                    window.translations?.[lang]?.[msgKey] ??
                    el.getAttribute(dataGuardMsg) ??
                    window.translations?.['en']?.[msgKey] ??
                    errFb;
                if (msg !== errFb) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, 'true');
                }
                }
                return msg;
            };

            const showError = message => {
                try {
                let container = document.querySelector('#bootstrap-toast-container');
                if (!container) {
                    const hasBs =
                    Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                        .some(l => /bootstrap/i.test(l.href)) &&
                    window.bootstrap?.Toast;
                    if (hasBs) {
                    container = document.createElement('div');
                    container.id = 'bootstrap-toast-container';
                    container.setAttribute('aria-live', 'polite');
                    container.setAttribute('aria-atomic', 'true');
                    document.body.appendChild(container);
                    }
                }
                if (container && window.bootstrap.Toast) {
                    let toast = container.querySelector('.toast');
                    if (!toast) {
                    toast = document.createElement('div');
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
                    }
                    toast.querySelector('.toast-body').textContent = message;
                    new bootstrap.Toast(toast).show();
                } else {
                    alert(message);
                }
                } catch {
                alert(message);
                }
            };

            let errorMessage = '';
            const onErrorPointerUp = () => {
                if (errorMessage) {
                showError(errorMessage);
                errorMessage = '';
                }
            };
            document.addEventListener('pointerup', onErrorPointerUp);
            new MutationObserver((muts, obs) => {
                muts.forEach(m => m.removedNodes.forEach(n => {
                if (n === document.documentElement) {
                    document.removeEventListener('pointerup', onErrorPointerUp);
                    obs.disconnect();
                }
                }));
            }).observe(document.body, { childList: true, subtree: true });

            document.addEventListener('DOMContentLoaded', () => {
                const branchEl = document.getElementById('branch_id');
                if (branchEl) {
                if (branchEl.dataset.listenerAttached !== 'true') {
                    branchEl.dataset.listenerAttached = 'true';
                    branchEl.addEventListener('change', () => loadDepartments(branchEl.value));
                    new MutationObserver((m, o) => {
                    m.forEach(mut => mut.removedNodes.forEach(node => {
                        if (node === branchEl) {
                        branchEl.removeEventListener('change', loadDepartments);
                        o.disconnect();
                        }
                    }));
                    }).observe(document.body, { childList: true, subtree: true });
                }
                loadDepartments(branchEl.value ?? '');
                }

                const deptEl = document.getElementById('department_id');
                if (deptEl) {
                if (deptEl.dataset.listenerAttached !== 'true') {
                    deptEl.dataset.listenerAttached = 'true';
                    deptEl.addEventListener('change', () => loadEmployees(deptEl.value));
                    new MutationObserver((m, o) => {
                    m.forEach(mut => mut.removedNodes.forEach(node => {
                        if (node === deptEl) {
                        deptEl.removeEventListener('change', loadEmployees);
                        o.disconnect();
                        }
                    }));
                    }).observe(document.body, { childList: true, subtree: true });
                }
                }
            });

            function loadDepartments(branchId) {
                try {
                $.ajax({
                    url: '{{ route("announcements.getdepartment") }}',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                    branch_id: branchId,
                    _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
                    }
                })
                .done(data => {
                    const deptEl = document.getElementById('department_id');
                    if (!deptEl) return;
                    deptEl.innerHTML = '';
                    const opt0 = document.createElement('option');
                    opt0.value = '';
                    opt0.textContent = '{{ __("Select Department") }}';
                    deptEl.appendChild(opt0);
                    const optAll = document.createElement('option');
                    optAll.value = '0';
                    optAll.textContent = '{{ __("All Department") }}';
                    deptEl.appendChild(optAll);
                    Object.entries(data).forEach(([key, val]) => {
                    const o = document.createElement('option');
                    o.value = key;
                    o.textContent = val;
                    deptEl.appendChild(o);
                    });
                })
                .fail(() => {
                    errorMessage = getLocalizedMessage('announcement_department_unavailable', document.getElementById('branch_id') || document.body);
                });
                } catch {
                errorMessage = getLocalizedMessage('announcement_department_unavailable', document.getElementById('branch_id') || document.body);
                }
            }

            function loadEmployees(deptId) {
                try {
                $.ajax({
                    url: '{{ route("announcements.getemployee") }}',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                    department_id: deptId,
                    _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
                    }
                })
                .done(data => {
                    const empEl = document.getElementById('employee_id');
                    if (!empEl) return;
                    empEl.innerHTML = '';
                    const opt0 = document.createElement('option');
                    opt0.value = '';
                    opt0.textContent = '{{ __("Select Employee") }}';
                    empEl.appendChild(opt0);
                    const optAll = document.createElement('option');
                    optAll.value = '0';
                    optAll.textContent = '{{ __("All Employee") }}';
                    empEl.appendChild(optAll);
                    Object.entries(data).forEach(([key, val]) => {
                    const o = document.createElement('option');
                    o.value = key;
                    o.textContent = val;
                    empEl.appendChild(o);
                    });
                })
                .fail(() => {
                    errorMessage = getLocalizedMessage('announcement_employee_unavailable', document.getElementById('department_id') || document.body);
                });
                } catch {
                errorMessage = getLocalizedMessage('announcement_employee_unavailable', document.getElementById('department_id') || document.body);
                }
            }
        })();
    </script>
@endpush
