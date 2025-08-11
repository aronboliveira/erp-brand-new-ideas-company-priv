@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as C, StacksConstants};
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang = Utility::fetchUserLang();
    $storeRoute = Route::has(ViewsConstants::AWD_TP)
        ? route(ViewsConstants::AWD_TP)
        : Route::has(Str::kebab(ViewsConstants::AWD_TP))
            ? route(Str::kebab(ViewsConstants::AWD_TP))
            : '#';
    $formId = 'awardtype-store-form';
    $storeMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::AWD_TP,
        'award_type_store_route_unavailable'
    ) ?? 'Award Type store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Collective\Html\FormFacade::open([
    'url'               => $storeRoute,
    'method'            => 'post',
    'id'                => $formId,
    'data-url'          => $storeRoute,
    'data-sv-localized' => 'true',
    'data-guard-msg'    => $storeMsg,
]) }}
    <div class="modal-body">
        <div class="{{ C::RW }}">
            <div class="col-md-12">
                <div class="{{ C::FM_GB3 }}">
                    {{ Collective\Html\FormFacade::label('name', __('Name'), ['class'=>C::FM_LB]) }}<span class="text-danger">*</span>
                    {{ Collective\Html\FormFacade::text('name', null, ['class'=>C::FM_CT,'placeholder'=>__('Enter Award Type Name')]) }}
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ C::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ C::BT_PRM }}">{{ __('Create') }}</button>
    </div>
{{ Collective\Html\FormFacade::close() }}

@push(StacksConstants::ADM_SCRP_PG)
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
@endpush
