@php
    use App\Config\Constants\{
        ViewClassNamesConstants as VC,
        ViewsConstants as VW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route, Auth};
    use Illuminate\Support\{Collection, Str};

    $user        = Auth::user();
    $lang        = Utility::fetchUserLang(user: $user);
    $hasLoanOpt  = !empty($loanoption ?? null) && data_get($loanoption, 'id');

    $updateBase     = VW::LN_OPT . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasLoanOpt) ? route($updateResolved, $loanoption->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::LN_OPT, 'update_route_unavailable')
                        ?? 'Update route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@if($hasLoanOpt)
    {{ Form::model($loanoption, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'loanOption-edit-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('name', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Loan Option Name')]) }}
                    @error('name')
                        <span class="invalid-name" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/loans/options/update.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="p-3">
        {{ __('The requested loan option could not be found.') }}
    </div>
@endif
