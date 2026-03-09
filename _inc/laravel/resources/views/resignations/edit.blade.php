@php
$user ??= null;
	$lang ??= 'en';
	$genBaseName ??= 'generate';
	$genKebabName ??= '';
	$genResolvedName ??= null;
	$genUrl ??= '#';
	$genLinkId ??= 'resignation-generate-link';
	$genGuardMsg ??= '';
	$updateBaseName ??= '';
	$updateKebabName ??= '';
	$updateResolvedName ??= null;
	$updateUrl ??= '#';
	$formId ??= 'edit_resignation';
	$updateGuardMsg ??= '';
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
		$genKebabName = Str::kebab($genBaseName);
		$genResolvedName = Route::has($genBaseName) ? $genBaseName : (Route::has($genKebabName) ? $genKebabName : null);
		$genUrl = $genResolvedName ? (route($genResolvedName, ['resignation']) ?? '#') : '#';
		$genGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::RSG, 'generate_resignation_route_unavailable')
			?? 'Generate resignation route is unavailable. Please contact technical support or your domain administrator.';
		$updateBaseName = ViewsConstants::RSG . '.update';
		$updateKebabName = Str::kebab($updateBaseName);
		$updateResolvedName = Route::has($updateBaseName) ? $updateBaseName : (Route::has($updateKebabName) ? $updateKebabName : null);
		$updateUrl = ($updateResolvedName && !empty($resignation?->id)) ? (route($updateResolvedName, [$resignation->id]) ?? '#') : '#';
		$updateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::RSG, 'update_resignation_route_unavailable')
			?? 'Update resignation route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in resignations/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in resignations/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in resignations/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{!! Form::model($resignation, [
    'url'    => $updateUrl,
    'method' => 'PUT',
    'id'     => $formId,
    'data-action-url'    => $updateUrl,
    'data-form-guard-msg'=> $updateGuardMsg,
    'data-sv-localized'  => 'true',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="{{ VC::TX_END }}">
                <a href="{{ $genUrl }}"
                   id="{{ $genLinkId }}"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon btn-sm"
                   data-ajax-popup-over="true"
                   data-url="{{ $genUrl }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}"
                   data-guard-msg="{{ base64_encode($genGuardMsg) }}"
                   data-sv-localized="true"
                   data-bs-toggle="tooltip"
                   title="{{ __('Generate with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="row">
            @if(!empty($user?->{UsersConstants::COL_TP}) && strtolower($user->{UsersConstants::COL_TP}) !== 'employee')
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
                </div>
            @endif

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('notice_date', __('Notice Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('notice_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('resignation_date', __('Resignation Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('resignation_date', null, ['class' => VC::FM_CT]) }}
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
{!! Form::close() !!}

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/resignations/generate.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/resignations/update.js') }}"></script>
@endpush
