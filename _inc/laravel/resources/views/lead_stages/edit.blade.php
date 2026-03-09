@php
$lang ??= 'en';
	$hasStage ??= false;
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateResolved ??= null;
	$updateUrl ??= '#';
	$updateGuard ??= '';
	$pipelinesIsList ??= false;
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$hasStage = !empty($leadStage ?? null) && data_get($leadStage, 'id');
		$updateBase = VW::LD_STG . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateResolved && $hasStage) ? (route($updateResolved, data_get($leadStage ?? null, 'id')) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::LD_STG, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
		$pipelinesIsList = (is_array($pipelines ?? null) && count($pipelines ?? []) > 0) || (($pipelines ?? null) instanceof Collection && $pipelines->isNotEmpty());
	} catch (\Error $e) {
		Log::error('Error in lead_stages/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in lead_stages/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in lead_stages/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if($hasStage)
    {{ Form::model($leadStage, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'leadstage-edit-form',
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true'
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('name', __('Stage Name'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>

                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('pipeline_id', __('Pipeline'), ['class' => VC::FM_LB]) }}
                    @if($pipelinesIsList)
                        {{ Form::select('pipeline_id', $pipelines, null, ['class' => VC::FM_CT_SL . ' select2', 'required' => 'required']) }}
                    @else
                        <div class="{{ VC::TXS }} {{ VC::TXT_MT }}">{{ __('No pipelines available.') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/leads/stages/update.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="{{ VC::TXS }} {{ VC::TXT_MT }}">{{ __('Requested stage was not found or is unavailable.') }}</div>
@endif
