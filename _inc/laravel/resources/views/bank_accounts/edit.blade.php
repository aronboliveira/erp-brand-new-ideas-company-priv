@php
$lang ??= 'en';
	$bankAccountId ??= null;
	$updateRoute ??= '#';
	$formId ??= 'bank-account-update-form';
	$updateMsg ??= '';
	$fields ??= [];
	$chart_accounts ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$bankAccountId = data_get($bankAccount ?? null, 'id');
		$updateRoute = $bankAccountId && Route::has(ViewsConstants::BNK_ACC . '.update')
			? (route(ViewsConstants::BNK_ACC . '.update', $bankAccountId) ?? '#')
			: ($bankAccountId && Route::has(Str::kebab(ViewsConstants::BNK_ACC . '.update'))
				? (route(Str::kebab(ViewsConstants::BNK_ACC . '.update'), $bankAccountId) ?? '#')
				: '#');
		$updateMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::BNK_ACC,
			'bank_account_update_route_unavailable'
		) ?? 'Bank Account update route is unavailable. Please contact technical support or your domain administrator.';
		$fields = [
			['name'=>'chart_account_id','type'=>'select','label'=>__('Account'),'options'=>$chart_accounts,'cols'=>6],
			['name'=>'holder_name',     'type'=>'text',  'label'=>__('Bank Holder Name'),                           'cols'=>6],
			['name'=>'bank_name',       'type'=>'text',  'label'=>__('Bank Name'),                                  'cols'=>6],
			['name'=>'account_number',  'type'=>'text',  'label'=>__('Account Number'),                             'cols'=>6],
			['name'=>'opening_balance', 'type'=>'number','label'=>__('Opening Balance'),'attrs'=>['step'=>'0.01'],'cols'=>6],
			['name'=>'contact_number',  'type'=>'text',  'label'=>__('Contact Number'),                             'cols'=>6],
			['name'=>'bank_address',    'type'=>'textarea','label'=>__('Bank Address'),'attrs'=>['rows'=>3],        'cols'=>12],
		];
	} catch (\Error $e) {
		Log::error('Error in bank_accounts/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in bank_accounts/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in bank_accounts/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($bankAccount) && isset($bankAccount?->id))
    {{ Form::model($bankAccount, [
        'url'            => $updateRoute,
        'method'         => 'PUT',
        'id'             => $formId,
        'data-url'       => $updateRoute,
        'data-guard-msg' => $updateMsg,
    ]) }}
        <div class="modal-body">
            <div class="row">
                @foreach($fields as $f)
                    <div class="{{ VC::FM_G }} col-md-{{ $f['cols'] }}">
                        {{ Form::label($f['name'], $f['label'], ['class'=>'form-label']) }}
                        @php
                            try {
                                $attrs = ['class'=>'form-control','required'=>'required'];
                                if(!empty($f['attrs'])) {
                                    $attrs = array_merge($attrs, $f['attrs']);
                                }
                            } catch (\Throwable $e) {
                                \Log::error('bank_accounts/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        @if($f['type']==='select')
                            {{ Form::select($f['name'], $f['options'], null, $attrs + ['placeholder'=>'']) }}
                        @elseif($f['type']==='textarea')
                            {{ Form::textarea($f['name'], null, $attrs) }}
                        @else
                            {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                        @endif
                    </div>
                @endforeach

                @if(isset($customFields) && !$customFields->isEmpty())
                    <div class="{{ VC::CM12 }}">
                        <div class="{{ VC::TAB_FD_SH }}" id="tab-2" role="tabpanel">
                            @include(ViewsConstants::CST_FD . '.formBuilder')
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
        </div>
        <script defer src="{{ asset('assets/js/routes/bank/accounts/edit.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::CM12 }}">
                <p class="{{ VC::TXT_MT }}">{{ __('No bank account found.') }}</p>
            </div>
        </div>
    </div>
@endif
