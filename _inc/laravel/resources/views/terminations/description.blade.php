@php
	try {
$desc = data_get($termination ?? null, 'description');
		$desc = isset($desc) && !empty($desc) ? $desc : __('No description available');
	} catch (\Throwable $e) {
		\Log::error('terminations/description — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp
<div class="modal-body">
	<div class="{{ VC::FM_G }}">
		<label class="{{ VC::FM_LB }}" for="termination-description">{{ __('Description') }}</label>
		<textarea class="{{ VC::FM_CT }}" id="termination-description" rows="10" readonly>{{ $desc }}</textarea>
	</div>
</div>
