@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
        StacksConstants
    };

    $lang = Utility::fetchUserLang();
    $createName     = ViewsConstants::CPT . '.create';
    $createRoute    = Route::has($createName)
        ? route($createName)
        : '#';
    $createGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPT,
        'competency_create_route_unavailable'
    ) ?? 'Competency create route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Competencies') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Competencies') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('Create Competencies')
            <a
                href="#"
                id="createCompetencyBtn"
                data-url="{{ $createRoute }}"
                data-guard-msg="{{ $createGuardMsg }}"
                data-listener-alias="create-competency"
                data-ajax-popup="true"
                data-title="{{ __('Create New Competencies') }}"
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
        <div class="{{ VC::C3 }}">
            @include('layouts.hrm_setup')
        </div>
        <div class="{{ VC::C9 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD }}-body {{ VC::TB }}">
                    <div class="{{ VC::DFL }} {{ VC::JCE }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach ($competencies as $competency)
                                    @php
                                        $editName     = ViewsConstants::CPT . '.edit';
                                        $editRoute    = Route::has($editName)
                                            ? route($editName, $competency->id)
                                            : '#';
                                        $editGuardMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::CPT,
                                            'competency_edit_route_unavailable'
                                        ) ?? 'Competency edit route is unavailable. Please contact technical support or your domain administrator.';
                                        $destroyName     = ViewsConstants::CPT . '.destroy';
                                        $destroyRoute    = Route::has($destroyName)
                                            ? route($destroyName, $competency->id)
                                            : '#';
                                        $destroyGuardMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::CPT,
                                            'competency_destroy_route_unavailable'
                                        ) ?? 'Competency delete route is unavailable. Please contact technical support or your domain administrator.';
                                        $deleteFormId = 'delete-form-' . $competency->id;
                                    @endphp
                                    <tr>
                                        <td>{{ $competency->name }}</td>
                                        <td>{{ optional($competency->performance)->name }}</td>
                                        <td class="Action">
                                            @can('edit document type')
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a
                                                        href="#"
                                                        class="{{ VC::DFL_IL }} {{ VC::ALC }}"
                                                        id="editCompetencyBtn_{{ $competency->id }}"
                                                        data-url="{{ $editRoute }}"
                                                        data-guard-msg="{{ $editGuardMsg }}"
                                                        data-listener-alias="edit-competency"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Competencies') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('Delete Competencies')
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open([
                                                        'method'         => 'DELETE',
                                                        'route'          => [ViewsConstants::CPT . '.destroy', $competency->id],
                                                        'id'             => $deleteFormId,
                                                        'data-url'       => $destroyRoute,
                                                        'data-guard-msg' => $destroyGuardMsg,
                                                    ]) !!}
                                                        <a
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-listener-alias="delete-competency"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                        >
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
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
                    } catch {}
                });
            };

            bindGuard(document.getElementById('createCompetencyBtn'), 'click');
            document.querySelectorAll('[data-listener-alias="edit-competency"]').forEach(el => bindGuard(el, 'click'));
            document.querySelectorAll('[data-listener-alias="delete-competency"]').forEach(el => bindGuard(el, 'click'));
        })();
    </script>
@endpush
