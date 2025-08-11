@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\ViewsConstants;

    $lang = Utility::fetchUserLang();
    $createName     = ViewsConstants::CPN_PL . '.create';
    $createRoute    = Route::has($createName)
        ? route($createName)
        : (Route::has(Str::kebab($createName))
            ? route(Str::kebab($createName))
            : '#');
    $createGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPN_PL,
        'company_policy_create_route_unavailable'
    ) ?? 'Create Company Policy route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Company Policy') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Company Policy') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create company policy')
            <a
                href="#"
                id="createPolicyBtn"
                data-url="{{ $createRoute }}"
                data-guard-msg="{{ $createGuardMsg }}"
                data-listener-alias="create-policy"
                data-ajax-popup="true"
                data-title="{{ __('Create New Company Policy') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ VC::BT_SM }} {{ VC::BT_PRM }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD }}-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Attachment') }}</th>
                                    @if(Gate::check('edit company policy') || Gate::check('delete company policy'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach($companyPolicy as $policy)
                                    @php
                                        $editName     = ViewsConstants::CPN_PL . '.edit';
                                        $editRoute    = Route::has($editName)
                                            ? route($editName, $policy->id)
                                            : (Route::has(Str::kebab($editName))
                                                ? route(Str::kebab($editName), $policy->id)
                                                : '#');
                                        $editGuardMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::CPN_PL,
                                            'company_policy_edit_route_unavailable'
                                        ) ?? 'Edit Company Policy route is unavailable. Please contact technical support or your domain administrator.';

                                        $destroyName     = ViewsConstants::CPN_PL . '.destroy';
                                        $destroyRoute    = Route::has($destroyName)
                                            ? route($destroyName, $policy->id)
                                            : (Route::has(Str::kebab($destroyName))
                                                ? route(Str::kebab($destroyName), $policy->id)
                                                : '#');
                                        $destroyGuardMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::CPN_PL,
                                            'company_policy_destroy_route_unavailable'
                                        ) ?? 'Delete Company Policy route is unavailable. Please contact technical support or your domain administrator.';
                                        $deleteFormId    = 'delete-form-' . $policy->id;
                                        $policyPath      = Utility::getFile('uploads/companyPolicy');
                                    @endphp
                                    <tr>
                                        <td>{{ optional($policy->branches)->name }}</td>
                                        <td>{{ $policy->title }}</td>
                                        <td>{{ $policy->description }}</td>
                                        <td>
                                            @if($policy->attachment)
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a href="{{ $policyPath . '/' . $policy->attachment }}"
                                                       download=""
                                                       class="{{ VC::BT_SM }} {{ VC::AL_IT_CT }}">
                                                        <i class="ti ti-download {{ VC::TXT_WT }}"></i>
                                                    </a>
                                                </div>
                                                <div class="{{ VC::ACT_BTN }} bg-secondary ms-2">
                                                    <a href="{{ $policyPath . '/' . $policy->attachment }}"
                                                       target="_blank"
                                                       class="{{ VC::BT_SM }} {{ VC::AL_IT_CT }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Preview') }}">
                                                        <i class="ti ti-crosshair {{ VC::TXT_WT }}"></i>
                                                    </a>
                                                </div>
                                            @else
                                                <p>-</p>
                                            @endif
                                        </td>
                                        @if(Gate::check('edit company policy') || Gate::check('delete company policy'))
                                            <td>
                                                @can('edit company policy')
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a
                                                            href="#"
                                                            class="{{ VC::BT_SM }} {{ VC::AL_IT_CT }}"
                                                            id="editPolicyBtn_{{ $policy->id }}"
                                                            data-url="{{ $editRoute }}"
                                                            data-guard-msg="{{ $editGuardMsg }}"
                                                            data-listener-alias="edit-policy"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Edit Company Policy') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                        >
                                                            <i class="ti ti-pencil {{ VC::TXT_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete company policy')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open([
                                                            'method'         => 'DELETE',
                                                            'route'          => [ViewsConstants::CPN_PL . '.destroy', $policy->id],
                                                            'id'             => $deleteFormId,
                                                            'data-url'       => $destroyRoute,
                                                            'data-guard-msg' => $destroyGuardMsg,
                                                        ]) !!}
                                                        <a
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-listener-alias="delete-policy"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                        >
                                                            <i class="ti ti-trash {{ VC::TXT_WT }}"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
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
                    } catch (e) {}
                });
            };

            bindGuard(document.getElementById('createPolicyBtn'), 'click');
            document.querySelectorAll('[data-listener-alias="edit-policy"]').forEach(el => bindGuard(el, 'click'));
            document.querySelectorAll('[data-listener-alias="delete-policy"]').forEach(el => bindGuard(el, 'click'));
        })();
    </script>
@endpush
