@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
        StacksConstants
    };

    $lang = Utility::fetchUserLang();

    // create route guard
    $createName     = ViewsConstants::CPL . '.create';
    $createRoute    = Route::has($createName)
        ? route($createName)
        : '#';
    $createGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPL,
        'complaint_create_route_unavailable'
    ) ?? 'Create Complaint route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Complain') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled=\"true\"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Complain') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create complaint')
            <a
                href="#"
                id="createComplaintBtn"
                data-url="{{ $createRoute }}"
                data-guard-msg="{{ $createGuardMsg }}"
                data-listener-alias="create-complaint"
                data-ajax-popup="true"
                data-title="{{ __('Create New Complaint') }}"
                data-size="lg"
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
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD }}-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Complaint From') }}</th>
                                    <th>{{ __('Complaint Against') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Complaint Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if(Gate::check('edit complaint') || Gate::check('delete complaint'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach ($complaints as $complaint)
                                    @php
                                        $editName     = ViewsConstants::CPL . '.edit';
                                        $editRoute    = Route::has($editName)
                                            ? route($editName, $complaint->id)
                                            : '#';
                                        $editGuardMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::CPL,
                                            'complaint_edit_route_unavailable'
                                        ) ?? 'Edit Complaint route is unavailable. Please contact technical support or your domain administrator.';
                                        $destroyName     = ViewsConstants::CPL . '.destroy';
                                        $destroyRoute    = Route::has($destroyName)
                                            ? route($destroyName, $complaint->id)
                                            : '#';
                                        $destroyGuardMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::CPL,
                                            'complaint_destroy_route_unavailable'
                                        ) ?? 'Delete Complaint route is unavailable. Please contact technical support or your domain administrator.';
                                        $deleteFormId    = 'delete-form-' . $complaint->id;
                                    @endphp
                                    <tr>
                                        <td>{{ $complaint->complaintFrom?->name ?? '' }}</td>
                                        <td>{{ $complaint->complaintAgainst?->name ?? '' }}</td>
                                        <td>{{ $complaint->title }}</td>
                                        <td>{{ \Auth::user()->dateFormat($complaint->complaint_date) }}</td>
                                        <td>{{ $complaint->description }}</td>
                                        @if(Gate::check('edit complaint') || Gate::check('delete complaint'))
                                            <td>
                                                @can('edit complaint')
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a
                                                            href="#"
                                                            id="editComplaintBtn_{{ $complaint->id }}"
                                                            data-url="{{ $editRoute }}"
                                                            data-guard-msg="{{ $editGuardMsg }}"
                                                            data-listener-alias="edit-complaint"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Edit Complaint') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            class="{{ VC::DFL_IL_VC }}"
                                                        >
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('delete complaint')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Collective\Html\FormFacade::open([
                                                            'method'         => 'DELETE',
                                                            'route'          => [ViewsConstants::CPL . '.destroy', $complaint->id],
                                                            'id'             => $deleteFormId,
                                                            'data-url'       => $destroyRoute,
                                                            'data-guard-msg' => $destroyGuardMsg,
                                                        ]) !!}
                                                            <a
                                                                href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-listener-alias="delete-complaint"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
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

            bindGuard(document.getElementById('createComplaintBtn'), 'click');
            document.querySelectorAll('[data-listener-alias="edit-complaint"]').forEach(el => bindGuard(el, 'click'));
            document.querySelectorAll('[data-listener-alias="delete-complaint"]').forEach(el => bindGuard(el, 'click'));
        })();
    </script>
@endpush
