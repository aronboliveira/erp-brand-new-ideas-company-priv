@php
$tax ??= null;

	$lang = Utility::fetchUserLang();

	$formId = 'update-tax-form';
	$taxId = data_get($tax, 'id') ?? null;

	$taxUpdateBaseName = ViewsConstants::TX . '.update';
	$taxUpdateResolved = null;
	$taxUpdateActionUrl = '#';
	$taxUpdateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::TX, 'update_tax_route_unavailable') ?? 'Update tax route is unavailable. Please contact technical support or your domain administrator.';

	$nameLabel = __('Tax Rate Name') ?: __('No name label available');
	$rateLabel = __('Tax Rate %') ?: __('No rate label available');
	$cancelLabel = __('Cancel') ?: __('Failed to get cancel label');
	$updateLabel = __('Update') ?: __('Failed to get update label');

	try {
		$taxUpdateResolved = Route::has($taxUpdateBaseName)
			? $taxUpdateBaseName
			: (Route::has(Str::kebab($taxUpdateBaseName)) ? Str::kebab($taxUpdateBaseName) : null);
	} catch (\Error $e) {
		Log::error('Blade taxes/update: route name resolution error: ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade taxes/update: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade taxes/update: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade taxes/update: throwable while resolving route name: ' . $e->getMessage());
	}

	try {
		$taxUpdateActionUrl = ($taxUpdateResolved && !empty($taxId)) ? route($taxUpdateResolved, $taxId) : '#';
	} catch (\Error $e) {
		Log::error('Blade taxes/update: route URL generation error: ' . $e->getMessage());
		$taxUpdateActionUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade taxes/update: invalid argument while generating URL: ' . $e->getMessage());
		$taxUpdateActionUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade taxes/update: general exception while generating URL: ' . $e->getMessage());
		$taxUpdateActionUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade taxes/update: throwable while generating URL: ' . $e->getMessage());
		$taxUpdateActionUrl = '#';
	}
@endphp

{{ Form::model($tax, [
	'url'                  => $taxUpdateActionUrl,
	'method'               => 'PUT',
	'id'                   => $formId,
	'data-resolved-action' => $taxUpdateActionUrl,
	'data-guard-msg'       => $taxUpdateGuardMsg,
	'data-sv-localized'    => 'true',
]) }}
	<div class="modal-body">
		<div class="{{ VC::RW }}">
			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('name', $nameLabel, ['class' => VC::FM_LB]) }}
				{{ Form::text('name', null, ['class' => VC::FM_CT . ' font-style', 'required' => 'required']) }}
				@error('name')
				<small class="invalid-name" role="alert">
					<strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
				</small>
				@enderror
			</div>
			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('rate', $rateLabel, ['class' => VC::FM_LB]) }}
				{{ Form::number('rate', null, ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
				@error('rate')
				<small class="invalid-rate" role="alert">
					<strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
				</small>
				@enderror
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<input type="button" value="{{ $cancelLabel }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ $updateLabel }}" class="{{ VC::BT_PRM }}">
	</div>
{{ Form::close() }}
<script defer src="{{ asset('assets/js/routes/taxes/update.js') }}"></script>
