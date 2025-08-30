@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        StacksConstants
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang = Utility::fetchUserLang();
    $updateBase     = VW::TRV . '.update';
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has(Str::kebab($updateBase)) ? Str::kebab($updateBase) : null);
    $updateUrl      = $updateResolved ? route($updateResolved, [$travel->id]) : url(VW::TRV . '/' . $travel->id);
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::TRV, 'update_travel_route_unavailable') ?? 'Update travel route is unavailable. Please contact technical support or your domain administrator.';
    $genResolved   = Route::has('generate') ? 'generate' : (Route::has(Str::kebab('generate')) ? Str::kebab('generate') : null);
    $genUrl        = $genResolved ? route($genResolved, ['travel']) : '#';
    $genGuard      = Utility::fetchLinkMessage($lang, VW::TRV, 'generate_edit_route_unavailable') ?? 'Generate content route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{!! Form::open([
    'url'  => $updateUrl,
    'method' => 'PUT',
    'id'     => 'edit_travel',
    'data-guard-msg' => $updateGuard,
    'data-sv-localized' => 'true',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                <a href="{{ $genUrl }}"
                   id="travel-generate-link"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-url="{{ $genUrl }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}"
                   data-guard-msg="{{ $genGuard }}"
                   data-sv-localized="true">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                {{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('start_date', $travel->start_date ?? null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('end_date', $travel->end_date ?? null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('purpose_of_visit', __('Purpose of Trip'), ['class' => VC::FM_LB]) }}
                {{ Form::text('purpose_of_visit', $travel->purpose_of_visit ?? null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('place_of_visit', __('Country'), ['class' => VC::FM_LB]) }}
                {{ Form::text('place_of_visit', $travel->place_of_visit ?? null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', $travel->description ?? null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/travels/update.js') }}"></script>
    @if($plan?->{PlansConstants::COL_GPT} == 1)
        <script defer src="{{ asset('assets/js/routes/travels/generateEdit.js') }}"></script>
    @endif
{!! Form::close() !!}

