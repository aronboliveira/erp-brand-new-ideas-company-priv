@php
$lang ??= '';
	$branchStoreRoute ??= '#';
	$formId ??= 'store-branch-form';
	$branchStoreMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? '';
		$branchStoreRoute = Route::has(VW::BRC . '.store')
			? (route(VW::BRC . '.store') ?? '#')
			: (Route::has(VW::BRC) ? (route(VW::BRC) ?? '#') : '#');
		$branchStoreMsg = Utility::fetchLinkMessage(
			$lang,
			VW::BRC,
			'branch_store_route_unavailable'
		) ?? 'Branch store route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\InvalidArgumentException $e) {
		Log::error('InvalidArgumentException in branches/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Error $e) {
		Log::error('Error in branches/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in branches/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in branches/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{!! Form::open([
	'url'            => $branchStoreRoute,
	'method'         => 'post',
	'id'             => $formId,
	'data-url'       => $branchStoreRoute,
	'data-guard-msg' => $branchStoreMsg,
]) !!}
	<div class="modal-body">
		<div class="{{ VC::RW }}">
			<div class="{{ VC::C12 }}">
				<div class="{{ VC::FM_G }}">
					{!! Form::label('name', __('Name'), ['class' => VC::FM_LB]) !!}
					{!! Form::text('name', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Branch Name')]) !!}
					@error('name')
						<span class="invalid-name" role="alert">
							<strong class="{{ VC::TXT_MT }}">{{ $message }}</strong>
						</span>
					@enderror
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
		<button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
	</div>
	<script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
	<script defer>window.RouteGuard?.guardFormSubmit?.('{{ $formId }}');</script>
{!! Form::close() !!}
