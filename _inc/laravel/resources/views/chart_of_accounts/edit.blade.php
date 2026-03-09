@php
$lang ??= 'en';
	$routeName ??= '';
	$updateRoute ??= '#';
	$formId ??= 'chart_of_accounts_update_form_unknown';
	$guardMsg ??= '';
	$chartOfAccountId ??= null;
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$chartOfAccountId = data_get($chartOfAccount ?? null, 'id');
		$routeName = ViewsConstants::COA . '.update';
		$updateRoute = ($chartOfAccountId && Route::has($routeName))
			? (route($routeName, $chartOfAccountId) ?? '#')
			: (($chartOfAccountId && Route::has(Str::kebab($routeName)))
				? (route(Str::kebab($routeName), $chartOfAccountId) ?? '#')
				: '#');
		$formId = 'chart_of_accounts_update_form_' . ($chartOfAccountId ?? 'unknown');
		$guardMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::COA,
			'chart_of_account_update_route_unavailable'
		) ?? 'Chart of Account update route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in chart_of_accounts/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in chart_of_accounts/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in chart_of_accounts/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::model($chartOfAccount, [
    'route'          => $updateRoute,
    'method'         => 'PUT',
    'id'             => $formId,
    'data-url'       => $updateRoute,
    'data-guard-msg' => $guardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        @php
 $plan = Utility::getChatGPTSettings();
@endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="{{ VC::FEND }}">
                <a
                    href="#"
                    data-size="md"
                    class="{{ VC::BT_SM_PM }} btn-icon"
                    data-ajax-popup-over="true"
                    data-url="{{ route('generate',['chart of account']) }}"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}"
                >
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('code', __('Code'), ['class' => VC::FM_LB]) }}
                {{ Form::number('code', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('is_enabled', __('Is Enabled'), ['class' => VC::FM_LB]) }}
                <div class="{{ VC::FM_CHK }} form-switch">
                    <input
                        type="checkbox"
                        name="is_enabled"
                        id="is_enabled"
                        class="form-check-input"
                        {{ $chartOfAccount->is_enabled ? 'checked' : '' }}
                    >
                    <label for="is_enabled" class="form-check-label"></label>
                </div>
            </div>

            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'rows' => 2]) }}
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
            value="{{ __('Update') }}"
            class="{{ VC::BT_PRM }}"
        >
    </div>
{{ Form::close() }}
