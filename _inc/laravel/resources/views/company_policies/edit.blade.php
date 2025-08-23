@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{
        ViewsConstants,
        PlansConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;

    $lang        = Utility::fetchUserLang();
    $routeName   = ViewsConstants::CPN_PL . '.update';
    $updateRoute = Route::has($routeName)
        ? route($routeName, $companyPolicy->id)
        : (Route::has(Str::kebab($routeName))
            ? route(Str::kebab($routeName), $companyPolicy->id)
            : '#');
    $formId      = 'companyPolicyUpdateForm_' . $companyPolicy->id;
    $guardMsg    = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPN_PL,
        'company_policy_update_route_unavailable'
    ) ?? 'Company Policy update route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::model($companyPolicy, [
    'route'          => [ViewsConstants::CPN_PL.'.update', $companyPolicy->id],
    'method'         => 'PUT',
    'enctype'        => 'multipart/form-data',
    'id'             => $formId,
    'data-url'       => $updateRoute,
    'data-guard-msg' => $guardMsg,
]) }}
<div class="modal-body">
    @php $plan = Utility::getChatGPTSettings(); @endphp
    @if($plan?->{PlansConstants::COL_GPT} == 1)
        <div class="{{ VC::FEND }}">
            <a href="#"
               data-size="md"
               class="{{ VC::BT_PRM }} {{ VC::BT_LG }} btn-icon btn-sm"
               data-ajax-popup-over="true"
               data-url="{{ route('generate',['company policy']) }}"
               data-bs-placement="top"
               data-title="{{ __('Generate content with AI') }}">
                <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
            </a>
        </div>
    @endif

    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
            {{ Form::label('branch', __('Branch'), ['class' => VC::FM_LB]) }}
            {{ Form::select('branch', $branch, null, ['class' => VC::FM_CT . ' select','required'=>'required']) }}
        </div>
        <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
            {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
            {{ Form::text('title', null, ['class' => VC::FM_CT,'required'=>'required']) }}
        </div>
        <div class="{{ VC::C12 }} {{ VC::FM_G }}">
            {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
            {{ Form::textarea('description', null, ['class' => VC::FM_CT]) }}
        </div>
        <div class="{{ VC::C12 }} {{ VC::FM_G }}">
            {{ Form::label('attachment', __('Attachment'), ['class' => VC::FM_LB]) }}
            <div class="choose-file {{ VC::FM_G }}">
                <label for="attachment" class="{{ VC::FM_LB }}">
                    @php $policyPath = Utility::getFile('uploads/companyPolicy/'); @endphp
                    <input type="file"
                           class="{{ VC::FM_CT }}"
                           name="attachment"
                           id="attachment">
                    <img id="preview"
                         width="25%"
                         class="mt-3"
                         src="{{ $companyPolicy->attachment ? $policyPath.$companyPolicy->attachment : $policyPath.'default.png' }}" />
                </label>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
</div>
{{ Form::close() }}
    <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
      ar: {
        image_preview_failed: 'فشل عرض المعاينة.'
      },
      da: {
        image_preview_failed: 'Kunne ikke vise forhåndsvisning.'
      },
      de: {
        image_preview_failed: 'Vorschau konnte nicht angezeigt werden.'
      },
      en: {
        image_preview_failed: 'Failed to preview image.'
      },
      es: {
        image_preview_failed: 'Error al mostrar la vista previa.'
      },
      fr: {
        image_preview_failed: 'Échec de l’affichage de l’aperçu.'
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
    const ERR_KEY = 'image_preview_failed';
    const ATTACH_SELECTOR = '#attachment';
    const IMAGE_SELECTOR  = '#image';
    const attachEl = document.querySelector(ATTACH_SELECTOR);
    const imageEl  = document.querySelector(IMAGE_SELECTOR);
    if (!attachEl || !imageEl) return;

    const showError = msg => {
        const hasBs = window.bootstrap && typeof bootstrap.Toast === 'function';
        if (hasBs) {
        const toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center text-white bg-danger border-0';
        toastEl.setAttribute('role','alert');
        toastEl.innerHTML = `
            <div class="d-flex">
            <div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
            </div>`;
        document.body.append(toastEl);
        new bootstrap.Toast(toastEl).show();
        } else {
        alert(msg);
        }
    };

    const handler = e => {
        try {
        const file = e.target.files?.[0];
        if (!file) return;
        imageEl.src = URL.createObjectURL(file);
        } catch {
        let lang = (sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
            .toLowerCase().replace(/_/g,'-');
        lang = lang === 'pt-br' ? lang : lang.slice(0,2);
        const msg = window.translations?.[lang]?.[ERR_KEY]
                || window.translations?.['en']?.[ERR_KEY]
                || '# ERROR';
        showError(msg);
        }
    };

    attachEl.addEventListener('change', handler, false);

    const mo = new MutationObserver((_, obs) => {
        if (!document.body.contains(attachEl)) {
        attachEl.removeEventListener('change', handler);
        obs.disconnect();
        }
    });
    mo.observe(document.body, { childList: true, subtree: true });
    })();
</script>




