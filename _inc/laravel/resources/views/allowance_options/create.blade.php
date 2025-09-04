@php 
    use App\Config\Constants\{ViewClassNamesConstants, ViewsConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form; 
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
    $storeRoute = Route::has(ViewsConstants::ALW_OPT)
        ? route(ViewsConstants::ALW_OPT)
        : '#';
    $formId = 'allowance-option-store-form';
    $storeMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::ALW_OPT,
        'allowance_option_store_route_unavailable'
    ) ?? 'Allowance option store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'url'              => $storeRoute,
    'method'           => 'post',
    'id'               => $formId,
    'data-url'         => $storeRoute,
    'data-sv-localized'=> 'true',
    'data-guard-msg'   => $storeMsg,
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
        <input type="submit" value="{{ __('Create') }}" class="{{ ViewClassNamesConstants::BT_PRM }}">
    </div>
    <script async src="{{ asset('assets/js/routes/allowancesOptions/lang/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/allowancesOptions/store.js') }}"></script>
{{ Form::close() }}
