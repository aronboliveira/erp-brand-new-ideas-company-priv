@php
$lang ??= 'en';
	$fields ??= [];
	$routeName ??= '';
	$editRoute ??= '#';
	$formId ??= 'credit_note_edit_form_x';
	$guardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$routeName = ViewsConstants::INV . '.edit.credit.note';
		$creditNoteInvoice = data_get($creditNote ?? null, 'invoice', '');
		$creditNoteId = data_get($creditNote ?? null, 'id', '');
		$editRoute = (Route::has($routeName) && $creditNoteInvoice && $creditNoteId)
			? (route($routeName, [$creditNoteInvoice, $creditNoteId]) ?? '#')
			: '#';
		$formId = 'credit_note_edit_form_' . ($creditNoteId ?: 'x');
		$guardMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::INV,
			'edit_credit_note_route_unavailable'
		) ?? 'Edit credit note route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in credit_notes/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in credit_notes/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in credit_notes/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::model($creditNote, [
    'route'          => [$editRoute],
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $editRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            @foreach($fields as $f)
                <div class="{{ VC::FM_G }} col-md-{{ $f['cols'] }}">
                    {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}
                    @php
 $attrs = $f['attrs'];
@endphp
                    @if($f['type'] === 'textarea')
                        {{ Form::textarea($f['name'], null, $attrs) }}
                    @else
                        {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
    </div>
    <script src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script defer>
        (() => {
            const form = document.getElementById('{{ $formId }}');
            if (window.RouteGuard?.guardFormSubmit) {
                window.RouteGuard.guardFormSubmit(form);
            }
        })();
    </script>
{{ Form::close() }}
