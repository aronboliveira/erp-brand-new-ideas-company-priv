@php
$lang ??= 'en';
	$transferStoreBaseName ??= '';
	$transferStoreKebabName ??= '';
	$transferStoreResolved ??= null;
	$transferStoreActionUrl ??= '#';
	$transferStoreGuardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$transferStoreBaseName = VW::TRF;
		$transferStoreKebabName = Str::kebab($transferStoreBaseName);
		$transferStoreResolved = Route::has($transferStoreBaseName)
			? $transferStoreBaseName
			: (Route::has($transferStoreKebabName) ? $transferStoreKebabName : null);
		$transferStoreActionUrl = $transferStoreResolved ? (route($transferStoreResolved) ?? '#') : '#';
		$transferStoreGuardMsg = Utility::fetchLinkMessage($lang, VW::TRF, 'store_transfer_route_unavailable')
			?? 'Store transfer route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in transfers/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in transfers/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in transfers/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{!! Form::open([
	'url'                  => $transferStoreActionUrl,
	'method'               => 'post',
	'id'                   => 'create_transfer',
	'data-resolved-action' => $transferStoreActionUrl,
	'data-guard-msg'       => $transferStoreGuardMsg,
	'data-sv-localized'    => 'true',
]) !!}
	<div class="modal-body">
		@php
			$plan = Utility::getChatGPTSettings();
		@endphp
			@if($plan?->{PlansConstants::COL_GPT} == 1)
				<div class="{{ VC::TX_END }}">
						@php
							try {
								$genBase = 'generate';
								$genKebab = Str::kebab($genBase);
								$genResolved = Route::has($genBase) ? $genBase : (Route::has($genKebab) ? $genKebab : null);
								$genParam = 'transfer';
								$genUrl = $genResolved ? route($genResolved, [$genParam]) : '#';
								$langValue = isset($lang) ? $lang : Utility::fetchUserLang();
								$genGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::BNK_TRF, 'generate_ai_bank_transfer_route_unavailable') ?? 'Generate AI bank transfer route is unavailable. Please contact technical support or your domain administrator.';
								$genAnchorId = 'ai-generate-'.$genParam;
							} catch (\Throwable $e) {
								\Log::error('transfers/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
							}
						@endphp
					<a href="{{ $genUrl }}"
						id="{{ $genAnchorId }}"
						data-size="md"
						class="{{ VC::BT_SM_PM }} btn-icon"
						data-ajax-popup-over="true"
						data-url="{{ $genUrl }}"
						data-bs-placement="top"
						data-title="{{ __('Generate content with AI') }}"
						data-guard-msg="{{ base64_encode($genGuardMsg) }}"
						data-sv-localized="true">
							<i class="{{ VC::FAS_RB }}"></i>
							<span>{{ __('Generate with AI') }}</span>
					</a>
					<script defer src="{{ asset('assets/js/routes/transfers/generate.js') }}"></script>
				</div>
		@endif

		<div class="row">
			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
				{{ Form::select('employee_id', $employees ?? [], null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('branch_id', __('Branch'), ['class' => VC::FM_LB]) }}
				{{ Form::select('branch_id', $branches ?? [], null, ['class' => VC::FM_CT_SL]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('department_id', __('Department'), ['class' => VC::FM_LB]) }}
				{{ Form::select('department_id', $departments ?? [], null, ['class' => VC::FM_CT_SL]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('transfer_date', __('Transfer Date'), ['class' => VC::FM_LB]) }}
				{{ Form::date('transfer_date', null, ['class' => VC::FM_CT]) }}
			</div>

			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
				{{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Description')]) }}
			</div>
		</div>
	</div>

	<div class="modal-footer">
		<input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
	</div>
	<script defer src="{{ asset('assets/js/routes/transfers/store.js') }}"></script>
{!! Form::close() !!}
