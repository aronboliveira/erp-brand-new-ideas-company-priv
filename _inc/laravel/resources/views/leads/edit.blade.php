@php
$lang ??= 'en';
	$hasLead ??= false;
	$plan ??= null;
	$leadSubjectFallback ??= '';
	$leadNameFallback ??= '';
	$leadEmailFallback ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$hasLead = !empty($lead ?? null) && data_get($lead, 'id');
		$plan = Utility::getChatGPTSettings();
		$leadSubjectFallback = __('No subject available for lead');
		$leadNameFallback = __('No client name available for lead');
		$leadEmailFallback = __('No email available for lead');
	} catch (\Error $e) {
		Log::error('Error in leads/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in leads/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in leads/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if(!$hasLead)
    <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('The requested lead was not found or is unavailable.') }}</div>
@else
    @php
$updateBase ??= '';
		$updateKebab ??= '';
		$updateResolved ??= null;
		$updateUrl ??= '#';
		$updateGuard ??= '';
		try {
			$updateBase = VW::LD . '.update';
			$updateKebab = Str::kebab($updateBase);
			$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
			$leadId = data_get($lead ?? null, 'id', '');
			$updateUrl = ($updateResolved && $hasLead && $leadId) ? (route($updateResolved, $leadId) ?? '#') : '#';
			$updateGuard = Utility::fetchLinkMessage($lang, VW::LD, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
		} catch (\Error $e) {
			UpdateLog::error('Error in leads/edit.blade.php update @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\Exception $e) {
			UpdateLog::error('Exception in leads/edit.blade.php update @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\Throwable $e) {
			UpdateLog::error('Throwable in leads/edit.blade.php update @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}
@endphp
    {{ Form::model($lead, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'lead-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true'
    ]) }}
        <div class="modal-body">
            @if(($plan?->{PlansConstants::COL_GPT} ?? 0) == 1)
              @php
$aiBase ??= 'generate';
				$aiKebab ??= '';
				$aiResolved ??= null;
				$aiUrl ??= '#';
				$aiGuard ??= '';
				try {
					$aiKebab = Str::kebab($aiBase);
					$aiResolved = Route::has($aiBase) ? $aiBase : (Route::has($aiKebab) ? $aiKebab : null);
					$aiUrl = $aiResolved ? (route($aiResolved, ['lead']) ?? '#') : '#';
					$aiGuard = Utility::fetchLinkMessage($lang, VW::LD, 'ai_generate_unavailable') ?? __('AI generation is unavailable. Please contact technical support or your domain administrator.');
				} catch (\Error $e) {
					AiLog::error('Error in leads/edit.blade.php AI @php block', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				} catch (\Exception $e) {
					AiLog::error('Exception in leads/edit.blade.php AI @php block', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				} catch (\Throwable $e) {
					AiLog::error('Throwable in leads/edit.blade.php AI @php block', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				}
@endphp
                <div class="{{ VC::TX_END }}">
                    <a href="{{ $aiUrl }}"
                       id="lead-ai-generate"
                       class="{{ VC::BT_SM_PM }}"
                       data-ajax-popup-over="true"
                       data-url="{{ $aiUrl }}"
                       data-guard-msg="{{ base64_encode($aiGuard) }}"
                       data-sv-localized="true"
                       data-title="{{ __('Generate content with AI') }}">
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif

            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('subject', __('Subject'), [ 'class' => VC::FM_LB ]) }}<span class="{{ VC::TX_DNG }}">*</span>
                    {{ Form::text('subject', data_get($lead ?? [], 'subject', $leadSubjectFallback), [ 'class' => VC::FM_CT, 'required' => 'required' ]) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('user_id', __('User'), [ 'class' => VC::FM_LB ]) }}<span class="{{ VC::TX_DNG }}">*</span>
                    {{ Form::select('user_id', Utility::isFilled($users)? $users : ['' => __('No user available')], null, [ 'class' => VC::FM_CT_SL . ' select', 'required' => 'required' ] ?? []) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('name', __('Name'), [ 'class' => VC::FM_LB ]) }}<span class="{{ VC::TX_DNG }}">*</span>
                    {{ Form::text('name', data_get($lead ?? [], 'name', $leadNameFallback), [ 'class' => VC::FM_CT, 'required' => 'required' ]) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('email', __('Email'), [ 'class' => VC::FM_LB ]) }}<span class="{{ VC::TX_DNG }}">*</span>
                    {{ Form::email('email', data_get($lead ?? [], 'email', $leadEmailFallback), [ 'class' => VC::FM_CT, 'required' => 'required' ]) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('phone', __('Phone'), [ 'class' => VC::FM_LB ]) }}<span class="{{ VC::TX_DNG }}">*</span>
                    {{ Form::text('phone', data_get($lead ?? [], 'phone', __('No phone available for lead')), [ 'class' => VC::FM_CT, 'required' => 'required' ]) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('pipeline_id', __('Pipeline'), [ 'class' => VC::FM_LB ]) }}<span class="{{ VC::TX_DNG }}">*</span>
                    {{ Form::select('pipeline_id', Utility::isFilled($pipelines) ? $pipelines : ['' => __('No pipeline available')], null, [ 'class' => VC::FM_CT_SL . ' select', 'required' => 'required' ] ?? []) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('stage_id', __('Stage'), [ 'class' => VC::FM_LB ]) }}<span class="{{ VC::TX_DNG }}">*</span>
                    {{ Form::select('stage_id', ['' => __('Select Stage')], null, [ 'class' => VC::FM_CT_SL . ' select', 'required' => 'required' ]) }}
                </div>
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('sources', __('Sources'), [ 'class' => VC::FM_LB ]) }}<span class="{{ VC::TX_DNG }}">*</span>
                    {{ Form::select('sources[]', Utility::isFilled($sources) ? $sources : ['' => __('No source available')], null, [ 'class' => VC::FM_CT_SL . ' select2', 'id' => 'choices-multiple1', 'multiple' => 'multiple', 'required' => 'required' ] ?? []) }}
                </div>
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('products', __('Products'), [ 'class' => VC::FM_LB ]) }}<span class="{{ VC::TX_DNG }}">*</span>
                    {{ Form::select('products[]', Utility::isFilled($products) ? $products : ['' => __('No product available')], null, [ 'class' => VC::FM_CT_SL . ' select2', 'id' => 'choices-multiple2', 'multiple' => 'multiple', 'required' => 'required' ] ?? []) }}
                </div>
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('notes', __('Notes'), [ 'class' => VC::FM_LB ]) }}
                    {{ Form::textarea('notes', data_get($lead ?? [], 'notes', ''), [ 'class' => 'summernote-simple' ]) }}
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}" id="lead-update-submit">
        </div>

        <script async src="{{ asset('assets/js/routes/leads/lang/edit.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/leads/update.js') }}"></script>
    {{ Form::close() }}
@endif
