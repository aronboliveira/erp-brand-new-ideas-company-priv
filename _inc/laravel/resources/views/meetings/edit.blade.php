@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;

    $lang        = Utility::fetchUserLang();
    $hasMeeting  = !empty($meeting ?? null) && data_get($meeting, 'id');

    $updateName  = VW::MT . '.update';
    $updateUrl   = ($hasMeeting && Route::has($updateName)) ? route($updateName, $meeting->id) : '#';
    $updateGuard = Utility::fetchLinkMessage($lang, VW::MT, 'update_route_unavailable')
                   ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');

    $plan        = Utility::getChatGPTSettings();
    $aiEnabled   = (bool) ($plan?->{PlansConstants::COL_GPT} ?? false);

    $aiRouteName = 'generate';
    $aiUrl       = ($aiEnabled && Route::has($aiRouteName)) ? route($aiRouteName, ['meeting']) : '#';
    $aiGuard     = Utility::fetchLinkMessage($lang, VW::MT, 'generate_route_unavailable')
                   ?? __('Generate route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@if($hasMeeting)
    {{ Form::model($meeting, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'meeting-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true'
    ]) }}
        <div class="modal-body">
            @if($aiEnabled)
                <div class="text-end">
                    <a
                        href="#"
                        id="ai-generate-meeting-btn"
                        data-size="md"
                        class="{{ VC::BT_SM_PM }} btn-icon"
                        data-ajax-popup-over="true"
                        data-url="{{ $aiUrl }}"
                        data-guard-msg="{{ $aiGuard }}"
                        data-bs-placement="top"
                        data-title="{{ __('Generate content with AI') }}"
                    >
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif

            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    <div class="form-group">
                        {{ Form::label('title', __('Meeting Title'), ['class' => VC::FM_LB]) }}
                        {{ Form::text('title', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Meeting Title')]) }}
                    </div>
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    <div class="form-group">
                        {{ Form::label('date', __('Meeting Date'), ['class' => VC::FM_LB]) }}
                        {{ Form::date('date', null, ['class' => VC::FM_CT]) }}
                    </div>
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    <div class="form-group">
                        {{ Form::label('time', __('Meeting Time'), ['class' => VC::FM_LB]) }}
                        {{ Form::time('time', null, ['class' => VC::FM_CT . ' timepicker']) }}
                    </div>
                </div>

                <div class="{{ VC::FM_GCB12 }}">
                    <div class="form-group">
                        {{ Form::label('note', __('Meeting Note'), ['class' => VC::FM_LB]) }}
                        {{ Form::textarea('note', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Meeting Note')]) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/meetings/update.js') }}"></script>
        @if($aiEnabled)
            <script defer src="{{ asset('assets/js/routes/meetings/generateUpdate.js') }}"></script>
        @endif
    {{ Form::close() }}

@else
    <p>{{ __('The requested meeting could not be found or is unavailable.') }}</p>
@endif
