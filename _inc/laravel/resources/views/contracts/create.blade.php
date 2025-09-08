@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        StacksConstants
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};

    $lang = Utility::fetchUserLang();
@endphp

{{ Form::open(['url' => ViewsConstants::CTC]) }}
  <div class="modal-body">
      {{-- start for ai module --}}
      @php $plan = Utility::getChatGPTSettings(); @endphp
      @if($plan?->{PlansConstants::COL_GPT} == 1)
      <div class="text-end">
        @php
            $aiGenerateContractRouteBase    = 'generate';
            $aiGenerateContractRouteKebab   = Str::kebab($aiGenerateContractRouteBase);
            $aiGenerateContractResolvedName = Route::has($aiGenerateContractRouteBase)
                ? $aiGenerateContractRouteBase
                : (Route::has($aiGenerateContractRouteKebab) ? $aiGenerateContractRouteKebab : null);
            $aiGenerateContractTopic        = 'contract';
            $aiGenerateContractUrl          = $aiGenerateContractResolvedName ? route($aiGenerateContractResolvedName, [$aiGenerateContractTopic]) : '#';
            $aiGenerateContractLang         = $lang ?? Utility::fetchUserLang();
            $aiGenerateContractGuardMsg     = Utility::fetchLinkMessage($aiGenerateContractLang, ViewsConstants::CTC, 'generate_ai_contract_route_unavailable') ?? 'Generate AI contract route is unavailable. Please contact technical support or your domain administrator.';
            $aiGenerateContractLinkId       = 'ai-generate-contract-link';
        @endphp
        <a href="{{ $aiGenerateContractUrl }}"
           id="{{ $aiGenerateContractLinkId }}"
           data-size="md"
           class="{{ VC::BT_SM_PM }} btn-icon"
           data-ajax-popup-over="true"
           data-url="{{ $aiGenerateContractUrl }}"
           data-bs-placement="top"
           data-title="{{ __('Generate content with AI') }}"
           data-guard-msg="{{ $aiGenerateContractGuardMsg }}"
           data-sv-localized="true">
            <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
        </a>
        <script defer src="{{ asset('assets/js/routes/contracts/generate.js') }}"></script>
      </div>
      @endif
      {{-- end for ai module --}}
      <div class="{{ VC::RW }}">
          <div class="{{ VC::FM_GCB12 }}">
              {{ Form::label('subject', __('Subject'), ['class' => VC::FM_LB]) }}
              {{ Form::text('subject', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
          </div>

          <div class="{{ VC::FM_GCB6 }}">
              {{ Form::label('client_name', __('Client'), ['class' => VC::FM_LB]) }}
              @if((is_array($clients) && count($clients)) || ($clients instanceof Collection && $clients->isNotEmpty()))
                  {{ Form::select('client_name', $clients, null, ['class' => VC::FM_CT_SL . ' client_select', 'id' => 'client_select']) }}
              @else
                  {{ Form::select('client_name', [__('No clients available')], null, ['class' => VC::FM_CT_SL . ' client_select', 'id' => 'client_select']) }}
              @endif
          </div>

          <div class="{{ VC::FM_GCB6 }}">
              {{ Form::label('projects', __('Projects'), ['class' => VC::FM_LB]) }}
              <select class="{{ VC::FM_CT_SL }} project_select" id="project_id" name="project_id">
                  <option value="">{{ __('Select Project') }}</option>
              </select>
          </div>

          <div class="{{ VC::FM_GCB6 }}">
              {{ Form::label('type', __('Contract Type'), ['class' => VC::FM_LB]) }}
              @if((is_array($contractTypes) && count($contractTypes)) || ($contractTypes instanceof Collection && $contractTypes->isNotEmpty()))
                  {{ Form::select('type', $contractTypes, null, ['class' => VC::FM_CT, 'data-toggle' => 'select', 'required' => 'required']) }}
              @else
                  {{ Form::select('type', [__('No contract types available')], null, ['class' => VC::FM_CT, 'data-toggle' => 'select', 'required' => 'required']) }}
              @endif
          </div>

          <div class="{{ VC::FM_GCB6 }}">
              {{ Form::label('value', __('Contract Value'), ['class' => VC::FM_LB]) }}
              {{ Form::number('value', '', ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
          </div>

          <div class="{{ VC::FM_GCB6 }}">
              {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
              {{ Form::date('start_date', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
          </div>

          <div class="{{ VC::FM_GCB6 }}">
              {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
              {{ Form::date('end_date', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
          </div>
      </div>

      <div class="{{ VC::RW }}">
          <div class="{{ VC::FM_GCB12 }}">
              {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
              {!! Form::textarea('description', null, ['class' => VC::FM_CT, 'rows' => '3']) !!}
          </div>
      </div>
  </div>

  <div class="modal-footer">
      <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
      <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
  </div>
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/contracts/lang/list.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/contracts/list.js') }}"></script>
{{ Form::close() }}
