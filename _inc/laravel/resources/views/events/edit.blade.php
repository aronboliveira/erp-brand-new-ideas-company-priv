@php
    use App\Config\Constants\{PlansConstants, ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Gate, Route, URL};

    $lang = Utility::fetchUserLang();
    $canEdit = Gate::check('edit event');
    $updateName = VW::EVT.'.update';
    $updateUrl = ($canEdit && Route::has($updateName)) ? route($updateName, $event->id) : '#';
    $updateGuard = Utility::fetchLinkMessage($lang, VW::EVT, 'update_route_unavailable') ?? 'Event update route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@if(!empty($event) && isset($event->id) && $canEdit)
    {!! Form::model($event, [
        'url' => $updateUrl,
        'method' => 'PUT',
        'id' => 'edit_event_form',
        'data-action-url' => $updateUrl,
        'data-guard-msg' => $updateGuard,
        'data-sv-localized' => 'true',
    ]) !!}
        <div class="modal-body">
            @php($plan = Utility::getChatGPTSettings())
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                @php
                    $genHref   = Route::has('generate') ? route('generate', ['event']) : '#';
                    $genMsg    = Utility::fetchLinkMessage($lang, VW::EVT, 'ai_generate_route_unavailable') ?? 'AI generate route is unavailable. Please contact technical support or your domain administrator.';
                    $genLinkId = 'event-generate-ai-link';
                @endphp
                <div class="text-end">
                    <a href="#"
                    id="{{ $genLinkId }}"
                    data-size="md"
                    class="{{ VC::BT_SM_PM }} btn-icon"
                    data-ajax-popup-over="true"
                    data-url="{{ $genHref }}"
                    data-sv-localized="true"
                    data-guard-msg="{{ $genMsg }}"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}">
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
                <script defer src="{{ asset('assets/js/routes/events/generate.js') }}"></script>
            @endif

            <div class="{{ VC::RW }}">
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('title', __('Event Title'), ['class' => VC::FM_LB]) }}
                        {{ Form::text('title', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Event Title')]) }}
                    </div>
                </div>
            </div>

            <div class="{{ VC::RW }}">
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('start_date', __('Event Start Date'), ['class' => VC::FM_LB]) }}
                        {{ Form::date('start_date', null, ['class' => VC::FM_CT]) }}
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('end_date', __('Event End Date'), ['class' => VC::FM_LB]) }}
                        {{ Form::date('end_date', null, ['class' => VC::FM_CT]) }}
                    </div>
                </div>
            </div>

            <div class="{{ VC::RW }}">
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('color', __('Event Select Color'), ['class' => VC::FM_LB.' d-block mb-3']) }}
                        <div class="btn-group-toggle btn-group-colors event-tag" data-toggle="buttons">
                            <label class="btn bg-info p-3 {{ $event->color == 'event-info' ? 'custom_color_radio_button' : '' }}">
                                <input type="radio" name="color" class="d-none" value="event-info" {{ $event->color == 'event-info' ? 'checked' : '' }}>
                            </label>
                            <label class="btn bg-warning p-3 {{ $event->color == 'event-warning' ? 'custom_color_radio_button' : '' }}">
                                <input type="radio" name="color" class="d-none" value="event-warning" {{ $event->color == 'event-warning' ? 'checked' : '' }}>
                            </label>
                            <label class="btn bg-danger p-3 {{ $event->color == 'event-danger' ? 'custom_color_radio_button' : '' }}">
                                <input type="radio" name="color" class="d-none" value="event-danger" {{ $event->color == 'event-danger' ? 'checked' : '' }}>
                            </label>
                            <label class="btn bg-primary p-3 {{ $event->color == 'event-success' ? 'custom_color_radio_button' : '' }}">
                                <input type="radio" name="color" class="d-none" value="event-success" {{ $event->color == 'event-success' ? 'checked' : '' }}>
                            </label>
                            <label class="btn p-3 {{ $event->color == 'event-primary' ? 'custom_color_radio_button' : '' }}" style="background-color:#51459d!important">
                                <input type="radio" name="color" class="d-none" value="event-primary" {{ $event->color == 'event-primary' ? 'checked' : '' }}>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="{{ VC::RW }}">
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('description', __('Event Description'), ['class' => VC::FM_LB]) }}
                        {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Event Description')]) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>

        <script async src="{{ asset('assets/js/routes/events/lang/edit.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/events/picker.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/events/edit.js') }}"></script>
    {!! Form::close() !!}
@else
    <div class="modal-body">
        <div class="{{ VC::ALERT }} {{ VC::ALERT_DANGER }}">
            {{ __('Failed to load event data.') }}
        </div>
    </div>
@endif