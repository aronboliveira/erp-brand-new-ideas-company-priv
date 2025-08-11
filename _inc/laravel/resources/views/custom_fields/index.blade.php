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
    use Illuminate\Support\Str;
    use Collective\Html\FormFacade as Form;

    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Custom Field')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Custom Field')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create constant custom field')
            @php
                $linkId   = 'create-custom-field-link';
                $createUrl = Route::has(ViewsConstants::CST_FD.'.create')
                    ? route(ViewsConstants::CST_FD.'.create')
                    : '#';
                $createCustomFieldMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::CST_FD,
                    'custom_field_create_route_unavailable'
                ) ?? 'Create route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                href="#"
                id="{{ $linkId }}"
                data-url="{{ $createUrl }}"
                data-sv-localized="true"
                data-guard-msg=" {{ $createCustomFieldMsg }} "
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Custom Field') }}"
                class="{{ VC::BT_SM_PM }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-3">
            @include('layouts.account_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{__('Custom Field')}}</th>
                                <th> {{__('Type')}}</th>
                                <th> {{__('Module')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($custom_fields as $field)
                                <tr>
                                    <td>{{ $field->name}}</td>
                                    <td>{{ $field->type}}</td>
                                    <td>{{ $field->module}}</td>
                                    @if(Gate::check('edit constant custom field') || Gate::check('delete constant custom field'))
                                        <td class="Action">
                                            <span>
                                                @can('edit constant custom field')
                                                    @php
                                                        $linkId  = 'edit-custom-field-link-'.$field->id;
                                                        $editUrl = Route::has(ViewsConstants::CST_FD.'.edit')
                                                            ? route(ViewsConstants::CST_FD.'.edit', $field->id)
                                                            : '#';
                                                        $editCustomFieldMsg = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::CST_FD,
                                                            'custom_field_edit_route_unavailable'
                                                        ) ?? 'Edit Custom Field route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a
                                                            href="{{ $editUrl }}"
                                                            id="{{ $linkId }}"
                                                            class="{{ VC::BT_SM_CT }} edit-custom-field-link"
                                                            data-route-guard
                                                            data-url="{{ $editUrl }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $editCustomFieldMsg }}"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Edit Custom Field') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            data-original-title="{{ __('Edit') }}"
                                                        >
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete constant custom field')
                                                    @php
                                                        $linkId     = 'delete-custom-field-link-'.$field->id;
                                                        $formId     = 'delete-custom-field-form-'.$field->id;
                                                        $destroyUrl = Route::has(ViewsConstants::CST_FD.'.destroy')
                                                            ? route(ViewsConstants::CST_FD.'.destroy', $field->id)
                                                            : '#';
                                                        $deleteCustomFieldMsg = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::CST_FD,
                                                            'custom_field_delete_unavailable'
                                                        ) ?? 'Delete Custom Field route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        {!! Collective\Html\FormFacade::open([
                                                            'method' => 'DELETE',
                                                            'url'    => $destroyUrl,
                                                            'id'     => $formId
                                                        ]) !!}
                                                            <a
                                                                href="{{ $destroyUrl }}"
                                                                id="{{ $linkId }}"
                                                                class="{{ VC::BT_SM_CT_PR }} delete-custom-field-link"
                                                                data-route-guard
                                                                data-url="{{ $destroyUrl }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $deleteCustomFieldMsg }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-original-title="{{ __('Delete') }}"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
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
    @can('create constant custom field')
        <script defer>
            (() => {
                const link = document.getElementById("create-custom-field-link");
                const alias = "data-listening-createclick";
                if (link.hasAttribute(alias)) return;
                link.addEventListener("click", event => {
                    if (link.getAttribute(alias) !== "true") return;
                    const url = link.getAttribute("data-url");
                    if (url !== "#" || link.href !== "#") return;
                    const hasBS = Array.from(document.scripts).some(
                        s =>
                            s.src &&
                            s.src.includes("bootstrap.min.js") &&
                            window.bootstrap &&
                            typeof window.bootstrap.Modal === "function"
                    );
                    const msg = "{{ $createCustomFieldMsg }}";
                    if (hasBS) {
                        const wrapper = document.createElement("div");
                        wrapper.innerHTML = `
                            <div class="modal fade" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Error</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body"><p>${msg}</p></div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        document.body.appendChild(wrapper);
                        new window.bootstrap.Modal(wrapper.querySelector(".modal")).show();
                    } else {
                        alert(msg);
                    }
                    event.preventDefault();
                });
                link.setAttribute(alias, "true");
            })();
        </script>
    @endcan
    @can('edit constant custom field')
        <script defer>
            (() => {
                const selector = ".edit-custom-field-link[data-ajax-popup][data-url]";
                const alias = "data-listening-customfieldseditclick";
                document.querySelectorAll(selector).forEach(el => {
                    if (!el.hasAttribute(alias)) {
                        el.addEventListener("click", event => {
                            if (el.getAttribute(alias) !== "true") return;
                            const url = el.getAttribute("data-url");
                            if (url === "#" && el.href === "#") {
                                event.preventDefault();
                                const hasBS = Array.from(document.scripts).some(
                                    s =>
                                        s.src &&
                                        s.src.includes("bootstrap.min.js") &&
                                        window.bootstrap &&
                                        typeof window.bootstrap.Modal === "function"
                                );
                                const msg = "{{ $editCustomFieldMsg }}";
                                if (hasBS) {
                                    const wrapper = document.createElement("div");
                                    wrapper.innerHTML = `
                                        <div class="modal fade" tabindex="-1">
                                            <div class="modal-dialog modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Error</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body"><p>${msg}</p></div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>`;
                                    document.body.appendChild(wrapper);
                                    new window.bootstrap.Modal(wrapper.querySelector(".modal")).show();
                                } else {
                                    alert(msg);
                                }
                            }
                        });
                        el.setAttribute(alias, "true");
                    }
                });
            })();
        </script>
        <script defer>
            (() => {
                const selector = ".delete-custom-field-link[data-route-guard][data-url]";
                const alias = "data-listening-customfieldsdeleteclick";
                document.querySelectorAll(selector).forEach(el => {
                    if (!el.hasAttribute(alias)) {
                        el.setAttribute(alias, "true");
                        el.addEventListener("click", event => {
                            if (el.getAttribute(alias) !== "true") return;
                            const url = el.getAttribute("data-url");
                            if (url === "#" && el.href === "#") {
                                event.preventDefault();
                                const hasBS = Array.from(document.scripts).some(
                                    s =>
                                        s.src &&
                                        s.src.includes("bootstrap.min.js") &&
                                        window.bootstrap &&
                                        typeof window.bootstrap.Modal === "function"
                                );
                                const msg = "{{ $deleteCustomFieldMsg }}";
                                if (hasBS) {
                                    const wrapper = document.createElement("div");
                                    wrapper.innerHTML = `
                                        <div class="modal fade" tabindex="-1">
                                            <div class="modal-dialog modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Error</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body"><p>${msg}</p></div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>`;
                                    document.body.appendChild(wrapper);
                                    new window.bootstrap.Modal(wrapper.querySelector(".modal")).show();
                                } else {
                                    alert(msg);
                                }
                            }
                        });
                    }
                });
            })();
        </script>
    @endcan
@endpush

