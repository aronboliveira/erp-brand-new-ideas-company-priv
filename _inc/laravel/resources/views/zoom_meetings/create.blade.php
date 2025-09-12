@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route, Str};

    $lang = Utility::fetchUserLang();

    $storeBase   = VW::ZMM . '.store';
    $storeKebab  = Str::kebab($storeBase);
    $storeName   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl    = $storeName ? route($storeName) : '#';
    $storeGuard  = Utility::fetchLinkMessage($lang, VW::ZMM, 'store_zoom_meeting_route_unavailable') ?? 'Store zoom meeting route is unavailable. Please contact technical support or your domain administrator.';
    $formId      = 'store_zoom_meeting';
@endphp

{!! Form::open([
    'url'  => $storeUrl,
    'method' => 'post',
    'id'     => $formId,
    'data-resolved-action' => $storeUrl,
    'data-guard-msg'       => $storeGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            @php
                $aiBase   = 'generate';
                $aiName   = Route::has($aiBase) ? $aiBase : (Route::has(Str::kebab($aiBase)) ? Str::kebab($aiBase) : null);
                $aiUrl    = $aiName ? route($aiName, ['zoom meeting']) : '#';
                $aiGuard  = Utility::fetchLinkMessage($lang, VW::ZMM, 'generate_ai_unavailable') ?? 'AI generation route is unavailable. Please contact technical support or your domain administrator.';
                $aiId     = 'zoom-ai-generate-link';
            @endphp
            <div class="text-end">
                <a id="{{ $aiId }}"
                   href="{{ $aiUrl }}"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-url="{{ $aiUrl }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}"
                   data-guard-msg="{{ $aiGuard }}"
                   data-sv-localized="true">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
            <script defer src="{{ asset('assets/js/routes/zoomMeetings/generate.js') }}"></script>
        @endif

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Meeting Title'), 'required' => true]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('project_id', __('Project'), ['class' => VC::FM_LB]) }}
                {{ Form::select('project_id', $projects ?? [], null, ['class' => VC::FM_CT_SL . ' project_select', 'id' => 'project_select', 'data-toggle' => 'select']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('user_id', __('Users'), ['class' => VC::FM_LB]) }}
                <div id="user_div">
                    <select class="{{ VC::FM_CT_SL }} employee_select" id="user_id" name="user_id[]">
                        <option value="">{{ __('Select User') }}</option>
                    </select>
                </div>
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('start_date', __('Start Date / Time'), ['class' => VC::FM_LB]) }}
                {{ Form::input('datetime-local', 'start_date', null, ['class' => VC::FM_CT . ' date', 'placeholder' => __('Select Date/Time'), 'required' => true]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('duration', __('Duration'), ['class' => VC::FM_LB]) }}
                {{ Form::number('duration', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Duration'), 'required' => true]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('password', __('Password ( Optional )'), ['class' => VC::FM_LB]) }}
                {{ Form::password('password', ['class' => VC::FM_CT, 'placeholder' => __('Enter Password')]) }}
            </div>

            @if(!empty($settings) && isset($settings['google_calendar_enable']) && $settings['google_calendar_enable'] == 'on')
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('synchronize_type', __('Synchronize in Google Calendar ?'), ['class' => VC::FM_LB]) }}
                    <div class="form-switch">
                        <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calendar">
                        <label class="form-check-label" for="switch-shadow"></label>
                    </div>
                </div>
            @endif

            <div class="{{ VC::FM_GCB6 }}">
                <div class="form-switch form-switch-right">
                    <input class="form-check-input" type="checkbox" name="client_id" id="client_id" checked>
                    <label class="form-check-label" for="client_id">{{ __('Invite Client For Zoom Meeting') }}</label>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>

    <script async src="{{ asset('assets/js/routes/zoomMeetings/lang/users.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/zoomMeetings/users.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/zoomMeetings/store.js') }}"></script>
{!! Form::close() !!}

    







