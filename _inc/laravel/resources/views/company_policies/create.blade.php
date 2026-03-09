@php
$lang ??= 'en';
	$routeName ??= '';
	$storeRoute ??= '#';
	$formId ??= 'company_policy_store_form';
	$guardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$routeName = ViewsConstants::CPN_PL;
		$storeRoute = Route::has($routeName)
			? (route($routeName) ?? '#')
			: (Route::has(Str::kebab($routeName))
				? (route(Str::kebab($routeName)) ?? '#')
				: '#');
		$guardMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::CPN_PL,
			'company_policy_store_route_unavailable'
		) ?? 'Company Policy store route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in company_policies/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in company_policies/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in company_policies/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::open([
    'url'            => $storeRoute,
    'method'         => 'post',
    'enctype'        => 'multipart/form-data',
    'id'             => $formId,
    'data-url'       => $storeRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        @php
 $plan = Utility::getChatGPTSettings();
@endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
          @php
				$aiGenerateRouteBase ??= 'generate';
				$aiGenerateRouteKebab ??= '';
				$aiGenerateResolvedName ??= null;
				$aiGenerateTopic ??= 'company policy';
				$aiGenerateUrl ??= '#';
				$aiGenerateLang ??= 'en';
				$aiGenerateGuardMsg ??= '';
				$aiGenerateCompanyPolicyLinkId ??= 'ai-generate-company-policy-link';
				try {
					$aiGenerateRouteKebab = Str::kebab($aiGenerateRouteBase);
					$aiGenerateResolvedName = Route::has($aiGenerateRouteBase) ? $aiGenerateRouteBase : (Route::has($aiGenerateRouteKebab) ? $aiGenerateRouteKebab : null);
					$aiGenerateUrl = $aiGenerateResolvedName ? (route($aiGenerateResolvedName, [$aiGenerateTopic]) ?? '#') : '#';
					$aiGenerateLang = isset($lang) ? $lang : Utility::fetchUserLang();
					$aiGenerateGuardMsg = Utility::fetchLinkMessage($aiGenerateLang, ViewsConstants::CPN_PL, 'generate_ai_company_policy_route_unavailable')
						?? 'Generate AI company policy route is unavailable. Please contact technical support or your domain administrator.';
				} catch (\Error $e) {
					Log::error('Error in company_policies/create.blade.php AI generate @php block', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				} catch (\Exception $e) {
					Log::error('Exception in company_policies/create.blade.php AI generate @php block', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				} catch (\Throwable $e) {
					Log::error('Throwable in company_policies/create.blade.php AI generate @php block', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				}
@endphp
          <div class="{{ VC::FEND }}">
              <a
                  id="{{ $aiGenerateCompanyPolicyLinkId }}"
                  href="{{ $aiGenerateUrl }}"
                  data-size="md"
                  class="{{ VC::BT_PRM }} {{ VC::BT_LG }} btn-icon btn-sm"
                  data-ajax-popup-over="true"
                  data-url="{{ $aiGenerateUrl }}"
                  data-bs-placement="top"
                  data-title="{{ __('Generate content with AI') }}"
                  data-guard-msg="{{ base64_encode($aiGenerateGuardMsg) }}"
                  data-sv-localized="true"
              >
                  <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
              </a>
          </div>
          <script defer src="{{ asset('assets/js/routes/companyPolicies/generate.js') }}"></script>
        @endif
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('branch', __('Branch'), ['class' => VC::FM_LB]) }}
                @if(!empty($branch) && ((is_array($branch) && count($branch)) || ($branch instanceof \Illuminate\Support\Collection && !$branch->isEmpty())))
                    {{ Form::select('branch', $branch, null, ['class' => VC::FM_CT . ' select', 'required' => 'required']) }}
                @else
                    {{ Form::select('branch', ['' => __('No branches available')], null, ['class' => VC::FM_CT . ' select', 'disabled' => 'disabled']) }}
                @endif
            </div>
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('attachment', __('Attachment'), ['class' => VC::FM_LB]) }}
                <div class="choose-file {{ VC::FM_G }}">
                    <label for="attachment" class="{{ VC::FM_LB }}">
                        <input
                            type="file"
                            class="{{ VC::FM_CT }}"
                            name="attachment"
                            id="attachment"
                            data-filename="attachment_create"
                        >
                        <img id="image" class="{{ VC::MT3 }}" style="width:25%;" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input
            type="button"
            value="{{ __('Cancel') }}"
            class="{{ VC::BT_LG }}"
            data-bs-dismiss="modal"
        >
        <input
            type="submit"
            value="{{ __('Create') }}"
            class="{{ VC::BT_PRM }}"
        >
    </div>
    <script async src="{{ asset('assets/js/routes/companyPolicies/lang/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/companyPolicies/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/companyPolicies/attachment.js') }}"></script>
{{ Form::close() }}
