@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        ViewsConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang = Utility::fetchUserLang();
    $branchCreateRoute = Route::has(ViewsConstants::BRC . '.create')
        ? route(ViewsConstants::BRC . '.create')
        : '#';
    $branchCreateBtnId = 'branch-create-btn';
    $branchCreateMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::BRC,
        'branch_create_route_unavailable'
    ) ?? 'Branch create route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Branch') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Branch') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create branch')
            <a
                id="{{ $branchCreateBtnId }}"
                href="#"
                data-url="{{ $branchCreateRoute }}"
                data-guard-msg="{{ $branchCreateMsg }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Branch') }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD }}-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Branch') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach ($branches as $branch)
                                    @php
                                        $branchEditRoute    = Route::has(ViewsConstants::BRC . '.edit')
                                            ? route(ViewsConstants::BRC . '.edit', $branch->id)
                                            : '#';
                                        $branchEditBtnId    = 'branch-edit-btn-' . $branch->id;
                                        $branchEditMsg      = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::BRC,
                                            'branch_edit_route_unavailable'
                                        ) ?? 'Branch edit route is unavailable. Please contact technical support or your domain administrator.';

                                        $branchDestroyRoute = Route::has(ViewsConstants::BRC . '.destroy')
                                            ? route(ViewsConstants::BRC . '.destroy', $branch->id)
                                            : '#';
                                        $branchDestroyBtnId = 'branch-delete-btn-' . $branch->id;
                                        $branchDestroyFormId= 'delete-form-' . $branch->id;
                                        $branchDestroyMsg   = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::BRC,
                                            'branch_destroy_route_unavailable'
                                        ) ?? 'Branch destroy route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <tr>
                                        <td>{{ $branch->name }}</td>
                                        <td class="Action text-end">
                                            @can('edit branch')
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a
                                                        id="{{ $branchEditBtnId }}"
                                                        href="#"
                                                        data-url="{{ $branchEditRoute }}"
                                                        data-guard-msg="{{ $branchEditMsg }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Branch') }}"
                                                        class="{{ VC::BT_SM_CT }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete branch')
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'url'            => $branchDestroyRoute,
                                                        'method'         => 'DELETE',
                                                        'id'             => $branchDestroyFormId,
                                                        'data-url'       => $branchDestroyRoute,
                                                        'data-guard-msg' => $branchDestroyMsg,
                                                    ]) !!}
                                                        <a
                                                            id="{{ $branchDestroyBtnId }}"
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $branchDestroyFormId }}').submit();"
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
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const guardClick = id => {
                const el = document.getElementById(id);
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener('click', event => {
                    try {
                        const href = el.getAttribute('href');
                        const url  = el.getAttribute('data-url');
                        if ((href && href !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
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

            guardClick('{{ $branchCreateBtnId }}');

            @foreach ($branches as $branch)
                guardClick('branch-edit-btn-{{ $branch->id }}');
                guardClick('branch-delete-btn-{{ $branch->id }}');
            @endforeach
        })();
    </script>
@endpush