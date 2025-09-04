@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as C, StacksConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang = Utility::fetchUserLang();
    $storeRoute = Route::has(ViewsConstants::AWD_TP)
        ? route(ViewsConstants::AWD_TP)
        : Route::has(Str::kebab(ViewsConstants::AWD_TP))
            ? route(Str::kebab(ViewsConstants::AWD_TP))
            : '#';
    $formId = 'awardtype-store-form';
    $storeMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::AWD_TP,
        'award_type_store_route_unavailable'
    ) ?? 'Award Type store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'url'               => $storeRoute,
    'method'            => 'post',
    'id'                => $formId,
    'data-url'          => $storeRoute,
    'data-sv-localized' => 'true',
    'data-guard-msg'    => $storeMsg,
]) }}
    <div class="modal-body">
        <div class="{{ C::RW }}">
            <div class="col-md-12">
                <div class="{{ C::FM_GB3 }}">
                    {{ Form::label('name', __('Name'), ['class'=>C::FM_LB]) }}<span class="text-danger">*</span>
                    {{ Form::text('name', null, ['class'=>C::FM_CT,'placeholder'=>__('Enter Award Type Name')]) }}
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ C::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ C::BT_PRM }}">{{ __('Create') }}</button>
    </div>
    <script defer src="{{ asset('assets/js/routes/awardTypes/store.js') }}"></script>
{{ Form::close() }}

