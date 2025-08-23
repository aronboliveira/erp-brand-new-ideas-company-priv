@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
    $updateRoute = Route::has(ViewsConstants::ALW.'.update')
        ? route(ViewsConstants::ALW.'.update', $allowance->id)
        : '#';
    $formId = 'allowance-update-form';
    $updateMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::ALW,
        'allowance_update_route_unavailable'
    ) ?? 'Allowance update route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::model($allowance, [
    'route'             => [ViewsConstants::ALW.'.update', $allowance->id],
    'method'            => 'PUT',
    'id'                => $formId,
    'data-url'          => $updateRoute,
    'data-sv-localized' => 'true',
    'data-guard-msg'    => $updateMsg,
]) }}
    <div class="modal-body">
        <div class="card-body p-0">
            <div class="{{ ViewClassNamesConstants::RW }}">
                <div class="{{ ViewClassNamesConstants::FM_G }} {{ ViewClassNamesConstants::CM6 }}">
                    {{ Form::label('allowance_option', __('Allowance Options'), ['class' => ViewClassNamesConstants::FM_LB]) }}<span class="text-danger">*</span>
                    {{ Form::select('allowance_option', $allowance_options, null, ['class' => ViewClassNamesConstants::FM_CT_SL, 'required']) }}
                </div>
                <div class="{{ ViewClassNamesConstants::FM_G }} {{ ViewClassNamesConstants::CM6 }}">
                    {{ Form::label('title', __('Title'), ['class' => ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => ViewClassNamesConstants::FM_CT, 'required']) }}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::RW }}">
                <div class="{{ ViewClassNamesConstants::FM_G }} {{ ViewClassNamesConstants::CM6 }}">
                    {{ Form::label('type', __('Type'), ['class' => ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::select('type', $Allowancetypes, null, ['class' => ViewClassNamesConstants::FM_CT_SL . ' amount_type', 'required']) }}
                </div>
                <div class="{{ ViewClassNamesConstants::FM_G }} {{ ViewClassNamesConstants::CM6 }}">
                    {{ Form::label('amount', __('Amount'), ['class' => ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::number('amount', null, ['class' => ViewClassNamesConstants::FM_CT, 'required', 'step' => '0.01']) }}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ ViewClassNamesConstants::BT_PRM }}">
    </div>
{{ Form::close() }}
    <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
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
        const form = document.getElementById('{{ $formId }}');
        if (!form || form.getAttribute('data-listener-active') === 'true') return;
        form.setAttribute('data-listener-active', 'true');
        form.addEventListener('submit', event => {
            try {
                const url    = form.getAttribute('data-url');
                const action = form.getAttribute('action');
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
