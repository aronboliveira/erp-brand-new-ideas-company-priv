@php
$user ??= null;
	$lang ??= 'en';
	$changeBase ??= '';
	$changeKebab ??= '';
	$changeResolved ??= null;
	$changeActionUrl ??= '#';
	$formId ??= 'leave-changeaction-form';
	$guardMsg ??= '';
	$formatDate ??= null;
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
		$changeBase = VW::LV . '.change_action';
		$changeKebab = Str::kebab($changeBase);
		$changeResolved = Route::has($changeBase) ? $changeBase : (Route::has($changeKebab) ? $changeKebab : null);
		$changeActionUrl = $changeResolved ? (route($changeResolved) ?? '#') : '#';
		$guardMsg = Utility::fetchLinkMessage($lang, VW::LV, 'change_action_leave_unavailable')
			?? 'Change leave action route is unavailable. Please contact technical support or your domain administrator.';
		$formatDate = function ($val, $fallback) use ($user) {
			return ($val && is_object($user) && method_exists($user, 'dateFormat')) ? ($user->dateFormat($val) ?? $fallback) : $fallback;
		};
	} catch (\Error $e) {
		Log::error('Error in leaves/action.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in leaves/action.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in leaves/action.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{!! Collective\Html\FormFacade::open(['url' => $changeActionUrl, 'method' => 'post', 'id' => $formId, 'data-guard-msg' => $guardMsg]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <table class="{{ VC::TB }} modal-table">
                    <tbody>
                        <tr role="row">
                            <th>{{ __('Employee') }}</th>
                            <td>{{ data_get($employee ?? null, 'name') ?? __('No employee name available') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Leave Type') }}</th>
                            <td>{{ data_get($leavetype ?? null, 'title') ?? __('No leave type available') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Applied On') }}</th>
                            <td>{{ $formatDate(data_get($leave ?? null,'applied_on'), __('No applied date available')) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Start Date') }}</th>
                            <td>{{ $formatDate(data_get($leave ?? null,'start_date'), __('No start date available')) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('End Date') }}</th>
                            <td>{{ $formatDate(data_get($leave ?? null,'end_date'), __('No end date available')) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Leave Reason') }}</th>
                            <td>{{ data_get($leave ?? null, 'leave_reason') ?? __('No leave reason available') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Status') }}</th>
                            <td>{{ data_get($leave ?? null, 'status') ?? __('No status available') }}</td>
                        </tr>
                        <input type="hidden" value="{{ data_get($leave ?? null,'id','') }}" name="leave_id">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
        <div class="modal-footer">
            <input type="submit" value="{{ __('Approval') }}" class="{{ VC::BT }} btn-success" data-bs-dismiss="modal" name="status">
            <input type="submit" value="{{ __('Reject') }}" class="{{ VC::BT }} btn-danger" name="status">
        </div>
    @endif
    <script defer src="{{ asset('assets/js/routes/leaves/changeAction.js') }}"></script>
{!! Collective\Html\FormFacade::close() !!}
