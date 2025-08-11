@php
    use App\Config\Constants\{
        ViewsConstants,
        ViewClassNamesConstants as VC,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
    $namespace       = ViewsConstants::DL;
    $routeName       = "{$namespace}.discussion.store";
    $hasStoreRoute   = Route::has($routeName);
    $storeGuardMsg   = Utility::fetchLinkMessage(
        $lang,
        $namespace,
        'discussion_store_route_unavailable'
    ) ?? 'Discussion store route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@if($hasStoreRoute)
    {{ Form::model($deal, [
        'route'           => [$routeName, $deal->id],
        'method'          => 'POST',
        'id'              => 'discussion-store-form',
        'data-guard-msg'  => $storeGuardMsg
    ]) }}
@else
    {{ Form::model($deal, [
        'url'             => '#',
        'method'          => 'POST',
        'id'              => 'discussion-store-form',
        'data-guard-msg'  => $storeGuardMsg
    ]) }}
@endif
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('comment', __('Message'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('comment', null, ['class' => VC::FM_CT]) }}
            </div>
        </div>
    </div>
    <div class="{{ VC::DFL }} modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
{{ Form::close() }}
@push(StacksConstants::ADM_SCRP_PG)
    <script defer>
        (() => {
            const form = document.getElementById('discussion-store-form');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', e => {
                try {
                    const action = form.getAttribute('action') ?? form.getAttribute('data-url') ?? '#';
                    if (action !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                    const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                    let container = document.getElementById('toast-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'toast-container';
                        document.body.appendChild(container);
                    }
                    if (bs) {
                        const toast = document.createElement('div');
                        toast.className = 'toast';
                        toast.setAttribute('role','alert');
                        toast.setAttribute('aria-live','assertive');
                        toast.setAttribute('aria-atomic','true');
                        const body = document.createElement('div');
                        body.className = 'toast-body';
                        body.textContent = msg;
                        toast.appendChild(body);
                        container.appendChild(toast);
                        bootstrap.Toast.getOrCreateInstance(toast).show();
                    } else {
                        alert(msg);
                    }
                    form.setAttribute('data-failed-route', 'true');
                } catch {}
            });
        })();
    </script>
@endpush
