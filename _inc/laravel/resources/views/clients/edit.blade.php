@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;

    $lang = Utility::fetchUserLang();
    $updateRouteName = VW::CLT . '.update';
    $updateGuard = Utility::fetchLinkMessage($lang, VW::CLT, 'update_client_route_unavailable')
        ?? __('Update client route is unavailable. Please contact technical support or your domain administrator.');

    $formParams = [
        'method'            => 'PUT',
        'id'                => 'edit_client',
        'data-sv-localized' => 'true',
        'data-guard-msg'    => $updateGuard,
        'data-action-href'  => Route::has($updateRouteName) ? route($updateRouteName, $client->id) : '#',
    ];

    if (Route::has($updateRouteName)) {
        $formParams['route'] = [$updateRouteName, $client->id];
    } else {
        $formParams['url'] = '#';
    }
@endphp

{{ Form::model($client, $formParams) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Client Name'), 'required' => 'required']) }}
                @error('name')
                    <small class="invalid-name" role="alert"><strong class="{{ VC::TXT_MT }}">{{ $message }}</strong></small>
                @enderror
            </div>

            <div class="{{ VC::FM_G }}">
                {{ Form::label('email', __('E-Mail Address'), ['class' => VC::FM_LB]) }}
                {{ Form::email('email', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Client Email'), 'required' => 'required']) }}
                @error('email')
                    <small class="invalid-email" role="alert"><strong class="{{ VC::TXT_MT }}">{{ $message }}</strong></small>
                @enderror
            </div>

            @if(!empty($customFields) && method_exists($customFields,'isEmpty') ? !$customFields->isEmpty() : !empty($customFields))
                @include(VW::CST_FD.'.formBuilder')
            @endif
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
    </div>

    <script defer src="{{ asset('assets/js/routes/clients/update.js') }}"></script>
{{ Form::close() }}
