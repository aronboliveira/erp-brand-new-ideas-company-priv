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
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Sources')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Sources')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            $sourceCreateBaseName        = ViewsConstants::SRC . '.create';
            $sourceCreateKebabName       = Str::kebab($sourceCreateBaseName);
            $sourceCreateResolvedName    = Route::has($sourceCreateBaseName)
                ? $sourceCreateBaseName
                : (Route::has($sourceCreateKebabName) ? $sourceCreateKebabName : null);
            $sourceCreateUrl             = $sourceCreateResolvedName ? route($sourceCreateResolvedName) : '#';
            $sourceCreateGuardMsg        = Utility::fetchLinkMessage($lang, ViewsConstants::SRC, 'source_create_route_unavailable') ?? 'Source create route is unavailable. Please contact technical support or your domain administrator.';
            $sourceCreateBtnId           = 'source-create-btn';
        @endphp
        <a
            id="{{ $sourceCreateBtnId }}"
            href="{{ $sourceCreateUrl }}"
            data-size="md"
            data-url="{{ $sourceCreateUrl }}"
            data-guard-msg="{{ $sourceCreateGuardMsg }}"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="{{ __('Create New Sources') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const el = document.getElementById('{{ $sourceCreateBtnId }}');
                    if (!el || el.getAttribute('data-listener-active') === 'true') return;
                    el.setAttribute('data-listener-active', 'true');
                    el.addEventListener('click', (e) => {
                        try {
                            const url = el.getAttribute('data-url') || '#';
                            if (url !== '#') return;
                            e.preventDefault();
                            const msg = el.getAttribute('data-guard-msg') || '# ERROR';
                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                            let container = document.getElementById('toast-container');
                            if (!container) {
                                container = document.createElement('div');
                                container.id = 'toast-container';
                                document.body.appendChild(container);
                            }
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
                                container.appendChild(toast);
                                bootstrap.Toast.getOrCreateInstance(toast).show();
                            } else {
                                alert(msg);
                            }
                            el.setAttribute('data-failed-route', 'true');
                        } catch (err) {}
                    });
                })();
            </script>
        @endpush
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="col-3">
            @include('layouts.crm_setup')
        </div>
        <div class="col-9">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Source') }}</th>
                                    <th width="250px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sources as $source)
                                    <tr>
                                        <td>{{ $source->name }}</td>
                                        <td class="Active">
                                            @can('edit source')
                                                <div class="{{ VC::ACT_BTN_INF }}">
                                                    @php
                                                        $sourceEditBaseName        = ViewsConstants::SRC . '.edit';
                                                        $sourceEditKebabName       = Str::kebab($sourceEditBaseName);
                                                        $sourceEditResolvedName    = Route::has($sourceEditBaseName)
                                                            ? $sourceEditBaseName
                                                            : (Route::has($sourceEditKebabName) ? $sourceEditKebabName : null);
                                                        $sourceEditUrl             = $sourceEditResolvedName ? route($sourceEditResolvedName, $source->id) : '#';
                                                        $sourceEditGuardMsg        = Utility::fetchLinkMessage($lang, ViewsConstants::SRC, 'source_edit_route_unavailable') ?? 'Source edit route is unavailable. Please contact technical support or your domain administrator.';
                                                        $sourceEditBtnId           = 'source-edit-btn-' . $source->id;
                                                    @endphp
                                                    <a
                                                        id="{{ $sourceEditBtnId }}"
                                                        href="{{ $sourceEditUrl }}"
                                                        class="{{ VC::BT_SM_FL_CT }}"
                                                        data-url="{{ $sourceEditUrl }}"
                                                        data-guard-msg="{{ $sourceEditGuardMsg }}"
                                                        data-ajax-popup="true"
                                                        data-size="md"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-title="{{ __('Edit Source') }}"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const el = document.getElementById('{{ $sourceEditBtnId }}');
                                                                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                                                                el.setAttribute('data-listener-active', 'true');
                                                                el.addEventListener('click', e => {
                                                                    try {
                                                                        const url = el.getAttribute('data-url') || '#';
                                                                        if (url !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = el.getAttribute('data-guard-msg') || '# ERROR';
                                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                        let container = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container = document.createElement('div');
                                                                            container.id = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
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
                                                                            container.appendChild(toast);
                                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
                                                                        el.setAttribute('data-failed-route', 'true');
                                                                    } catch (err) {}
                                                                });
                                                            })();
                                                        </script>
                                                    @endpush
                                                </div>
                                            @endcan
                                            @can('delete source')
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    @php
                                                        $sourceDestroyBaseName        = ViewsConstants::SRC . '.destroy';
                                                        $sourceDestroyKebabName       = Str::kebab($sourceDestroyBaseName);
                                                        $sourceDestroyResolvedName    = Route::has($sourceDestroyBaseName)
                                                            ? $sourceDestroyBaseName
                                                            : (Route::has($sourceDestroyKebabName) ? $sourceDestroyKebabName : null);
                                                        $sourceDestroyRouteArray      = $sourceDestroyResolvedName ? [$sourceDestroyResolvedName, $source->id] : ['#'];
                                                        $sourceDestroyUrl             = $sourceDestroyResolvedName ? route($sourceDestroyResolvedName, $source->id) : '#';
                                                        $sourceDestroyGuardMsg        = Utility::fetchLinkMessage($lang, ViewsConstants::SRC, 'source_destroy_route_unavailable') ?? 'Source destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                        $sourceDeleteFormId           = 'source-delete-form-' . $source->id;
                                                        $sourceDeleteBtnId            = 'source-destroy-btn-' . $source->id;
                                                    @endphp
                                                    {!! Form::open(['method' => 'DELETE', 'route' => $sourceDestroyRouteArray, 'id' => $sourceDeleteFormId]) !!}
                                                        <a
                                                            id="{{ $sourceDeleteBtnId }}"
                                                            href="{{ $sourceDestroyUrl }}"
                                                            data-url="{{ $sourceDestroyUrl }}"
                                                            data-guard-msg="{{ $sourceDestroyGuardMsg }}"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                        >
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const btn = document.getElementById('{{ $sourceDeleteBtnId }}');
                                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                btn.setAttribute('data-listener-active', 'true');
                                                                btn.addEventListener('click', e => {
                                                                    try {
                                                                        const url = btn.getAttribute('data-url') || '#';
                                                                        if (url !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                        let container = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container = document.createElement('div');
                                                                            container.id = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
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
                                                                            container.appendChild(toast);
                                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
                                                                        btn.setAttribute('data-failed-route', 'true');
                                                                    } catch (err) {}
                                                                });
                                                            })();
                                                        </script>
                                                    @endpush
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
