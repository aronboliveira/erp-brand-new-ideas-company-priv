@php
$user ??= null;
	$lang ??= 'en';
	$updateBase ??= '';
	$updateKeb ??= '';
	$updateName ??= null;
	$wid ??= '';
	$updateUrl ??= '#';
	$updateGuard ??= '';
	$formId ??= 'edit_warning';
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
		$updateBase = VW::WRN . '.update';
		$updateKeb = Str::kebab($updateBase);
		$updateName = Route::has($updateBase) ? $updateBase : (Route::has($updateKeb) ? $updateKeb : null);
		$wid = (string) data_get($warning ?? null, 'id', '');
		$updateUrl = ($updateName && $wid !== '') ? (route($updateName, [$wid]) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::WRN, 'update_warning_route_unavailable') ?? 'Update warning route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in warnings/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in warnings/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in warnings/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{!! Form::model($warning, [
    'url'                  => $updateUrl,
    'method'               => 'PUT',
    'id'                   => $formId,
    'data-resolved-action' => $updateUrl,
    'data-guard-msg'       => $updateGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        @php
($plan = Utility::getChatGPTSettings())
        @if(($plan?->{PlansConstants::COL_GPT} ?? 0) == 1)
            @php
$genBase ??= 'generate';
				$genName ??= null;
				$genUrl ??= '#';
				$genGuard ??= '';
				$genId ??= 'warning-ai-generate-link';
				try {
					$genName = Route::has($genBase) ? $genBase : (Route::has(Str::kebab($genBase)) ? Str::kebab($genBase) : null);
					$genUrl = $genName ? (route($genName, ['warning']) ?? '#') : '#';
					$genGuard = Utility::fetchLinkMessage($lang, VW::WRN, 'ai_generate_content_unavailable') ?? 'AI content generation for warnings is unavailable. Please contact technical support or your domain administrator.';
				} catch (\Error $e) {
					NestedLog::error('Error in warnings/edit.blade.php AI generate @php block', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				} catch (\Exception $e) {
					NestedLog::error('Exception in warnings/edit.blade.php AI generate @php block', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				} catch (\Throwable $e) {
					NestedLog::error('Throwable in warnings/edit.blade.php AI generate @php block', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				}
@endphp
            <div class="{{ VC::TX_END }}">
                <a id="{{ $genId }}"
                   href="{{ $genUrl }}"
                   data-url="{{ $genUrl }}"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-title="{{ __('Generate content with AI') }}"
                   data-bs-placement="top"
                   data-guard-msg="{{ base64_encode($genGuard) }}"
                   data-sv-localized="true"
                   class="{{ VC::BT_SM_PM }} btn-icon">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
            <script defer src="{{ asset('assets/js/routes/warnings/generate.js') }}"></script>
        @endif

        <div class="row">
            @if(!empty($user?->{UC::COL_TP}) && strtolower($user->{UC::COL_TP}) !== 'employee')
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('warning_by', __('Warning By'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('warning_by', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
                </div>
            @endif

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('warning_to', __('Warning To'), ['class' => VC::FM_LB]) }}
                {{ Form::select('warning_to', $employees, null, ['class' => VC::FM_CT_SL]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('subject', __('Subject'), ['class' => VC::FM_LB]) }}
                {{ Form::text('subject', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('warning_date', __('Warning Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('warning_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>

    <script defer src="{{ asset('assets/js/routes/warnings/update.js') }}"></script>
{!! Form::close() !!}
