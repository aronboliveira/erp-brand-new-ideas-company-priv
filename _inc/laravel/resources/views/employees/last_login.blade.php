@php
$authUser ??= null;
	$users ??= [];
	$list ??= [];
	try {
		$authUser = Auth::user();
	} catch (InvalidArgumentException $e) {
		Log::error('auth_user_fetch_failed', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (RuntimeException $e) {
		Log::error('auth_user_runtime_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (TypeError $e) {
		Log::error('auth_user_type_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Error $e) {
		Log::error('auth_user_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Exception $e) {
		Log::error('auth_user_exception', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Throwable $e) {
		Log::error('auth_user_throwable', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	}
	try {
		$list = (is_array($users ?? null) || ($users ?? null) instanceof Collection) ? $users : [];
	} catch (TypeError $e) {
		Log::error('users_type_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
		$list = [];
	} catch (\Throwable $e) {
		Log::error('users_normalization_throwable', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
		$list = [];
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Last Login') }}
@endsection
@section(YieldingConstants::ADM_CTT)
	<div class="row">
		<div class="{{ VC::CM12 }}">
			<div class="card">
				<div class="{{ VC::CD_BD_TB_BD }}">
					<div class="{{ VC::TB_RSP }}">
						<table class="table datatable">
							<thead>
								<tr>
									<th>#</th>
									<th>{{ __('Name') }}</th>
									<th>{{ __('Last Login') }}</th>
									<th>{{ __('Role') }}</th>
								</tr>
							</thead>
							<tbody class="font-style">
								@forelse($list as $u)
									<tr>
										@if(isset($u->type) && strtolower($u->{UsersConstants::COL_TP}) === 'employee')
											<td>{{ isset($u->id) && $u->id !== '' ? (string)($authUser?->employeeIdFormat((string)$u->id) ?? (string)$u->id) : __('No employee ID available') }}</td>
										@else
											<td>--</td>
										@endif
										<td>{{ isset($u->name) && $u->name !== '' ? $u->name : __('No name available') }}</td>
										<td>{{ isset($u->last_login) && $u->last_login !== '' ? $u->last_login : __('No last login available') }}</td>
										<td>{{ isset($u->type) && $u->{UsersConstants::COL_TP} !== '' ? $u->{UsersConstants::COL_TP} : __('No role available') }}</td>
									</tr>
								@empty
									<tr>
										<td colspan="4" class="{{ VC::TXCT }}">{{ __('No users available') }}</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
