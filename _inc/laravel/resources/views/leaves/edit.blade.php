@php
$user ??= null;
	$lang ??= 'en';
	$leaveId ??= null;
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateResolved ??= null;
	$updateGuardMsg ??= '';
	$formId ??= 'edit_leave';
	$formOpen ??= [];
	$plan ??= null;
	$aiBase ??= 'generate';
	$aiKebab ??= '';
	$aiResolved ??= null;
	$aiParams ??= ['leave'];
	$aiUrl ??= '#';
	$aiLinkId ??= 'leave-ai-generate-link';
	$aiGuardMsg ??= '';
	$grammarBase ??= 'grammar';
	$grammarKebab ??= '';
	$grammarResolved ??= null;
	$grammarParams ??= ['grammar'];
	$grammarUrl ??= '#';
	$grammarLinkId ??= 'leave-grammar-link';
	$grammarGuardMsg ??= '';
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
		$leaveId = isset($leave) && !empty(data_get($leave, 'id')) ? data_get($leave, 'id') : null;
		$updateBase = VW::LV . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateGuardMsg = Utility::fetchLinkMessage($lang, VW::LV, 'update_leave_unavailable')
			?? 'Update leave route is unavailable. Please contact technical support or your domain administrator.';
		$formOpen = [
			'method' => 'PUT',
			'id' => $formId,
			'data-guard-msg' => $updateGuardMsg,
		];
		if ($updateResolved && $leaveId)
			$formOpen['route'] = [$updateResolved, $leaveId];
		else
			$formOpen['url'] = '#';
		$plan = Utility::getChatGPTSettings();
		if ($plan?->{PlansConstants::COL_GPT} == 1) {
			$aiKebab = Str::kebab($aiBase);
			$aiResolved = Route::has($aiBase) ? $aiBase : (Route::has($aiKebab) ? $aiKebab : null);
			$aiUrl = $aiResolved ? (route($aiResolved, $aiParams) ?? '#') : '#';
			$aiGuardMsg = Utility::fetchLinkMessage($lang, VW::LV, 'generate_leave_unavailable')
				?? 'Generate leave content route is unavailable. Please contact technical support or your domain administrator.';
		}
		$grammarKebab = Str::kebab($grammarBase);
		$grammarResolved = Route::has($grammarBase) ? $grammarBase : (Route::has($grammarKebab) ? $grammarKebab : null);
		$grammarUrl = $grammarResolved ? (route($grammarResolved, $grammarParams) ?? '#') : '#';
		$grammarGuardMsg = Utility::fetchLinkMessage($lang, 'generics', 'grammar_check_route_unavailable')
			?? 'Grammar check route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in leaves/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in leaves/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in leaves/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{!! Form::model($leave, $formOpen) !!}
    <div class="modal-body">
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="{{ VC::TX_END }}">
                <a href="{{ $aiUrl }}"
                   id="{{ $aiLinkId }}"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-url="{{ $aiUrl }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}"
                   data-guard-msg="{{ base64_encode($aiGuardMsg) }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif
        @if($user?->{UsersConstants::COL_TP} === UsersConstants::CPN || strtolower($user?->{UsersConstants::COL_TP}) == PermissionsConstants::HR)
            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'placeholder' => __('Select Employee')]) }}
                </div>
            </div>
        @endif
        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('leave_type_id', __('Leave Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('leave_type_id', $leavetypes, null, ['class' => VC::FM_CT_SL, 'placeholder' => __('Select Leave Type')]) }}
            </div>
        </div>
        <div class="row">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('start_date', null, ['class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('end_date', null, ['class' => VC::FM_CT]) }}
            </div>
        </div>
        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('leave_reason', __('Leave Reason'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('leave_reason', null, ['class' => VC::FM_CT, 'placeholder' => __('Leave Reason')]) }}
            </div>
        </div>
        @php
 $grammarTitle = __('Grammar check with AI');
@endphp
        <div class="row">
            <div class="{{ VC::CM12 }} {{ VC::TX_END }}">
                <a href="{{ $grammarUrl }}"
                   id="{{ $grammarLinkId }}"
                   class="{{ VC::BT_SM_PM }} btn-icon text-right"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-url="{{ $grammarUrl }}"
                   data-bs-placement="top"
                   data-title="{{ $grammarTitle }}"
                   data-guard-msg="{{ base64_encode($grammarGuardMsg) }}">
                    <i class="ti ti-rotate"></i> <span>{{ $grammarTitle }}</span>
                </a>
            </div>
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('remark', __('Remark'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('remark', null, ['class' => VC::FM_CT.' grammar_textarea', 'placeholder' => __('Leave Remark')]) }}
            </div>
        </div>
        @role(PermissionsConstants::CPN)
            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('status', __('Status'), ['class' => VC::FM_LB]) }}
                    <select name="status" class="{{ VC::FM_CT }} select2">
                        <option value="">{{ __('Select Status') }}</option>
                        <option value="pending"  @if($leave->status=='Pending')  selected @endif>{{ __('Pending') }}</option>
                        <option value="approval" @if($leave->status=='Approval') selected @endif>{{ __('Approval') }}</option>
                        <option value="reject"   @if($leave->status=='Reject')   selected @endif>{{ __('Reject') }}</option>
                    </select>
                </div>
            </div>
        @endrole
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/leaves/update.js') }}"></script>
    @if($plan?->{PlansConstants::COL_GPT} == 1)
        <script defer src="{{ asset('assets/js/routes/leaves/generate.js') }}"></script>
    @endif
    <script defer src="{{ asset('assets/js/routes/generics/grammar.js') }}"></script>
{!! Form::close() !!}
