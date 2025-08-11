@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;

    $lang = Utility::fetchUserLang();
    $createRoute = Route::has(ViewsConstants::AWD_TP.'.create')
        ? route(ViewsConstants::AWD_TP.'.create')
        : Route::has(Str::kebab(ViewsConstants::AWD_TP.'.create'))
            ? route(Str::kebab(ViewsConstants::AWD_TP.'.create'))
            : '#';
    $createId = 'awardtype-create-link';
    $createMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::AWD_TP,
        'award_type_create_route_unavailable'
    ) ?? 'Create Award Type route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL, __('Manage Award Type'))

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Award Type') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ ViewClassNamesConstants::FEND }}">
        @can('create award type')
            <a
                id="{{ $createId }}"
                href="#"
                data-url="{{ $createRoute }}"
                data-sv-localized="true"
                data-guard-msg="{{ $createMsg }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Award Type') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            >
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="{{ ViewClassNamesConstants::CL3 }}">
            @include('layouts.hrm_setup')
        </div>
        <div class="{{ ViewClassNamesConstants::CL9 }}">
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="{{ ViewClassNamesConstants::CD }}-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Award Type') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach($awardtypes as $awardtype)
                                    @php
                                        $editRoute = Route::has(ViewsConstants::AWD_TP.'.edit')
                                            ? route(ViewsConstants::AWD_TP.'.edit', $awardtype->id)
                                            : Route::has(Str::kebab(ViewsConstants::AWD_TP.'.edit'))
                                                ? route(Str::kebab(ViewsConstants::AWD_TP.'.edit'), $awardtype->id)
                                                : '#';
                                        $editId = 'awardtype-edit-' . $awardtype->id . '-link';
                                        $editMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::AWD_TP,
                                            'award_type_edit_route_unavailable'
                                        ) ?? 'award_type_edit_route_unavailable';

                                        $deleteRoute = Route::has(ViewsConstants::AWD_TP.'.destroy')
                                            ? route(ViewsConstants::AWD_TP.'.destroy', $awardtype->id)
                                            : Route::has(Str::kebab(ViewsConstants::AWD_TP.'.destroy'))
                                                ? route(Str::kebab(ViewsConstants::AWD_TP.'.destroy'), $awardtype->id)
                                                : '#';
                                        $deleteId = 'awardtype-delete-' . $awardtype->id . '-link';
                                        $deleteMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::AWD_TP,
                                            'award_type_destroy_route_unavailable'
                                        ) ?? 'Delete Award Type route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <tr>
                                        <td>{{ $awardtype->name }}</td>
                                        <td>
                                            @can('edit award type')
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                    <a
                                                        id="{{ $editId }}"
                                                        href="#"
                                                        data-url="{{ $editRoute }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $editMsg }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Award Type') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_CT }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete award type')
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method'=>'DELETE',
                                                        'route'=>[ViewsConstants::AWD_TP.'.destroy',$awardtype->id],
                                                        'id'=>'delete-form-'.$awardtype->id
                                                    ]) !!}
                                                        <a
                                                            id="{{ $deleteId }}"
                                                            href="#"
                                                            class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                            data-url="{{ $deleteRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $deleteMsg }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{$awardtype->id}}').submit();"
                                                        >
                                                            <i class="ti ti-trash {{ ViewClassNamesConstants::TXT_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
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
            const ids = [
                '{{ $createId ?? '' }}',
                @foreach($awardtypes as $awardtype)
                    'awardtype-edit-{{ $awardtype->id }}-link',
                    'awardtype-delete-{{ $awardtype->id }}-link',
                @endforeach
            ].filter(Boolean);
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
