@php
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use App\Config\Constants\{
        ViewsConstants,
        StacksConstants
    };
    $lang = Utility::fetchUserLang();
    $createLangRoute = Route::has('languages.store')
        ? route('languages.store')
        : '#';
    $formId = 'language-create-form';
    $createLangMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::LNG,
        'language_store_route_unavailable'
    ) ?? 'Language create route is unavailable. Please contact technical support or your domain administrator.';
@endphp
{!! Collective\Html\FormFacade::open([
    'url'    => $createLangRoute,
    'method' => 'post',
    'id'     => $formId,
    'data-sv-localized' => 'true',
    'data-guard-msg'    => $createLangMsg,
]) !!}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Collective\Html\FormFacade::label('code', __('Language Code'), ['class' => 'form-label']) }}
            {{ Collective\Html\FormFacade::text('code', '', ['class' => 'form-control', 'required' => 'required']) }}
            @error('code')
                <span class="invalid-code" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            {{ Collective\Html\FormFacade::label('full_name', __('Language Name'), ['class' => 'form-label']) }}
            {{ Collective\Html\FormFacade::text('full_name', '', ['class' => 'form-control', 'required' => 'required']) }}
            @error('full_name')
                <span class="invalid-full_name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{!! Collective\Html\FormFacade::close() !!}
<script defer>
    (() => {
        const listenerAttr = 'data-language-create-listener-active';
        const form = document.getElementById('{{ $formId }}');
        if (!form || form.getAttribute(listenerAttr) === 'true') return;
        form.setAttribute(listenerAttr, 'true');

        form.addEventListener('submit', event => {
            try {
                const action = form.getAttribute('action');
                if (!action || action === '#') {
                    event.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
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
                }
            } catch (error) {}
        });

        const observer = new MutationObserver(() => {
            if (!document.getElementById('{{ $formId }}')) observer.disconnect();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    })();
</script>