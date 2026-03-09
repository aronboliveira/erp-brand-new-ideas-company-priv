@php
$lang ??= 'en';
$employee ??= [];
	$chatEnabled ??= 0;
	$row ??= VC::RW;
	$colMd6 ??= VC::CM6;
	$col12 ??= VC::C12;
	$formGroup ??= VC::FM_G;
	$formControl ??= VC::FM_CT;
	$formLabel ??= VC::FM_LB;
	$aiGenBase ??= 'generate';
	$aiGenKebab ??= '';
	$aiGenResolved ??= null;
	$aiGenUrl ??= '#';
	$aiGenMsg ??= '';
	$aiGenId ??= 'account-asset-generate-link';
	$formId ??= 'store-account-asset-form';
	$storeBase ??= '';
	$storeKebab ??= '';
	$storeResolved ??= null;
	$storeResolvedUrl ??= '#';
	$storeGuardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$chatEnabled = Utility::getChatGPTSettings()?->{PlansConstants::COL_GPT} ?? 0;
		$aiGenKebab = Str::kebab($aiGenBase);
		$aiGenResolved = Route::has($aiGenBase) ? $aiGenBase : (Route::has($aiGenKebab) ? $aiGenKebab : null);
		$aiGenUrl = $aiGenResolved ? (route($aiGenResolved, ['account asset']) ?? '#') : '#';
		$aiGenMsg = Utility::fetchLinkMessage($lang, ViewsConstants::ACC_AST, 'generate_account_asset_unavailable')
			?? 'Generate account asset route is unavailable. Please contact technical support or your domain administrator.';
		$storeBase = ViewsConstants::ACC_AST;
		$storeKebab = Str::kebab($storeBase);
		$storeResolved = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
		$storeResolvedUrl = $storeResolved ? (route($storeResolved) ?? '#') : '#';
		$storeGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::ACC_AST, 'store_account_asset_unavailable')
			?? 'Store account asset route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in assets/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in assets/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in assets/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::open(['url' => $storeResolvedUrl, 'method' => 'post', 'id' => $formId, 'data-resolved-action' => $storeResolvedUrl, 'data-guard-msg' => $storeGuardMsg, 'data-sv-localized' => 'true']) }}
    <div class="modal-body">
        @if($chatEnabled)
            <div class="{{ VC::TX_END }}">
                <a href="{{ $aiGenUrl }}"
                   id="{{ $aiGenId }}"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-url="{{ $aiGenUrl }}"
                   data-bs-placement="top"
                   title="{{ __('Generate with AI') }}"
                   data-guard-msg="{{ base64_encode($aiGenMsg) }}"
                   data-sv-localized="true">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif
        <div class="{{ $row }}">
            <div class="{{ $col12 }} {{ $formGroup }}">
                {{ Form::label('employee_id', __('Employee'), ['class' => $formLabel]) }}
                {{ Form::select('employee_id[]', $employee, null, ['class' => "$formControl select2", 'id' => 'choices-multiple1', 'multiple' => true]) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Form::label('name', __('Name'), ['class' => $formLabel]) }}
                {{ Form::text('name', '', ['class' => $formControl, 'required' => true]) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Form::label('amount', __('Amount'), ['class' => $formLabel]) }}
                {{ Form::number('amount', '', ['class' => $formControl, 'required' => true, 'step' => '0.01']) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Form::label('purchase_date', __('Purchase Date'), ['class' => $formLabel]) }}
                {{ Form::date('purchase_date', '', ['class' => $formControl]) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Form::label('supported_date', __('Supported Date'), ['class' => $formLabel]) }}
                {{ Form::date('supported_date', '', ['class' => $formControl]) }}
            </div>
            <div class="{{ $col12 }} {{ $formGroup }}">
                {{ Form::label('description', __('Description'), ['class' => $formLabel]) }}
                {{ Form::textarea('description', '', ['class' => $formControl, 'rows' => 3]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
    <script defer src="{{ asset('assets/js/routes/accountAssets/generate.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/accountAssets/store.js') }}"></script>
{{ Form::close() }}
