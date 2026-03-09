@php
$user ??= null;
	$lang ??= 'en';
	$routeName ??= '';
	$storeRoute ??= '#';
	$formId ??= 'complaintStoreForm';
	$guardMsg ??= '';
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
		$routeName = ViewsConstants::CPL;
		$storeRoute = Route::has($routeName)
			? (route($routeName) ?? '#')
			: (Route::has(Str::kebab($routeName))
				? (route(Str::kebab($routeName)) ?? '#')
				: '#');
		$guardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::CPL, 'complaint_store_route_unavailable')
			?? 'Complaint store route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in complaints/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in complaints/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in complaints/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{{ Form::open([
    'route'          => [$storeRoute],
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $storeRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="{{ VC::RW }} modal-body">
        @php
 $plan = Utility::getChatGPTSettings();
@endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            @php
                $aiGenerateRouteBase             ??= 'generate';
                try {
                    $aiGenerateRouteKebab            = Str::kebab($aiGenerateRouteBase);
                    $aiGenerateResolvedName          = Route::has($aiGenerateRouteBase) ? $aiGenerateRouteBase : (Route::has($aiGenerateRouteKebab) ? $aiGenerateRouteKebab : null);
                    $aiGenerateTopic                 = 'complaint';
                    $aiGenerateComplaintUrl          = $aiGenerateResolvedName ? route($aiGenerateResolvedName, [$aiGenerateTopic]) : '#';
                    $aiGenerateLang                  = isset($lang) ? $lang : Utility::fetchUserLang();
                    $aiGenerateComplaintGuardMsg     = Utility::fetchLinkMessage($aiGenerateLang, ViewsConstants::CPL, 'generate_ai_complaint_route_unavailable') ?? 'Generate AI complaint route is unavailable. Please contact technical support or your domain administrator.';
                    $aiGenerateComplaintLinkId       = 'ai-generate-complaint-link';
                } catch (\Throwable $e) {
                    \Log::error('complaints/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <div class="{{ VC::FEND }}">
                <a href="{{ $aiGenerateComplaintUrl }}"
                id="{{ $aiGenerateComplaintLinkId }}"
                data-size="md"
                class="{{ VC::BT_PRM }} {{ VC::BT_SM }} btn-icon"
                data-ajax-popup-over="true"
                data-url="{{ $aiGenerateComplaintUrl }}"
                data-bs-placement="top"
                data-title="{{ __('Generate content with AI') }}"
                data-guard-msg="{{ base64_encode($aiGenerateComplaintGuardMsg) }}"
                data-sv-localized="true">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
            <script defer src="{{ asset('assets/js/routes/complaints/generate.js') }}"></script>
        @endif
        @php
            $isEmployeesEmpty = empty($employees) || (is_array($employees) && count($employees) === 0) || ($employees instanceof Collection && $employees->isEmpty());
@endphp
        <div class="{{ VC::RW }}">
            @if($user?->{UsersConstants::COL_TP} !== 'employee')
                <div class="{{ VC::FM_G }} col-md-6 col-lg-6">
                    {{ Form::label('complaint_from', __('Complaint From'), ['class' => VC::FM_LB]) }}
                    @if($isEmployeesEmpty)
                        {{ Form::select('complaint_from', $employees, null, ['class' => VC::FM_CT_SL]) }}
                    @else
                        {{ Form::select('complaint_from', [__('No employees available')], null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                    @endif
                </div>
            @endif

            <div class="{{ VC::FM_G }} col-md-6 col-lg-6">
                {{ Form::label('complaint_against', __('Complaint Against'), ['class' => VC::FM_LB]) }}
                @if($isEmployeesEmpty)
                    {{ Form::select('complaint_against', $employees, null, ['class' => VC::FM_CT_SL]) }}
                @else
                    {{ Form::select('complaint_against', [__('No employees available')], null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                @endif
            </div>

            <div class="{{ VC::FM_G }} col-md-6 col-lg-6">
                {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_G }} col-md-6 col-lg-6">
                {{ Form::label('complaint_date', __('Complaint Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('complaint_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_G }} col-md-12">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            {{ __('Cancel') }}
        </button>
        <button type="submit" class="{{ VC::BT_PRM }}">
            {{ __('Create') }}
        </button>
    </div>
    <script defer src="{{ asset('assets/js/routes/complaints/store.js') }}"></script>
{{ Form::close() }}
