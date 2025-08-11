@php
    use Illuminate\Support\Facades\Route;
    use App\Models\Utility;
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        YieldingConstants,
        ViewClassNamesConstants as VC
    };
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Auth;

    $user              = Auth::user();
    $lang              = Utility::fetchUserLang();
    $createRoute       = Route::has(ViewsConstants::CTC_TP . '.create')
        ? route(ViewsConstants::CTC_TP . '.create')
        : (Route::has(Str::kebab(ViewsConstants::CTC_TP . '.create'))
            ? route(Str::kebab(ViewsConstants::CTC_TP . '.create'))
            : '#');
    $createBtnId       = 'contract-type-create-btn';
    $createGuardMsg    = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CTC_TP,
        'contract_type_create_route_unavailable'
    ) ?? 'Contract Type create route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Contract Type') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Contract Type') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        <a
            id="{{ $createBtnId }}"
            href="#"
            data-url="{{ $createRoute }}"
            data-guard-msg="{{ $createGuardMsg }}"
            data-ajax-popup="true"
            data-size="md"
            class="{{ VC::BT_SM_PM }}"
            data-bs-toggle="tooltip"
            title="{{ __('Create New Contract Type') }}"
        >
            <i class="ti ti-plus"></i>
        </a>
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
                                    <th>{{ __('Name') }}</th>
                                    @if($user?->type == 'company')
                                        <th class="text-end">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($types as $type)
                                    @php
                                        $editRoute    = Route::has(ViewsConstants::CTC_TP . '.edit')
                                            ? route(ViewsConstants::CTC_TP . '.edit', $type->id)
                                            : (Route::has(Str::kebab(ViewsConstants::CTC_TP . '.edit'))
                                                ? route(Str::kebab(ViewsConstants::CTC_TP . '.edit'), $contractType->id)
                                                : '#');
                                        $editBtnId    = 'contract-type-edit-btn-' . $type->id;
                                        $editMsg      = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::CTC_TP,
                                            'contract_type_edit_route_unavailable'
                                        ) ?? 'Contract Type edit route is unavailable. Please contact technical support or your domain administrator.';
                                        $delRoute     = Route::has(ViewsConstants::CTC_TP . '.destroy')
                                            ? route(ViewsConstants::CTC_TP . '.destroy', $type->id)
                                            : (Route::has(Str::kebab(ViewsConstants::CTC_TP . '.destroy'))
                                                ? route(Str::kebab(ViewsConstants::CTC_TP . '.destroy'), $contractType->id)
                                                : '#');
                                        $delFormId    = 'contract-type-delete-form-' . $type->id;
                                        $delBtnId     = 'contract-type-delete-btn-' . $type->id;
                                        $delMsg       = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::CTC_TP,
                                            'contract_type_destroy_route_unavailable'
                                        ) ?? 'Contract Type delete route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <tr class="font-style">
                                        <td>{{ $type->name }}</td>
                                        @if($user?->type == 'company')
                                            <td class="action text-end">
                                                <div class="{{ VC::ACT_BTN_INF }}">
                                                    <a
                                                        id="{{ $editBtnId }}"
                                                        href="#"
                                                        data-url="{{ $editRoute }}"
                                                        data-guard-msg="{{ $editMsg }}"
                                                        data-ajax-popup="true"
                                                        data-size="md"
                                                        class="{{ VC::BT_SM_FL_CT }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-title="{{ __('Edit Type') }}"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open([
                                                        'url'            => $delRoute,
                                                        'method'         => 'DELETE',
                                                        'id'             => $delFormId,
                                                        'data-url'       => $delRoute,
                                                        'data-guard-msg' => $delMsg,
                                                    ]) !!}
                                                        <a
                                                            id="{{ $delBtnId }}"
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                        >
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
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

            guardClick('{{ $createBtnId }}');
            @foreach($types as $type)
                guardClick('contract-type-edit-btn-{{ $type->id }}');
                guardClick('contract-type-delete-btn-{{ $type->id }}');
            @endforeach
        })();
    </script>
@endpush
