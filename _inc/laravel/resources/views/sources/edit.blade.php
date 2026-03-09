@php
$lang ??= 'en';
	$sourceId ??= '';
	$sourceUpdateBaseName ??= '';
	$sourceUpdateKebabName ??= '';
	$sourceUpdateResolvedName ??= null;
	$sourceUpdateRouteArray ??= ['#'];
	$sourceUpdateUrl ??= '#';
	$sourceUpdateGuardMsg ??= '';
	$sourceUpdateFormId ??= 'source-update-form-x';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$sourceId = data_get($source ?? null, 'id', '');
		$sourceUpdateBaseName = ViewsConstants::SRC . '.update';
		$sourceUpdateKebabName = Str::kebab($sourceUpdateBaseName);
		$sourceUpdateResolvedName = Route::has($sourceUpdateBaseName)
			? $sourceUpdateBaseName
			: (Route::has($sourceUpdateKebabName) ? $sourceUpdateKebabName : null);
		$sourceUpdateRouteArray = ($sourceUpdateResolvedName && $sourceId) ? [$sourceUpdateResolvedName, $sourceId] : ['#'];
		$sourceUpdateUrl = ($sourceUpdateResolvedName && $sourceId) ? (route($sourceUpdateResolvedName, $sourceId) ?? '#') : '#';
		$sourceUpdateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::SRC, 'source_update_route_unavailable') ?? 'Source update route is unavailable. Please contact technical support or your domain administrator.';
		$sourceUpdateFormId = 'source-update-form-' . ($sourceId ?: 'x');
	} catch (\Error $e) {
		Log::error('Error in sources/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in sources/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in sources/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{!! Form::model($source, [
    'route'          => $sourceUpdateRouteArray,
    'method'         => 'PUT',
    'id'             => $sourceUpdateFormId,
    'data-url'       => $sourceUpdateUrl,
    'data-guard-msg' => $sourceUpdateGuardMsg
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Source Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
{!! Form::close() !!}
@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('{{ $sourceUpdateFormId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', e => {
                try {
                    const url = form.getAttribute('data-url') || '#';
                    const action = form.getAttribute('action') || '#';
                    if (url !== '#' || action !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                    const RG = window.RouteGuard || {};
                    (RG.showToast || (m => alert(m)))(msg);
                    form.setAttribute('data-failed-route', 'true');
                } catch (err) {}
            });
        })();
    </script>
@endpush
