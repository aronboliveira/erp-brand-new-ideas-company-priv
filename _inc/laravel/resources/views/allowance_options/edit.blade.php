@php 
    use App\Config\Constants\{ViewClassNamesConstants, ViewsConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form; 
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
    $updateRoute = Route::has(ViewsConstants::ALW_OPT.'.update')
        ? route(ViewsConstants::ALW_OPT.'.update', $allowanceoption->id)
        : '#';
    $formId = 'allowance-option-update-form';
    $updateMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::ALW_OPT,
        'allowance_option_update_route_unavailable'
    ) ?? 'Allowance option update route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@if(!empty($allowanceoption) && isset($allowanceoption?->id))
    {{ Form::model($allowanceoption, [
        'route'             => [$updateRoute],
        'method'            => 'PUT',
        'id'                => $formId,
        'data-url'          => $updateRoute,
        'data-sv-localized' => 'true',
        'data-guard-msg'    => $updateMsg,
    ]) }}
        <div class="modal-body">
            <div class="{{ ViewClassNamesConstants::RW }}">
                <div class="{{ ViewClassNamesConstants::C12 }}">
                    <div class="{{ ViewClassNamesConstants::FM_G }}">
                        {{ Form::label('name', __('Name'), ['class'=>ViewClassNamesConstants::FM_LB]) }}<span class="text-danger">*</span>
                        {{ Form::text('name', null, ['class'=>ViewClassNamesConstants::FM_CT, 'placeholder'=>__('Enter Allowance option Name')]) }}
                        @error('name')
                            <span class="invalid-name" role="alert">
                                <strong class="text-danger">{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ ViewClassNamesConstants::BT_PRM }}">
        </div>
        <script async src="{{ asset('assets/js/routes/allowanceOptions/lang/edit.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/allowanceOptions/edit.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="{{ ViewClassNamesConstants::ALERT }} {{ ViewClassNamesConstants::ALERT_DANGER }}">
                    <h4 class="text-danger">{{ __('No Allowance Option found') }}</h4>
                    <p>{{ __('The allowance option data is invalid or not found. Please refresh the page and try again.') }}</p>
                </div>
            </div>
        </div>
    </div>
@endif
