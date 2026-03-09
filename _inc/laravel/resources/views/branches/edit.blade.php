@php
$lang ??= 'en';
	$branchId ??= null;
	$branchUpdateRoute ??= '#';
	$branchUpdateFormId ??= 'branch-update-form';
	$branchUpdateGuardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$branchId = data_get($branch ?? null, 'id');
		$branchUpdateRoute = ($branchId && Route::has(ViewsConstants::BRC . '.update'))
			? (route(ViewsConstants::BRC . '.update', $branchId) ?? '#')
			: '#';
		$branchUpdateFormId = 'branch-update-form-' . ($branchId ?? 'unknown');
		$branchUpdateGuardMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::BRC,
			'branch_update_route_unavailable'
		) ?? 'Branch update route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in branches/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in branches/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in branches/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($branch) && !empty($branchId))
	{!! Form::model($branch, [
		'url'            => $branchUpdateRoute,
		'method'         => 'PUT',
		'id'             => $branchUpdateFormId,
		'data-url'       => $branchUpdateRoute,
		'data-guard-msg' => $branchUpdateGuardMsg,
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
			<button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
		</div>
		<script defer>window.RouteGuard?.guardFormSubmit?.('{{ $branchUpdateFormId }}');</script>
	{!! Form::close() !!}
@else
	<div class="{{ VC::ALT_DNG }}">{{ __('No branch data found') }}</div>
@endif
