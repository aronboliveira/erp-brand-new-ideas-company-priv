@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Collective\Html\FormFacade as Form;
    $lang = Utility::fetchUserLang();
    $createRoute = Route::has(ViewsConstants::ALW_OPT.'.create')
        ? route(ViewsConstants::ALW_OPT.'.create')
        : Route::has(Str::kebab(ViewsConstants::ALW_OPT.'.create'))
            ? route(Str::kebab(ViewsConstants::ALW_OPT.'.create'))
            : '#';
    $createId = 'allowance-option-create-link';
    $createMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::ALW_OPT,
        'allowance_option_create_route_unavailable'
    ) ?? 'Create Allowance Option route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Allowance Option') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a
            href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
            {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}
        >
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Allowance Option') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ ViewClassNamesConstants::FEND }}">
        @can('create allowance option')
            <a
                id="{{ $createId }}"
                href="{{ $createRoute }}"
                data-url="{{ $createRoute }}"
                data-sv-localized="true"
                data-guard-msg="{{ $createMsg }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Allowance Option') }}"
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
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Allowance Option') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach($allowanceoptions as $option)
                                    @php
                                        $editRoute = Route::has(ViewsConstants::ALW_OPT.'.edit')
                                            ? route(ViewsConstants::ALW_OPT.'.edit', $option->id)
                                            : Route::has(Str::kebab(ViewsConstants::ALW_OPT.'.edit'))
                                                ? route(Str::kebab(ViewsConstants::ALW_OPT.'.edit'), $option->id)
                                                : '#';
                                        $editId = "allowance-option-edit-{$option->id}-link";
                                        $editMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::ALW_OPT,
                                            'allowance_option_edit_route_unavailable'
                                        ) ?? 'Edit Allowance Option route is unavailable. Please contact technical support or your domain administrator.';
                                        $destroyRoute = Route::has(ViewsConstants::ALW_OPT.'.destroy')
                                            ? route(ViewsConstants::ALW_OPT.'.destroy', $option->id)
                                            : Route::has(Str::kebab(ViewsConstants::ALW_OPT.'.destroy'))
                                                ? route(Str::kebab(ViewsConstants::ALW_OPT.'.destroy'), $option->id)
                                                : '#';
                                        $deleteId = "allowance-option-delete-{$option->id}-link";
                                        $deleteMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::ALW_OPT,
                                            'allowance_option_destroy_route_unavailable'
                                        ) ?? 'Delete Allowance Option route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <tr>
                                        <td>{{ $option->name }}</td>
                                        <td>
                                            @can('edit allowance option')
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                    <a
                                                        id="{{ $editId }}"
                                                        href="{{ $editRoute }}"
                                                        data-url="{{ $editRoute }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $editMsg }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Allowance Option') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_MX3 }} {{ ViewClassNamesConstants::AL_IT_CT }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete allowance option')
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'route'  => [ViewsConstants::ALW_OPT.'.destroy', $option->id],
                                                        'id'     => "delete-form-{$option->id}"
                                                    ]) !!}
                                                        <a
                                                            id="{{ $deleteId }}"
                                                            href="#"
                                                            class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                            data-url="{{ $destroyRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $deleteMsg }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $option->id }}').submit();"
                                                        >
                                                            <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
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
    <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
    ar: {
        guard_unavailable: 'هذا الإجراء غير متاح.'
    },
    da: {
        guard_unavailable: 'Denne handling er ikke tilgængelig.'
    },
    de: {
        guard_unavailable: 'Diese Aktion ist nicht verfügbar.'
    },
    en: {
        guard_unavailable: 'This action is unavailable.'
    },
    es: {
        guard_unavailable: 'Esta acción no está disponible.'
    },
    fr: {
        guard_unavailable: 'Cette action n’est pas disponible.'
    },
    he: {
        guard_unavailable: 'הפעולה הזו אינה זמינה.'
    },
    it: {
        guard_unavailable: 'Questa azione non è disponibile.'
    },
    ja: {
        guard_unavailable: 'この操作は利用できません。'
    },
    nl: {
        guard_unavailable: 'Deze actie is niet beschikbaar.'
    },
    pl: {
        guard_unavailable: 'Ta akcja jest niedostępna.'
    },
    pt: {
        guard_unavailable: 'Esta ação não está disponível.'
    },
    'pt-br': {
        guard_unavailable: 'Esta ação não está disponível.'
    },
    ru: {
        guard_unavailable: 'Это действие недоступно.'
    },
    tr: {
        guard_unavailable: 'Bu işlem kullanılamıyor.'
    },
    zh: {
        guard_unavailable: '此操作不可用。'
    }
    };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
 
          })();
    </script>
<script defer>
    (() => {
        const selector = '[data-sv-localized="true"]';
        const listenerAttr = 'data-guard-listener-active';
        document.querySelectorAll(selector).forEach(el => {
            if (el.getAttribute(listenerAttr) === 'true') return;
            el.setAttribute(listenerAttr, 'true');
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
                        form.setAttribute('data-failed-route', 'true');
                    }
                } catch {}
            });
            const observer = new MutationObserver(() => {
                if (!document.querySelector(selector)) observer.disconnect();
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    })();
</script>
