@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp

@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Holiday') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Holiday') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @can('create holiday')
        @php
            $calendarRoute = Route::has(ViewsConstants::HLD.'.calendar')
                ? route(ViewsConstants::HLD.'.calendar')
                : '#';
            $calendarId = 'holiday-calendar-link';
            $calendarMsg = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::HLD,
                'holiday_calendar_route_unavailable'
            ) ?? 'Calendar view route is unavailable. Please contact technical support or your domain administrator.';

            $createRoute = Route::has(ViewsConstants::HLD.'.create')
                ? route(ViewsConstants::HLD.'.create')
                : '#';
            $createId = 'holiday-create-link';
            $createMsg = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::HLD,
                'holiday_create_route_unavailable'
            ) ?? 'Create holiday route is unavailable. Please contact technical support or your domain administrator.';

            $guardIds = [$calendarId, $createId];
        @endphp
        <div class="{{ ViewClassNamesConstants::FEND }}">
            <a
                id="{{ $calendarId }}"
                href="{{ $calendarRoute }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }}"
                data-url="{{ $calendarRoute }}"
                data-sv-localized="true"
                data-guard-msg="{{ $calendarMsg }}"
                data-bs-toggle="tooltip"
                title="{{ __('calendar View') }}"
            >
                <i class="{{ ViewClassNamesConstants::TI_CLD }}"></i>
            </a>
            <a
                id="{{ $createId }}"
                href="{{ $createRoute }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }}"
                data-url="{{ $createRoute }}"
                data-sv-localized="true"
                data-guard-msg="{{ $createMsg }}"
                data-size="lg"
                data-ajax-popup="true"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                data-title="{{ __('Create New Holiday') }}"
            >
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>
        </div>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const ids = {!! json_encode($guardIds) !!};
                    const flagAttr = 'data-listener-active';
                    ids.forEach(id => {
                        const el = document.getElementById(id);
                        if (!el || el.getAttribute(flagAttr) === 'true') return;
                        el.setAttribute(flagAttr, 'true');
                        el.addEventListener('click', event => {
                            try {
                                const url = el.getAttribute('data-url');
                                const href = el.href;
                                if ((!url || url === '#') && (!href || href === '#')) {
                                    event.preventDefault();
                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                    let container = document.getElementById('toast-container');
                                    if (!container) {
                                        container = document.createElement('div');
                                        container.id = 'toast-container';
                                        document.body.appendChild(container);
                                    }
                                    if (bootstrapLink && window.bootstrap) {
                                        const toastEl = document.createElement('div');
                                        toastEl.className = 'toast';
                                        toastEl.setAttribute('role', 'alert');
                                        toastEl.setAttribute('aria-live', 'assertive');
                                        toastEl.setAttribute('aria-atomic', 'true');
                                        const body = document.createElement('div');
                                        body.className = 'toast-body';
                                        body.textContent = msg;
                                        toastEl.appendChild(body);
                                        container.appendChild(toastEl);
                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                    } else {
                                        alert(msg);
                                    }
                                    el.setAttribute('data-failed-route', 'true');
                                }
                            } catch {}
                        });
                        const observer = new MutationObserver(() => {
                            if (!document.getElementById(id)) observer.disconnect();
                        });
                        observer.observe(document.body, { childList: true, subtree: true });
                    });
                })();
            </script>
        @endpush
    @endcan
@endsection

