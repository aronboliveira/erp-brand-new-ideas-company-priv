@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth,Route};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $profile = Utility::getFile('uploads/avatar/');
    //$profile=asset(Storage::url('uploads/avatar/'));
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Client')}}
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
    <li class="breadcrumb-item"><a href="{{route('clients.index')}}">{{__('Client')}}</a></li>
    <li class="breadcrumb-item">  {{ ucwords($client->name).__("'s Detail") }}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)

@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="col-xl-12">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Estimate') }}</th>
                                    <th>{{ __('Client') }}</th>
                                    <th>{{ __('Issue Date') }}</th>
                                    <th>{{ __('Value') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if(Auth::user()->type != 'client')
                                        <th width="250px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($estimations as $estimate)
                                    @php
                                        $showName     = ViewsConstants::EST . '.show';
                                        $showRoute    = Route::has($showName)
                                            ? route($showName, $estimate->id)
                                            : '#';
                                        $showGuardMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::EST,
                                            'estimation_show_route_unavailable'
                                        ) ?? 'Estimate view route is unavailable. Please contact technical support or your domain administrator.';
                                        $editName     = ViewsConstants::EST . '.edit';
                                        $editRoute    = Route::has($editName)
                                            ? route($editName, $estimate->id)
                                            : '#';
                                        $editGuardMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::EST,
                                            'estimation_edit_route_unavailable'
                                        ) ?? 'Estimate edit route is unavailable. Please contact technical support or your domain administrator.';
                                        $destroyName     = ViewsConstants::EST . '.destroy';
                                        $destroyRoute    = Route::has($destroyName)
                                            ? route($destroyName, $estimate->id)
                                            : '#';
                                        $destroyGuardMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::EST,
                                            'estimation_destroy_route_unavailable'
                                        ) ?? 'Estimate delete route is unavailable. Please contact technical support or your domain administrator.';
                                        $deleteFormId    = 'delete-form-' . $estimate->id;
                                    @endphp
                                    <tr>
                                        <td class="Id">
                                            @can('View Estimation')
                                                <a
                                                    href="#"
                                                    class="{{ VC::BT_OUTPM }}"
                                                    data-url="{{ $showRoute }}"
                                                    data-guard-msg="{{ $showGuardMsg }}"
                                                    data-listener-alias="view-estimate"
                                                >
                                                    <i class="{{ VC::TI_CC_PLS }}"></i>
                                                    {{$user?->estimateNumberFormat($estimate->estimation_id) }}
                                                </a>
                                            @else
                                                {{$user?->estimateNumberFormat($estimate->estimation_id) }}
                                            @endcan
                                        </td>
                                        <td>{{ $estimate->client->name }}</td>
                                        <td>{{$user?->dateFormat($estimate->issue_date) }}</td>
                                        <td>{{$user?->priceFormat($estimate->getTotal()) }}</td>
                                        <td>
                                            @php
                                                $statusClasses = [
                                                    0 => VC::BDG.' bg-primary',
                                                    1 => VC::BDG.' bg-danger',
                                                    2 => VC::BDG.' bg-warning',
                                                    3 => VC::BDG.' bg-success',
                                                    4 => VC::BDG.' bg-info',
                                                ];
                                                $label = \App\Models\Estimation::$statuses[$estimate->status] ?? '';
                                            @endphp
                                            <span class="{{ $statusClasses[$estimate->status] ?? (VC::BDG.' bg-secondary') }} p-2 px-3 rounded">
                                                {{ __($label) }}
                                            </span>
                                        </td>
                                        @if(Auth::user()->type != 'client')
                                            <td class="Action">
                                                @can('View Estimation')
                                                    <div class="{{ VC::ACT_BTN_WRN }}">
                                                        <a
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-url="{{ $showRoute }}"
                                                            data-guard-msg="{{ $showGuardMsg }}"
                                                            data-listener-alias="view-estimate"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('View') }}"
                                                        >
                                                            <i class="{{ VC::TI_EYE_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('Edit Estimation')
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-url="{{ $editRoute }}"
                                                            data-guard-msg="{{ $editGuardMsg }}"
                                                            data-listener-alias="edit-estimate"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Edit Estimation') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                        >
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('Delete Estimation')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Collective\Html\FormFacade::open([
                                                            'method'         => 'DELETE',
                                                            'route'          => [ViewsConstants::EST . '.destroy', $estimate->id],
                                                            'id'             => $deleteFormId,
                                                            'data-url'       => $destroyRoute,
                                                            'data-guard-msg' => $destroyGuardMsg,
                                                        ]) !!}
                                                        <a
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-listener-alias="delete-estimate"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
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
                    } catch (e) {}
                });
            };

            document.querySelectorAll('[data-listener-alias="view-estimate"]').forEach(el => bindGuard(el, 'click'));
            document.querySelectorAll('[data-listener-alias="edit-estimate"]').forEach(el => bindGuard(el, 'click'));
            document.querySelectorAll('[data-listener-alias="delete-estimate"]').forEach(el => bindGuard(el, 'click'));
        })();
    </script>
@endpush
