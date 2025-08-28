@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
        StacksConstants
    };
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;

    $lang          = Utility::fetchUserLang();
    $createRoute   = Route::has(ViewsConstants::AWD . '.create')
        ? route(ViewsConstants::AWD . '.create')
        : '#';
    $createMsg     = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::AWD,
        'award_create_route_unavailable'
    ) ?? 'Award create route is unavailable. Please contact technical support or your domain administrator.';
    $createBtnId   = 'award-create-button';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL, __('Manage Award'))

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Award') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ ViewClassNamesConstants::FEND }}">
        @can('create award')
            <a id="{{ $createBtnId }}"
               href="{{ $createRoute }}"
               data-url="{{ $createRoute }}"
               data-guard-msg="{{ $createMsg }}"
               data-size="lg"
               data-ajax-popup="true"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-title="{{ __('Create New Award') }}"
               class="{{ ViewClassNamesConstants::BT_SM_PM }}">
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="{{ ViewClassNamesConstants::C12 }}">
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="{{ ViewClassNamesConstants::CD }}-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    @role('company')
                                        <th>{{ __('Employee') }}</th>
                                    @endrole
                                    <th>{{ __('Award Type') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Gift') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if(Gate::check('edit award') || Gate::check('delete award'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach($awards as $award)
                                    @php
                                        $editRoute   = Route::has(ViewsConstants::AWD . '.edit')
                                            ? route(ViewsConstants::AWD . '.edit', $award->id)
                                            : '#';
                                        $editMsg     = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::AWD,
                                            'award_edit_route_unavailable'
                                        ) ?? 'Award edit route is unavailable. Please contact technical support or your domain administrator.';
                                        $editBtnId   = 'award-edit-' . $award->id;
                                        $destroyRoute = Route::has(ViewsConstants::AWD . '.destroy')
                                            ? route(ViewsConstants::AWD . '.destroy', $award->id)
                                            : '#';
                                        $destroyMsg   = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::AWD,
                                            'award_destroy_route_unavailable'
                                        ) ?? 'Award destroy route is unavailable. Please contact technical support or your domain administrator.';
                                        $deleteBtnId  = 'award-delete-' . $award->id;
                                    @endphp
                                    <tr>
                                        @role('company')
                                            <td>{{ $award->employee->name ?? '' }}</td>
                                        @endrole
                                        <td>{{ $award->awardType->name ?? '' }}</td>
                                        <td>{{ auth()->user()?->dateFormat($award->date) }}</td>
                                        <td>{{ $award->gift }}</td>
                                        <td>{{ $award->description }}</td>
                                        @if(Gate::check('edit award') || Gate::check('delete award'))
                                            <td>
                                                @can('edit award')
                                                    <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                        <a id="{{ $editBtnId }}"
                                                           href="{{ $editRoute }}"
                                                           data-url="{{ $editRoute }}"
                                                           data-guard-msg="{{ $editMsg }}"
                                                           data-size="lg"
                                                           data-ajax-popup="true"
                                                           data-title="{{ __('Edit Award') }}"
                                                           class="{{ ViewClassNamesConstants::BT_SM_CT }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}">
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete award')
                                                    <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open([
                                                            'url'            => $destroyRoute,
                                                            'method'         => 'DELETE',
                                                            'id'             => 'delete-form-' . $award->id,
                                                            'data-url'       => $destroyRoute,
                                                            'data-guard-msg' => $destroyMsg,
                                                        ]) !!}
                                                        <a id="{{ $deleteBtnId }}"
                                                           href="#"
                                                           class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('delete-form-{{ $award->id }}').submit();">
                                                            <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
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
            const attachGuard = (el, eventType) => {
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener(eventType, event => {
                    try {
                        const href = el.tagName === 'A' ? el.getAttribute('href') : null;
                        const url  = el.getAttribute('data-url');
                        if ((href && href !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
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
                    } catch (e) {}
                });
            };
            attachGuard(document.getElementById('{{ $createBtnId }}'), 'click');
            document.querySelectorAll('[id^="award-edit-"]').forEach(el => {
                attachGuard(el, 'click');
            });
            document.querySelectorAll('[id^="award-delete-"]').forEach(el => {
                attachGuard(el, 'click');
            });
        })();
    </script>
@endpush