@section(YieldingConstants::ADM_CTT)
    @can('create holiday')
        <div class="{{ ViewClassNamesConstants::RW }}">
            <div class="{{ ViewClassNamesConstants::CS12 }}">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="{{ ViewClassNamesConstants::CD }}">
                        <div class="card-body">
                            {{ Collective\Html\FormFacade::open([
                                'route' => [ViewsConstants::HLD.'.calendar'],
                                'method' => 'get',
                                'id'     => 'holiday_filter'
                            ]) }}
                            <div class="{{ ViewClassNamesConstants::R_ALC_JCE }}">
                                <div class="col-xl-10">
                                    <div class="{{ ViewClassNamesConstants::RW }}">
                                        @foreach(range(1,2) as $i)
                                            <div class="{{ ViewClassNamesConstants::CL_POS3 }}">
                                                <div class="btn-box"></div>
                                            </div>
                                        @endforeach
                                        <div class="{{ ViewClassNamesConstants::CL_POS3 }}">
                                            <div class="btn-box">
                                                {{ Collective\Html\FormFacade::label(
                                                    'start_date',
                                                    __('Start Date'),
                                                    ['class' => 'form-label']
                                                ) }}
                                                {{ Collective\Html\FormFacade::date(
                                                    'start_date',
                                                    request('start_date'),
                                                    ['class' => 'month-btn form-control']
                                                ) }}
                                            </div>
                                        </div>
                                        <div class="{{ ViewClassNamesConstants::CL_POS3 }}">
                                            <div class="btn-box">
                                                {{ Collective\Html\FormFacade::label(
                                                    'end_date',
                                                    __('End Date'),
                                                    ['class' => 'form-label']
                                                ) }}
                                                {{ Collective\Html\FormFacade::date(
                                                    'end_date',
                                                    request('end_date'),
                                                    ['class' => 'month-btn form-control']
                                                ) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ ViewClassNamesConstants::C_AT }}">
                                    <div class="{{ ViewClassNamesConstants::RW }}">
                                        <div class="{{ ViewClassNamesConstants::C_AT }} {{ ViewClassNamesConstants::MT4 }}">
                                            <a href="#"
                                               class="{{ ViewClassNamesConstants::BT_SM_PM }}"
                                               onclick="document.getElementById('holiday_filter').submit(); return false;"
                                               data-bs-toggle="tooltip"
                                               title="{{ __('Apply') }}">
                                                <span class="btn-inner--icon">
                                                    <i class="{{ ViewClassNamesConstants::TI_SRC }}"></i>
                                                </span>
                                            </a>
                                            <a href="{{ route(ViewsConstants::HLD.'.calendar') }}"
                                               class="{{ ViewClassNamesConstants::BT_SM_DG }}"
                                               data-bs-toggle="tooltip"
                                               title="{{ __('Reset') }}">
                                                <span class="btn-inner--icon">
                                                    <i class="{{ ViewClassNamesConstants::TI_TRS_OFF }}"></i>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{ Collective\Html\FormFacade::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    <div class="{{ ViewClassNamesConstants::RW }} {{ ViewClassNamesConstants::MT1 }}">
        <div class="col-md-12">
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Occasion') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    @if(Gate::check('edit holiday') || Gate::check('delete holiday'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach ($holidays as $holiday)
                                    <tr>
                                        <td>{{ $holiday->occasion }}</td>
                                        <td>{{ $user?->dateFormat($holiday->date) }}</td>
                                        <td>{{ $user?->dateFormat($holiday->end_date) }}</td>
                                        @if(Gate::check('edit holiday') || Gate::check('delete holiday'))
                                            <td class="Action">
                                                <span>
                                                    @can('edit holiday')
                                                        <div class="{{ ViewClassNamesConstants::ACT_BTN }} {{ ViewClassNamesConstants::BG_P }} {{ ViewClassNamesConstants::MS2 }}">
                                                            <a href="#"
                                                               class="{{ ViewClassNamesConstants::BT_SM_CT }}"
                                                               data-url="{{ route(ViewsConstants::HLD.'.edit', $holiday->id) }}"
                                                               data-ajax-popup="true"
                                                               data-title="{{ __('Edit Holiday') }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Edit') }}">
                                                                <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete holiday')
                                                        <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                            {!! Collective\Html\FormFacade::open([
                                                                'method' => 'DELETE',
                                                                'route'  => [ViewsConstants::HLD.'.destroy', $holiday->id],
                                                                'id'     => 'delete-form-' . $holiday->id
                                                            ]) !!}
                                                                <a href="#"
                                                                   class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Delete') }}"
                                                                   data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                   data-confirm-yes="document.getElementById('delete-form-{{$holiday->id}}').submit();">
                                                                    <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
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
