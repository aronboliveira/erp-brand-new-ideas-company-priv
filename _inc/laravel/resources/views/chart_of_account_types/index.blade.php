@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewsConstants,
        PermissionsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
        StacksConstants
    };
    $lang = Utility::fetchUserLang();
    $createName     = ViewsConstants::COA_TP . '.create';
    $createRoute    = Route::has($createName)
        ? route($createName)
        : (Route::has(Str::kebab($createName))
            ? route(Str::kebab($createName))
            : '#');
    $createGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::COA_TP,
        'chart_of_account_type_create_route_unavailable'
    ) ?? 'Create Chart of Account Type route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Chart of Account Type') }}
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="all-button-box {{ VC::RW }} {{ VC::DFL }} {{ VC::JCE }}">
        @can(PermissionsConstants::CR_COA_TYPE)
            <div class="{{ VC::CXL2 }} {{ VC::CL2 }} {{ VC::CM4 }} {{ VC::CS6 }} {{ VC::C6 }}">
                <a
                    href="#"
                    id="createTypeBtn"
                    data-url="{{ $createRoute }}"
                    data-guard-msg="{{ $createGuardMsg }}"
                    data-listener-alias="create-type"
                    data-ajax-popup="true"
                    data-title="{{ __('Create New Type') }}"
                    class="{{ VC::BT_XS }} btn-white btn-icon-only width-auto"
                >
                    <i class="{{ VC::TI_PLS }}"></i> {{ __('Create') }}
                </a>
            </div>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_MT }} {{ VC::BD }}">
                    <div class="{{ VC::TB }}-striped {{ VC::MB0 }} dataTable">
                        <table class="{{ VC::TB }}-striped {{ VC::MB0 }} dataTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($types as $type)
                                    @php
                                        $editName     = ViewsConstants::COA_TP . '.edit';
                                        $editRoute    = Route::has($editName)
                                            ? route($editName, $type->id)
                                            : (Route::has(Str::kebab($editName))
                                                ? route(Str::kebab($editName), $type->id)
                                                : '#');
                                        $editGuardMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::COA_TP,
                                            'chart_of_account_type_edit_route_unavailable'
                                        ) ?? 'Edit Chart of Account Type route is unavailable. Please contact technical support or your domain administrator.';
                                        $destroyName     = ViewsConstants::COA_TP . '.destroy';
                                        $destroyRoute    = Route::has($destroyName)
                                            ? route($destroyName, $type->id)
                                            : (Route::has(Str::kebab($destroyName))
                                                ? route(Str::kebab($destroyName), $type->id)
                                                : '#');
                                        $destroyGuardMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::COA_TP,
                                            'chart_of_account_type_destroy_route_unavailable'
                                        ) ?? 'Delete Chart of Account Type route is unavailable. Please contact technical support or your domain administrator.';
                                        $deleteFormId    = 'delete-form-' . $type->id;
                                    @endphp
                                    <tr>
                                        <td>{{ $type->name }}</td>
                                        <td class="Action">
                                            <span>
                                                @can('edit constant chart of account type')
                                                    <a
                                                        href="#"
                                                        class="edit-icon"
                                                        data-url="{{ $editRoute }}"
                                                        data-guard-msg="{{ $editGuardMsg }}"
                                                        data-listener-alias="edit-type"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Unit') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                @endcan

                                                @can('delete constant chart of account type')
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method'         => 'DELETE',
                                                        'route'          => [ViewsConstants::COA_TP . '.destroy', $type->id],
                                                        'id'             => $deleteFormId,
                                                        'data-url'       => $destroyRoute,
                                                        'data-guard-msg' => $destroyGuardMsg,
                                                    ]) !!}
                                                    <a
                                                        href="#"
                                                        class="delete-icon"
                                                        data-listener-alias="delete-type"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                        data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                    >
                                                        <i class="ti ti-trash"></i>
                                                    </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
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
            bindGuard(document.getElementById('createTypeBtn'), 'click');
            document.querySelectorAll('[data-listener-alias="edit-type"]').forEach(el => bindGuard(el, 'click'));
            document.querySelectorAll('[data-listener-alias="delete-type"]').forEach(el => bindGuard(el, 'click'));
        })();
    </script>
@endpush
