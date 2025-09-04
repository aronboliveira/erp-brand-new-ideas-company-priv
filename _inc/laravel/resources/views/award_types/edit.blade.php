@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as C, StacksConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang = Utility::fetchUserLang();
    $updateRoute = Route::has(ViewsConstants::AWD_TP.'.update')
        ? route(ViewsConstants::AWD_TP.'.update', $awardtype->id)
        : Route::has(Str::kebab(ViewsConstants::AWD_TP.'.update'))
            ? route(Str::kebab(ViewsConstants::AWD_TP.'.update'), $awardtype->id)
            : '#';
    $formId = 'awardtype-update-form';
    $updateMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::AWD_TP,
        'award_type_update_route_unavailable'
    ) ?? 'Award Type update route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@if(!empty($awardtype) && isset($awardtype?->id))
    {{ Form::model($awardtype, [
        'route'             => [$updateRoute],
        'method'            => 'PUT',
        'id'                => $formId,
        'data-url'          => $updateRoute,
        'data-sv-localized' => 'true',
        'data-guard-msg'    => $updateMsg,
    ]) }}
        <div class="modal-body">
            <div class="{{ C::RW }}">
                <div class="col-md-12">
                    <div class="{{ C::FM_GB3 }}">
                        {{ Form::label('name', __('Name'), ['class'=>C::FM_LB]) }}<span class="text-danger">*</span>
                        {{ Form::text('name', null, ['class'=>C::FM_CT,'placeholder'=>__('Enter Award Type Name'),'required'=>'required']) }}
                        @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="{{ C::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="{{ C::BT_PRM }}">{{ __('Update') }}</button>
        </div>
        <script defer src="{{ asset('assets/js/routes/awardTypes/edit.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="{{ C::ALERT }} {{ C::ALERT_DANGER }}">
                    <h4 class="text-danger">{{ __('No Award Type found') }}</h4>
                    <p>{{ __('The award type data is invalid or not found. Please refresh the page and try again.') }}</p>
                </div>
            </div>
        </div>
    </div>
@endif

