@php 
    use App\Config\Constants\{ViewClassNamesConstants, ViewsConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form; 
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
    $storeRoute = Route::has(ViewsConstants::ALW_OPT)
        ? route(ViewsConstants::ALW_OPT)
        : '#';
    $formId = 'allowance-option-store-form';
    $storeMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::ALW_OPT,
        'allowance_option_store_route_unavailable'
    ) ?? 'Allowance option store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'url'              => $storeRoute,
    'method'           => 'post',
    'id'               => $formId,
    'data-url'         => $storeRoute,
    'data-sv-localized'=> 'true',
    'data-guard-msg'   => $storeMsg,
]) }}
    <div class="modal-body">
        <div class="{{ ViewClassNamesConstants::RW }}">
            <div class="{{ ViewClassNamesConstants::C12 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('name', __('Name'), ['class'=>ViewClassNamesConstants::FM_LB]) }}<span class="text-danger">*</span>
                    {{ Form::text('name', null, ['class'=>ViewClassNamesConstants::FM_CT, 'placeholder'=>__('Enter Allowance option Name')]) }}
                    @error('name')
                        <span class="invalid-name" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ ViewClassNamesConstants::BT_PRM }}">
    </div>
{{ Form::close() }}
<script>
    window.translations = {
    ar: {
        form_submit_unavailable: 'إرسال النموذج غير متاح.'
    },
    da: {
        form_submit_unavailable: 'Indsendelse af formularen er ikke tilgængelig.'
    },
    de: {
        form_submit_unavailable: 'Formularübermittlung ist nicht verfügbar.'
    },
    en: {
        form_submit_unavailable: 'Form submission is unavailable.'
    },
    es: {
        form_submit_unavailable: 'El envío del formulario no está disponible.'
    },
    fr: {
        form_submit_unavailable: 'La soumission du formulaire n’est pas disponible.'
    },
    he: {
        form_submit_unavailable: 'שליחת הטופס אינה זמינה.'
    },
    it: {
        form_submit_unavailable: 'Invio del modulo non disponibile.'
    },
    ja: {
        form_submit_unavailable: 'フォームの送信は利用できません。'
    },
    nl: {
        form_submit_unavailable: 'Formulierverzending is niet beschikbaar.'
    },
    pl: {
        form_submit_unavailable: 'Przesyłanie formularza jest niedostępne.'
    },
    pt: {
        form_submit_unavailable: 'Envio do formulário indisponível.'
    },
    'pt-br': {
        form_submit_unavailable: 'Envio do formulário indisponível.'
    },
    ru: {
        form_submit_unavailable: 'Отправка формы недоступна.'
    },
    tr: {
        form_submit_unavailable: 'Form gönderimi kullanılamıyor.'
    },
    zh: {
        form_submit_unavailable: '表单提交不可用。'
    }
    };
</script>
<script defer>
    (() => {
        const form = document.getElementById('{{ $formId }}');
        if (!form || form.getAttribute('data-listener-active') === 'true') return;
        form.setAttribute('data-listener-active', 'true');
        form.addEventListener('submit', event => {
            try {
                const action = form.getAttribute('action');
                const url    = form.getAttribute('data-url');
                if ((!action || action === '#') && (!url || url === '#')) {
                    event.preventDefault();
                    const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                    let container       = document.getElementById('toast-container');
                    if (!container) {
                        container       = document.createElement('div');
                        container.id    = 'toast-container';
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
            if (!document.getElementById('{{ $formId }}')) observer.disconnect();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    })();
</script>
