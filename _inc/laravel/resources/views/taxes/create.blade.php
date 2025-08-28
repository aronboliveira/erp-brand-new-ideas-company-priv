@php
	use App\Config\Constants\{StacksConstants, ViewClassNamesConstants as VC, ViewsConstants};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Log, Facades\Route, Str};
	use InvalidArgumentException;

	$lang = Utility::fetchUserLang();

	$formId = 'create-tax-form';
	$taxStoreBaseName = ViewsConstants::TX;
	$taxStoreResolved = null;
	$taxStoreActionUrl = '#';
	$taxStoreGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::TX, 'store_tax_route_unavailable') ?? 'Create tax route is unavailable. Please contact technical support or your domain administrator.';

	$nameLabel = __('Tax Rate Name') ?: __('No name label available');
	$rateLabel = __('Tax Rate %') ?: __('No rate label available');
	$cancelLabel = __('Cancel') ?: __('Failed to get cancel label');
	$createLabel = __('Create') ?: __('Failed to get create label');

	try {
		$taxStoreResolved = Route::has($taxStoreBaseName)
			? $taxStoreBaseName
			: (Route::has(Str::kebab($taxStoreBaseName)) ? Str::kebab($taxStoreBaseName) : null);
	} catch (\Error $e) {
		Log::error('Blade taxes/create: route name resolution error: ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade taxes/create: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade taxes/create: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade taxes/create: throwable while resolving route name: ' . $e->getMessage());
	}

	try {
		$taxStoreActionUrl = $taxStoreResolved ? route($taxStoreResolved) : '#';
	} catch (\Error $e) {
		Log::error('Blade taxes/create: route URL generation error: ' . $e->getMessage());
		$taxStoreActionUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade taxes/create: invalid argument while generating URL: ' . $e->getMessage());
		$taxStoreActionUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade taxes/create: general exception while generating URL: ' . $e->getMessage());
		$taxStoreActionUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade taxes/create: throwable while generating URL: ' . $e->getMessage());
		$taxStoreActionUrl = '#';
	}
@endphp

{{ Form::open([
	'url'                  => $taxStoreActionUrl,
	'method'               => 'post',
	'id'                   => $formId,
	'data-resolved-action' => $taxStoreActionUrl,
	'data-guard-msg'       => $taxStoreGuardMsg,
	'data-sv-localized'    => 'true',
]) }}
	<div class="modal-body">
		<div class="{{ VC::RW }}">
			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('name', $nameLabel, ['class' => VC::FM_LB]) }}
				{{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
				@error('name')
				<small class="invalid-name" role="alert">
					<strong class="text-danger">{{ $message }}</strong>
				</small>
				@enderror
			</div>
			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('rate', $rateLabel, ['class' => VC::FM_LB]) }}
				{{ Form::number('rate', '', ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
				@error('rate')
				<small class="invalid-rate" role="alert">
					<strong class="text-danger">{{ $message }}</strong>
				</small>
				@enderror
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<input type="button" value="{{ $cancelLabel }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ $createLabel }}" class="{{ VC::BT_PRM }}">
	</div>
{{ Form::close() }}
<script defer src="{{ asset('assets/js/routes/taxes/create.js') }}"></script>
