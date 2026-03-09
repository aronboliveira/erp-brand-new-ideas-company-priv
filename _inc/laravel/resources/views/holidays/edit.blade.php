@php
$lang ??= 'en';
	$hasHoliday ??= false;
	$updateUrl ??= '#';
	$updateGuardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$hasHoliday = !empty($holiday ?? null) && data_get($holiday, 'id');
		$updateGuardMsg = Utility::fetchLinkMessage($lang, VW::HLD, 'update_route_unavailable')
			?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
	} catch (\Error $e) {
		Log::error('Error in holidays/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in holidays/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in holidays/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@can('edit holiday')
    @php
        try {
            $updateBase     = VW::HLD . '.update';
            $updateKebab    = Str::kebab($updateBase);
            $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
            $updateUrl      = ($updateResolved && $hasHoliday) ? route($updateResolved, $holiday->id) : '#';
        } catch (\Throwable $e) {
            \Log::error('holidays/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
@endcan

@if(!$hasHoliday)
    <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('The requested holiday was not found or is unavailable.') }}</div>
@else
    {{ Form::model($holiday, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'holiday-edit-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuardMsg,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            @php
 $plan = Utility::getChatGPTSettings();
@endphp
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                @php
                    $genUrl      ??= '#';
                    try {
                        $genGuardMsg = Utility::fetchLinkMessage($lang, VW::HLD, 'generate_ai_route_unavailable') ?? __('AI generation route is unavailable. Please contact technical support or your domain administrator.');
                        if (Route::has('generate')) {
                            $genUrl = route('generate', ['holiday']);
                        }
                    } catch (\Throwable $e) {
                        \Log::error('holidays/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <div class="{{ VC::TX_END }}">
                    <a
                        id="holiday-gen-ai"
                        href="#"
                        data-size="md"
                        class="{{ VC::BT_SM_PM }} btn-icon"
                        data-ajax-popup-over="true"
                        data-url="{{ $genUrl }}"
                        data-bs-placement="top"
                        data-title="{{ __('Generate content with AI') }}"
                        data-guard-msg="{{ base64_encode($genGuardMsg) }}"
                        data-sv-localized="true"
                    >
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif

            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('occasion', __('Occasion'), ['class' => 'form-label']) }}
                    {{ Form::text('occasion', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter occasion')]) }}
                </div>
            </div>
            <div class="row">
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('date', __('Start Date'), ['class' => 'form-label']) }}
                    {{ Form::date('date', null, ['class' => VC::FM_CT]) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                    {{ Form::date('end_date', null, ['class' => VC::FM_CT]) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/holidays/edit.js') }}"></script>
        <script async src="{{ asset('assets/js/routes/holidays/generateEdit.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/holidays/editPicker.js') }}"></script>
    {{ Form::close() }}
@endif
