@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
        ViewsConstants
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Custom Question for interview') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"{{ Route::has('dashboard') ? '' : ' aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Custom-Question') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create custom question')
            @php
                use Illuminate\Support\Facades\Route;
                use Illuminate\Support\Str;
                use App\Models\Utility;
                use App\Config\Constants\{
                    ViewsConstants,
                    StacksConstants,
                    ViewClassNamesConstants as VC
                };
            
                $lang                    = Utility::fetchUserLang();
                $createRouteName         = ViewsConstants::CST_QT . '.create';
                $createUrl               = Route::has($createRouteName)
                    ? route($createRouteName)
                    : (Route::has(Str::kebab($createRouteName))
                        ? route(Str::kebab($createRouteName))
                        : '#');
                $linkId                  = 'custom-question-create-btn';
                $guardMsg                = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::CST_QT,
                    'custom_question_create_route_unavailable'
                ) ?? 'Create Custom Question route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                id="{{ $linkId }}"
                href="#"
                data-url="{{ $createUrl }}"
                data-size="lg"
                data-ajax-popup="true"
                data-title="{{ __('Create New Custom Question') }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                data-guard-msg="{{ $guardMsg }}"
                {{ $createUrl === '#' ? 'aria-disabled="true"' : '' }}
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const btn = document.getElementById('{{ $linkId }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active', 'true');
            
                        btn.addEventListener('click', e => {
                            try {
                                const url = btn.getAttribute('data-url') || '#';
                                if (url !== '#') return; // valid route, proceed with AJAX popup
            
                                e.preventDefault();
                                const msg           = btn.getAttribute('data-guard-msg') || '# ERROR';
                                const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                let container       = document.getElementById('toast-container');
                                if (!container) {
                                    container       = document.createElement('div');
                                    container.id    = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bsLink && window.bootstrap) {
                                    const toastEl      = document.createElement('div');
                                    toastEl.className  = 'toast';
                                    toastEl.setAttribute('role','alert');
                                    toastEl.setAttribute('aria-live','assertive');
                                    toastEl.setAttribute('aria-atomic','true');
                                    const body         = document.createElement('div');
                                    body.className     = 'toast-body';
                                    body.textContent   = msg;
                                    toastEl.appendChild(body);
                                    container.appendChild(toastEl);
                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                } else {
                                    alert(msg);
                                }
                                btn.setAttribute('data-failed-route', 'true');
                            } catch (err) {}
                        });
                    })();
                </script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Question') }}</th>
                                    <th>{{ __('Is Required') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach($questions as $question)
                                    <tr>
                                        <td>{{ $question->question }}</td>
                                        <td>
                                            @if($question->is_required == 'yes')
                                                <span class="{{ VC::BDG }} bg-primary p-2 px-3 rounded">{{ \App\Models\CustomQuestion::$is_required[$question->is_required] }}</span>
                                            @else
                                                <span class="{{ VC::BDG }} bg-danger p-2 px-3 rounded">{{ \App\Models\CustomQuestion::$is_required[$question->is_required] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @can('edit custom question')
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    @php
                                                        $editRouteName      = ViewsConstants::CST_QT . '.edit';
                                                        $editUrl            = Route::has($editRouteName)
                                                            ? route($editRouteName, $question->id)
                                                            : (Route::has(Str::kebab($editRouteName))
                                                                ? route(Str::kebab($editRouteName), $question->id)
                                                                : '#');
                                                        $linkId             = 'custom-question-edit-link-' . $question->id;
                                                        $guardMsg           = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::CST_QT,
                                                            'custom_question_edit_route_unavailable'
                                                        ) ?? 'Edit Custom Question route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <a
                                                        id="{{ $linkId }}"
                                                        href="{{ $editUrl }}"
                                                        data-url="{{ $editUrl }}"
                                                        data-size="lg"
                                                        data-ajax-popup="true"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-title="{{ __('Edit Custom Question') }}"
                                                        data-guard-msg="{{ $guardMsg }}"
                                                        class="{{ VC::BT_SM_FL_CT }}"
                                                        {{ $editUrl === '#' ? 'aria-disabled="true"' : '' }}
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const link = document.getElementById('{{ $linkId }}');
                                                                if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                                link.setAttribute('data-listener-active', 'true');
                                                                link.addEventListener('click', event => {
                                                                    try {
                                                                        const url = link.getAttribute('data-url') ?? '#';
                                                                        if (url !== '#') return;
                                                                        event.preventDefault();
                                                                        const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                                                        link.setAttribute('data-failed-route', 'true');
                                                                    } catch (e) {}
                                                                });
                                                            })();
                                                        </script>
                                                    @endpush
                                                </div>
                                            @endcan
                                            @can('delete custom question')
                                                @php
                                                    $destroyRouteName        = ViewsConstants::CST_QT . '.destroy';
                                                    $destroyRouteKebab       = Str::kebab(ViewsConstants::CST_QT) . '.destroy';
                                                    $destroyUrl              = Route::has($destroyRouteName)
                                                        ? route($destroyRouteName, $question->id)
                                                        : (Route::has($destroyRouteKebab)
                                                            ? route($destroyRouteKebab, $question->id)
                                                            : '#');
                                                    $formId                  = 'delete-form-' . $question->id;
                                                    $linkId                  = 'delete-custom-question-link-' . $question->id;
                                                    $guardMsg                = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::CST_QT,
                                                        'custom_question_destroy_route_unavailable'
                                                    ) ?? 'Delete Custom Question route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }} {{ VC::MS2 }}">
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'route'  => [ViewsConstants::CST_QT . '.destroy', $question->id],
                                                        'id'     => $formId,
                                                    ]) !!}
                                                        <a
                                                            id="{{ $linkId }}"
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-url="{{ $destroyUrl }}"
                                                            data-guard-msg="{{ $guardMsg }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                        >
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            const link = document.getElementById('{{ $linkId }}');
                                                            if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                            link.setAttribute('data-listener-active', 'true');
                                                
                                                            link.addEventListener('click', event => {
                                                                try {
                                                                    const url = link.getAttribute('data-url') || '#';
                                                                    if (url !== '#') return;
                                                                    event.preventDefault();
                                                                    const msg           = link.getAttribute('data-guard-msg') || '# ERROR';
                                                                    const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                    let container       = document.getElementById('toast-container');
                                                                    if (!container) {
                                                                        container       = document.createElement('div');
                                                                        container.id    = 'toast-container';
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bsLink && window.bootstrap) {
                                                                        const toastEl      = document.createElement('div');
                                                                        toastEl.className  = 'toast';
                                                                        toastEl.setAttribute('role','alert');
                                                                        toastEl.setAttribute('aria-live','assertive');
                                                                        toastEl.setAttribute('aria-atomic','true');
                                                                        const body         = document.createElement('div');
                                                                        body.className     = 'toast-body';
                                                                        body.textContent   = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    link.setAttribute('data-failed-route', 'true');
                                                                } catch (e) {}
                                                            });
                                                        })();
                                                    </script>
                                                @endpush
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
