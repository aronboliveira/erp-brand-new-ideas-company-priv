@php
$user ??= null;
	$lang ??= 'en';
	$storeBase ??= '';
	$storeKebab ??= '';
	$storeResolved ??= null;
	$storeUrl ??= '#';
	$formId ??= 'store_leave';
	$formGuardMsg ??= '';
	$grammarBase ??= 'grammar';
	$grammarKebab ??= '';
	$grammarResolved ??= null;
	$grammarParams ??= ['grammar'];
	$grammarUrl ??= '#';
	$grammarLinkId ??= 'grammar-check-link';
	$grammarGuardMsg ??= '';
	$grammarTitle ??= __('Check Grammar');
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
		$storeBase = VW::LV;
		$storeKebab = Str::kebab($storeBase);
		$storeResolved = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
		$storeUrl = $storeResolved ? (route($storeResolved) ?? '#') : '#';
		$formGuardMsg = Utility::fetchLinkMessage($lang, VW::LV, 'store_leave_unavailable') ?? 'Store leave route is unavailable. Please contact technical support or your domain administrator.';
		$grammarKebab = Str::kebab($grammarBase);
		$grammarResolved = Route::has($grammarBase) ? $grammarBase : (Route::has($grammarKebab) ? $grammarKebab : null);
		$grammarUrl = $grammarResolved ? (route($grammarResolved, $grammarParams) ?? '#') : '#';
		$grammarGuardMsg = Utility::fetchLinkMessage($lang, 'generics', 'grammar_check_route_unavailable') ?? 'Grammar check route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in leaves/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in leaves/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in leaves/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{{ Form::open(array('url'=>ViewsConstants::LV,'method'=>'post'))}}
    <div class="modal-body">
        @php
			$plan ??= null;
			try {
				$plan = Utility::getChatGPTSettings();
			} catch (\Error $e) {
				Log::error('Error fetching ChatGPT settings in leaves/create.blade.php', [
					'exception_class' => get_class($e),
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			} catch (\Exception $e) {
				Log::error('Exception fetching ChatGPT settings in leaves/create.blade.php', [
					'exception_class' => get_class($e),
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			} catch (\Throwable $e) {
				Log::error('Throwable fetching ChatGPT settings in leaves/create.blade.php', [
					'exception_class' => get_class($e),
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
@endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            @php
				$aiBase ??= 'generate';
				$aiKebab ??= '';
				$aiResolved ??= null;
				$aiParams ??= ['leave'];
				$aiUrl ??= '#';
				$aiLinkId ??= 'leave-ai-generate-link';
				$aiGuardMsg ??= '';
				try {
					$aiKebab = Str::kebab($aiBase);
					$aiResolved = Route::has($aiBase) ? $aiBase : (Route::has($aiKebab) ? $aiKebab : null);
					$aiUrl = $aiResolved ? (route($aiResolved, $aiParams) ?? '#') : '#';
					$aiGuardMsg = Utility::fetchLinkMessage($lang, VW::LV, 'generate_leave_unavailable') ?? 'Generate leave content route is unavailable. Please contact technical support or your domain administrator.';
				} catch (\Error $e) {
					Log::error('Error in leaves/create.blade.php AI generate @php block', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				} catch (\Exception $e) {
					Log::error('Exception in leaves/create.blade.php AI generate @php block', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				} catch (\Throwable $e) {
					Log::error('Throwable in leaves/create.blade.php AI generate @php block', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				}
@endphp
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

        @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN || strtolower($user?->{UsersConstants::COL_TP}) == PermissionsConstants::HR)
            <div class="{{ VC::RW }}">
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                        {{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
                    </div>
                </div>
            </div>
        @endif
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('leave_type_id', __('Leave Type'), ['class' => VC::FM_LB]) }}
                    <select name="leave_type_id" id="leave_type_id" class="{{ VC::FM_CT_SL }}">
                        <option value="">{{ __('Select Leave Type') }}</option>
                        @forelse($leavetypes ?? [] as $leave)
                            <option value="{{ $leave->id ?? '' }}">
                                {{ $leave->title ?? __('Untitled') }}
                                (<span class="float-right pr-5">{{ $leave->days ?? 0 }}</span>)
                            </option>
                        @empty
                            <option value="" disabled>{{ __('No leave types available') }}</option>
                        @endforelse
                    </select>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('start_date', null, ['class' => VC::FM_CT]) }}
                </div>
            </div>
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('end_date', null, ['class' => VC::FM_CT]) }}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('leave_reason',__('Leave Reason') ,['class'=>'form-label'])}}
                    {{ Form::textarea('leave_reason',null,array('class'=>'form-control','placeholder'=>__('Leave Reason')))}}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="{{ VC::CM12 }} {{ VC::TX_END }}">
                <a href="{{ $grammarUrl }}"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon text-right"
                   data-ajax-popup-over="true"
                   id="{{ $grammarLinkId }}"
                   data-url="{{ $grammarUrl }}"
                   data-bs-placement="top"
                   data-title="{{ $grammarTitle }}"
                   data-guard-msg="{{ base64_encode($grammarGuardMsg) }}">
                    <i class="ti ti-rotate"></i> <span>{{ $grammarTitle }}</span>
                </a>
            </div>
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('remark',__('Remark'),['class'=>'form-label'])}}
                    {{ Form::textarea('remark',null,array('class'=>'form-control grammer_textarea','placeholder'=>__('Leave Remark')))}}
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/leaves/store.js') }}">
    </script>
{!! Form::close() !!}
