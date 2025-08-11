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
    $createName    = ViewsConstants::CLT . '.create';
    $createRoute   = Route::has($createName)
        ? route($createName)
        : (Route::has(Str::kebab($createName))
            ? route(Str::kebab($createName))
            : '#');
    $createGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CLT,
        'client_create_route_unavailable'
    ) ?? 'Create Client route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Client') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Client') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can(PermissionsConstants::CR_CLT)
            <a
                href="#"
                id="createClientBtn"
                data-url="{{ $createRoute }}"
                data-guard-msg="{{ $createGuardMsg }}"
                data-listener-alias="create-client"
                data-size="md"
                data-ajax-popup="true"
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
        <div class="col-xxl-12">
            <div class="{{ VC::RW }}">
                @foreach($clients as $client)
                    @php
                        $editName    = ViewsConstants::CLT . '.edit';
                        $editRoute   = Route::has($editName)
                            ? route($editName, $client->id)
                            : (Route::has(Str::kebab($editName))
                                ? route(Str::kebab($editName), $client->id)
                                : '#');
                        $editGuardMsg = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::CLT,
                            'client_edit_route_unavailable'
                        ) ?? 'Edit Client route is unavailable. Please contact technical support or your domain administrator.';
                        $destroyName    = ViewsConstants::CLT . '.destroy';
                        $destroyRoute   = Route::has($destroyName)
                            ? route($destroyName, $client->id)
                            : (Route::has(Str::kebab($destroyName))
                                ? route(Str::kebab($destroyName), $client->id)
                                : '#');
                        $destroyGuardMsg = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::CLT,
                            'client_destroy_route_unavailable'
                        ) ?? 'Delete Client route is unavailable. Please contact technical support or your domain administrator.';
                        $deleteFormId   = 'delete-form-' . $client->id;
                        $resetName    = 'clients.reset';
                        $resetRoute   = Route::has($resetName)
                            ? route($resetName, Crypt::encrypt($client->id))
                            : '#';
                        $resetGuardMsg = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::CLT,
                            'client_reset_route_unavailable'
                        ) ?? 'Reset Password route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp
                    <div class="{{ VC::CM3 }}">
                        <div class="{{ VC::CD }} text-center">
                            <div class="card-header border-0 pb-0">
                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle"
                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                            <i class="{{ VC::TI_DRP }}"></i>
                                        </button>
                                        <div class="{{ VC::DRP_MN_EM }}">
                                            @can('edit client')
                                                <a
                                                    href="#"
                                                    class="dropdown-item edit-icon"
                                                    id="editClientBtn_{{ $client->id }}"
                                                    data-url="{{ $editRoute }}"
                                                    data-guard-msg="{{ $editGuardMsg }}"
                                                    data-listener-alias="edit-client"
                                                    data-size="md"
                                                    data-ajax-popup="true"
                                                >
                                                    <i class="{{ VC::TI_PC }}"></i>
                                                    <span>{{ __('Edit') }}</span>
                                                </a>
                                            @endcan
                                            @can('delete client')
                                                {!! Collective\Html\FormFacade::open([
                                                    'method'         => 'DELETE',
                                                    'route'          => [ViewsConstants::CLT . '.destroy', $client->id],
                                                    'id'             => $deleteFormId,
                                                    'data-url'       => $destroyRoute,
                                                    'data-guard-msg' => $destroyGuardMsg,
                                                ]) !!}
                                                <a
                                                    href="#"
                                                    class="dropdown-item delete-icon"
                                                    data-listener-alias="delete-client"
                                                    data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                    data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                >
                                                    <i class="{{ VC::TI_TRS }}"></i>
                                                    <span>
                                                        {{ $client->delete_status != 0 ? __('Delete') : __('Restore') }}
                                                    </span>
                                                </a>
                                                {!! Collective\Html\FormFacade::close() !!}
                                            @endcan
                                            <a
                                                href="#"
                                                class="dropdown-item reset-icon"
                                                id="resetClientBtn_{{ $client->id }}"
                                                data-url="{{ $resetRoute }}"
                                                data-guard-msg="{{ $resetGuardMsg }}"
                                                data-listener-alias="reset-client"
                                                data-ajax-popup="true"
                                            >
                                                <i class="{{ VC::TI_ADJ }}"></i>
                                                <span>{{ __('Reset Password') }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::CD_MT }} full-card">
                                <div class="card-avatar">
                                    <img src="{{ $client->avatar
                                        ? asset(Storage::url('uploads/avatar/'.$client->avatar))
                                        : asset(Storage::url('uploads/avatar/avatar.png')) }}"
                                         class="img-user wid-80 rounded-circle">
                                </div>
                                <h4 class="mt-2 text-primary">{{ $client->name }}</h4>
                                <div class="{{ VC::DFL_AIC_JCB }}">
                                    <div class="me-4 text-primary">
                                        {{ $client->email }}
                                    </div>
                                </div>
                                <div class="mt-2 h6" data-bs-toggle="tooltip" title="{{ __('Last Login') }}">
                                    {{ $client->last_login_at ?? '' }}
                                </div>
                            </div>
                            <div class="card-footer p-3">
                                <div class="{{ VC::DFL_JCB }}">
                                    <div>
                                        <h6 class="mb-0">{{ $client->clientDeals->count() ?? 0 }}</h6>
                                        <p class="text-muted text-sm mb-0">{{ __('Deals') }}</p>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $client->clientProjects->count() ?? 0 }}</h6>
                                        <p class="text-muted text-sm mb-0">{{ __('Projects') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
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

            bindGuard(document.getElementById('createClientBtn'), 'click');
            document.querySelectorAll('[data-listener-alias="edit-client"]').forEach(el => bindGuard(el, 'click'));
            document.querySelectorAll('[data-listener-alias="delete-client"]').forEach(el => bindGuard(el, 'click'));
            document.querySelectorAll('[data-listener-alias="reset-client"]').forEach(el => bindGuard(el, 'click'));
        })();
    </script>
@endpush
