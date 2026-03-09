@php
$lang ??= 'en';
	$estId ??= null;
	$estUpdateBase ??= '';
	$estUpdateKebab ??= '';
	$estUpdateResolved ??= null;
	$estUpdateUrl ??= '#';
	$estUpdateFormId ??= 'estimate-update-form';
	$estUpdateGuardMsg ??= '';
	$clientsIsList ??= false;
	$taxesIsList ??= false;
	$statuses ??= [];
	$statusIsList ??= false;
	$clientOptions ??= [];
	$taxOptions ??= [];
	$statusOptions ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$estId = data_get($estimation ?? null, 'id');
		$estUpdateBase = VW::EST . '.update';
		$estUpdateKebab = Str::kebab($estUpdateBase);
		$estUpdateResolved = Route::has($estUpdateBase) ? $estUpdateBase : (Route::has($estUpdateKebab) ? $estUpdateKebab : null);
		$estUpdateUrl = ($estUpdateResolved && $estId) ? (route($estUpdateResolved, $estId) ?? '#') : '#';
		$estUpdateGuardMsg = Utility::fetchLinkMessage($lang, VW::EST, 'update_estimate_route_unavailable') ?? 'Update estimate route is unavailable. Please contact technical support or your domain administrator.';
		$clientsIsList = (is_array($client ?? null) && count($client ?? []) > 0) || (($client ?? null) instanceof Collection && $client->isNotEmpty());
		$taxesIsList = (is_array($taxes ?? null) && count($taxes ?? []) > 0) || (($taxes ?? null) instanceof Collection && $taxes->isNotEmpty());
		$statuses = Estimation::$statuses ?? [];
		$statusIsList = is_array($statuses) && count($statuses) > 0;
		$clientOptions = $clientsIsList ? (is_array($client) ? $client : $client->toArray()) : [__('No clients available')];
		$taxOptions = $taxesIsList ? (is_array($taxes) ? $taxes : $taxes->toArray()) : [__('No taxes available')];
		$statusOptions = $statusIsList ? $statuses : [__('No statuses available')];
	} catch (\Error $e) {
		Log::error('Error in estimations/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in estimations/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in estimations/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

<div class="{{ VC::CD }} bg-none card-box">
    @if(!empty($estimation) && isset($estimation->id))
        {{ Form::model($estimation, [
            'url'               => $estUpdateUrl,
            'method'            => 'PUT',
            'id'                => $estUpdateFormId,
            'data-url'          => $estUpdateUrl,
            'data-guard-msg'    => $estUpdateGuardMsg,
            'data-sv-localized' => 'true',
        ]) }}
            @csrf
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }} {{ VC::CLMS6 }}">
                    {{ Form::label('client_id', __('Client'), ['class' => VC::FM_LB]) }}
                    {{ Form::select(
                        'client_id',
                        $clientOptions,
                        null,
                        array_merge(['class' => VC::FM_CT.' select2', 'required' => 'required', 'placeholder' => __('Select Client')], $clientsIsList ? [] : ['disabled' => 'disabled'])
                    ) }}
                    @error('client_id')
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ $message }}</div>
                    @enderror
                    @unless($clientsIsList)
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ __('No clients available.') }}</div>
                    @endunless
                </div>

                <div class="{{ VC::FM_G }} {{ VC::CLMS6 }}">
                    {{ Form::label('status', __('Status'), ['class' => VC::FM_LB]) }}
                    {{ Form::select(
                        'status',
                        $statusOptions,
                        null,
                        array_merge(['class' => VC::FM_CT.' select2', 'required' => 'required', 'placeholder' => __('Select Status')], $statusIsList ? [] : ['disabled' => 'disabled'])
                    ) }}
                    @error('status')
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ $message }}</div>
                    @enderror
                    @unless($statusIsList)
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ __('No statuses available.') }}</div>
                    @endunless
                </div>

                <div class="{{ VC::FM_G }} {{ VC::CLMS6 }}">
                    {{ Form::label('issue_date', __('Issue Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('issue_date', null, ['class' => VC::FM_CT.' datepicker', 'required' => 'required', 'placeholder' => __('Select Issue Date')]) }}
                    @error('issue_date')
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ $message }}</div>
                    @enderror
                </div>

                <div class="{{ VC::FM_G }} {{ VC::CLMS6 }}">
                    {{ Form::label('discount', __('Discount'), ['class' => VC::FM_LB]) }}
                    {{ Form::number('discount', null, ['class' => VC::FM_CT, 'required' => 'required', 'min' => '0', 'placeholder' => __('Enter discount')]) }}
                    @error('discount')
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ $message }}</div>
                    @enderror
                </div>

                <div class="{{ VC::FM_G }} {{ VC::CLMS6 }}">
                    {{ Form::label('tax_id', __('Tax %'), ['class' => VC::FM_LB]) }}
                    {{ Form::select(
                        'tax_id',
                        $taxOptions,
                        null,
                        array_merge(['class' => VC::FM_CT.' select2', 'required' => 'required', 'placeholder' => __('Select Tax')], $taxesIsList ? [] : ['disabled' => 'disabled'])
                    ) }}
                    @error('tax_id')
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ $message }}</div>
                    @enderror
                    @unless($taxesIsList)
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ __('No taxes available') }}</div>
                    @endunless
                </div>

                <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                    {{ Form::label('terms', __('Terms'), ['class' => VC::FM_LB]) }}
                    {{ Form::textarea('terms', null, ['class' => VC::FM_CT, 'rows' => 3, 'placeholder' => __('Enter terms...')]) }}
                    @error('terms')
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ $message }}</div>
                    @enderror
                </div>

                <div class="{{ VC::C12 }} text-end">
                    <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
                    <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
                </div>
            </div>

            <script defer src="{{ asset('assets/js/routes/estimations/update.js') }}"></script>
        {{ Form::close() }}
    @else
        <div class="{{ VC::ALERT }} {{ VC::ALERT_DANGER }} mb-4" role="alert">
            {{ __('Estimation information could not be found.') }}
        </div>
    @endif
</div>
