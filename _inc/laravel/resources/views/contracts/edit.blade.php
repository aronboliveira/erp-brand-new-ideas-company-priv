@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Collection, Str};

    $lang = Utility::fetchUserLang();
@endphp
@if(!empty($contract) && isset($contract->id))
  @php
      $contractUpdateRouteBase        = ViewsConstants::CTC.'.update';
      $contractUpdateRouteKebab       = Str::kebab($contractUpdateRouteBase);
      $contractIdValue                = (string) data_get($contract,'id','');
      $contractUpdateResolvedName     = Route::has($contractUpdateRouteBase)
          ? $contractUpdateRouteBase
          : (Route::has($contractUpdateRouteKebab) ? $contractUpdateRouteKebab : null);
      $contractUpdateUrl              = ($contractUpdateResolvedName && $contractIdValue !== '')
          ? route($contractUpdateResolvedName, $contractIdValue)
          : '#';
      $contractUpdateLang             = isset($lang) ? $lang : Utility::fetchUserLang();
      $contractUpdateGuardMsg         = Utility::fetchLinkMessage($contractUpdateLang, ViewsConstants::CTC, 'update_contract_route_unavailable')
          ?? 'Update contract route is unavailable. Please contact technical support or your domain administrator.';
      $contractUpdateFormId           = 'contract-update-form-'.($contractIdValue === '' ? 'x' : $contractIdValue);
  @endphp
  {!! Form::model($contract, [
      'method'          => 'PUT',
      'url'             => $contractUpdateUrl,
      'id'              => $contractUpdateFormId,
      'data-url'        => $contractUpdateUrl,
      'data-guard-msg'  => $contractUpdateGuardMsg,
      'data-sv-localized' => 'true',
  ]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if ($plan?->{PlansConstants::COL_GPT} == 1)
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
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('subject', __('Subject'), ['class' => VC::FM_LB]) }}
                {{ Form::text('subject', null, ['class' => VC::FM_CT, 'required' => true]) }}
            </div>

            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('client_name', __('Client'), ['class' => VC::FM_LB]) }}
                @if(!empty($clients) && ((is_array($clients) && count($clients)) || ($clients instanceof Collection && $clients->isNotEmpty())))
                    {{ Form::select('client_name', $clients, null, [
                        'class' => VC::FM_CT_SL . ' client_select',
                        'id'    => 'client_select',
                    ]) }}
                @else
                    {{ Form::select('client_name', [__('No clients available')], null, [
                        'class' => VC::FM_CT_SL . ' client_select',
                        'id'    => 'client_select',
                    ]) }}
                @endif
            </div>

            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('project', __('Project'), ['class' => VC::FM_LB]) }}
                <div class="project-div">
                    @if(!empty($project) && ((is_array($project) && count($project)) || ($project instanceof Collection && $project->isNotEmpty())))
                        {{ Form::select('project', $project, null, [
                            'class' => VC::FM_CT_SL . ' project_select',
                            'id'    => 'project_id',
                            'name'  => 'project_id',
                        ]) }}
                    @else
                        {{ Form::select('project', [__('No projects available')], null, [
                            'class' => VC::FM_CT_SL . ' project_select',
                            'id'    => 'project_id',
                            'name'  => 'project_id',
                        ]) }}
                    @endif
                </div>
            </div>

            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('type', __('Contract Type'), ['class' => VC::FM_LB]) }}
                @if(!empty($contractTypes) && ((is_array($contractTypes) && count($contractTypes)) || ($contractTypes instanceof Collection && $contractTypes->isNotEmpty())))
                    {{ Form::select('type', $contractTypes, null, [
                        'class'       => VC::FM_CT_SL,
                        'data-toggle' => 'select',
                        'required'    => true,
                    ]) }}
                @else
                    {{ Form::select('type', [__('No contract types available')], null, [
                        'class'       => VC::FM_CT_SL,
                        'data-toggle' => 'select',
                        'required'    => true,
                    ]) }}
                @endif
            </div>

            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('value', __('Contract Value'), ['class' => VC::FM_LB]) }}
                {{ Form::number('value', null, [
                    'class'    => VC::FM_CT,
                    'required' => true,
                    'step'     => '0.01'
                ]) }}
            </div>

            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('start_date', null, [
                    'class'    => VC::FM_CT,
                    'required' => true
                ]) }}
            </div>

            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('end_date', null, [
                    'class'    => VC::FM_CT,
                    'required' => true
                ]) }}
            </div>
        </div>
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, [
                    'class' => VC::FM_CT,
                    'rows'  => 3
                ]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/contracts/lang/edit.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/contracts/editList.js') }}"></script>
    <script defer>
        (() => {
            try {
                const form = document.getElementById('{{ $contractUpdateFormId }}');
                if (!form) { return; }
                if (form.getAttribute('data-listener-active') === 'true') { return; }
                form.setAttribute('data-listener-active','true');

                form.addEventListener('submit', (e) => {
                    try {
                        const action = form.getAttribute('action') ?? '#';
                        const url    = form.getAttribute('data-url') ?? action ?? '#';
                        if (url !== '#' && action !== '#') { return; }

                        e.preventDefault();

                        const msg = form.getAttribute('data-guard-msg') ?? 'Update contract route is unavailable. Please contact technical support or your domain administrator.';
                        const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);

                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            document.body.appendChild(container);
                        }

                        if (hasBootstrap) {
                            const toast = document.createElement('div');
                            toast.className = 'toast';
                            toast.setAttribute('role','alert');
                            toast.setAttribute('aria-live','assertive');
                            toast.setAttribute('aria-atomic','true');

                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;

                            toast.appendChild(body);
                            container.appendChild(toast);
                            bootstrap.Toast.getOrCreateInstance(toast).show();
                        } else {
                            alert(msg);
                        }

                        form.setAttribute('data-failed-route','true');
                    } catch (err) {}
                });
            } catch (err) {}
        })();
    </script>
  {!! Form::close() !!}
@else
    <div class="text-center">
        <h5 class="text-danger">{{ __('Contract data is not available.') }}</h5>
    </div>
@endif

