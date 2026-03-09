@php
$lang ??= 'en';
	$routeName ??= '';
	$updateRoute ??= '#';
	$formId ??= 'bugstatus-update-form-unknown';
	$guardMsg ??= '';
	$bugStatusId ??= null;
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$bugStatusId = data_get($bug_status ?? null, 'id');
		$routeName = ViewsConstants::BUG_STT . '.update';
		$updateRoute = ($bugStatusId && Route::has($routeName))
			? (route($routeName, $bugStatusId) ?? '#')
			: (($bugStatusId && Route::has(Str::kebab($routeName)))
				? (route(Str::kebab($routeName), $bugStatusId) ?? '#')
				: '#');
		$formId = 'bugstatus-update-form-' . ($bugStatusId ?? 'unknown');
		$guardMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::BUG_STT,
			'bug_status_update_route_unavailable'
		) ?? 'Bug Status update route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in bug_status/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in bug_status/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in bug_status/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{{ Form::model($bug_status, [
    'route'            => [$updateRoute],
    'method'           => 'PUT',
    'id'               => $formId,
    'data-url'         => $updateRoute,
    'data-guard-msg'   => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('title', __('Bug Status Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, [
                    'class'    => VC::FM_CT,
                    'required' => 'required',
                ]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input
            type="button"
            value="{{ __('Cancel') }}"
            class="{{ VC::BT_LG }}"
            data-bs-dismiss="modal"
        >
        <input
            type="submit"
            value="{{ __('Update') }}"
            class="{{ VC::BT_PRM }}"
        >
    </div>
    <script defer>
        (() => {
            const form = document.getElementById('{{ $formId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', event => {
                try {
                    const action = form.getAttribute('action');
                    const url    = form.getAttribute('data-url');
                    if ((action && action !== '#') || (url && url !== '#')) return;
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
                    form.setAttribute('data-failed-route', 'true');
                } catch (e) {}
            });
        })();
    </script>
{{ Form::close() }}
