@php
	try {
		\tuse App\\Config\\Constants\\{PlansConstants, ViewClassNamesConstants as VC, ViewsConstants};
		\tuse App\\Models\\Utility;
		\tuse Collective\\Html\\FormFacade as Form;
		\tuse Illuminate\\Support\\Facades\\{Log, Route};
		\tuse Illuminate\\Support\\{Collection, Str};
		\t$lang ??= 'en';
		\ttry {
		\t\t$lang = Utility::fetchUserLang() ?? 'en';
		\t} catch (\\Error $e) {
		\t\tLog::error('Error in contracts/edit.blade.php main @php block', [
		\t\t\t'exception_class' => get_class($e),
		\t\t\t'message' => $e->getMessage(),
		\t\t\t'file' => $e->getFile(),
		\t\t\t'line' => $e->getLine(),
		\t\t]);
		\t} catch (\\Exception $e) {
		\t\tLog::error('Exception in contracts/edit.blade.php main @php block', [
		\t\t\t'exception_class' => get_class($e),
		\t\t\t'message' => $e->getMessage(),
		\t\t\t'file' => $e->getFile(),
		\t\t\t'line' => $e->getLine(),
		\t\t]);
		\t} catch (\\Throwable $e) {
		\t\tLog::error('Throwable in contracts/edit.blade.php main @php block', [
		\t\t\t'exception_class' => get_class($e),
		\t\t\t'message' => $e->getMessage(),
		\t\t\t'file' => $e->getFile(),
		\t\t\t'line' => $e->getLine(),
		\t\t]);
		\t}
	} catch (\Throwable $e) {
		\Log::error('contracts/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp
@if(!empty($contract) && isset($contract->id))
  @php
	try {
		\t\tuse Illuminate\\Support\\Facades\\Log as UpdateLog;
		\t\t$contractUpdateRouteBase ??= '';
		\t\t$contractUpdateRouteKebab ??= '';
		\t\t$contractIdValue ??= '';
		\t\t$contractUpdateResolvedName ??= null;
		\t\t$contractUpdateUrl ??= '#';
		\t\t$contractUpdateLang ??= 'en';
		\t\t$contractUpdateGuardMsg ??= '';
		\t\t$contractUpdateFormId ??= 'contract-update-form-x';
		\t\ttry {
		\t\t\t$contractUpdateRouteBase = ViewsConstants::CTC . '.update';
		\t\t\t$contractUpdateRouteKebab = Str::kebab($contractUpdateRouteBase);
		\t\t\t$contractIdValue = (string) data_get($contract ?? null, 'id', '');
		\t\t\t$contractUpdateResolvedName = Route::has($contractUpdateRouteBase)
		\t\t\t\t? $contractUpdateRouteBase
		\t\t\t\t: (Route::has($contractUpdateRouteKebab) ? $contractUpdateRouteKebab : null);
		\t\t\t$contractUpdateUrl = ($contractUpdateResolvedName && $contractIdValue !== '')
		\t\t\t\t? (route($contractUpdateResolvedName, $contractIdValue) ?? '#')
		\t\t\t\t: '#';
		\t\t\t$contractUpdateLang = isset($lang) ? $lang : (Utility::fetchUserLang() ?? 'en');
		\t\t\t$contractUpdateGuardMsg = Utility::fetchLinkMessage($contractUpdateLang, ViewsConstants::CTC, 'update_contract_route_unavailable')
		\t\t\t\t?? 'Update contract route is unavailable. Please contact technical support or your domain administrator.';
		\t\t\t$contractUpdateFormId = 'contract-update-form-' . ($contractIdValue === '' ? 'x' : $contractIdValue);
		\t\t} catch (\\Error $e) {
		\t\t\tUpdateLog::error('Error in contracts/edit.blade.php update @php block', [
		\t\t\t\t'exception_class' => get_class($e),
		\t\t\t\t'message' => $e->getMessage(),
		\t\t\t\t'file' => $e->getFile(),
		\t\t\t\t'line' => $e->getLine(),
		\t\t\t]);
		\t\t} catch (\\Exception $e) {
		\t\t\tUpdateLog::error('Exception in contracts/edit.blade.php update @php block', [
		\t\t\t\t'exception_class' => get_class($e),
		\t\t\t\t'message' => $e->getMessage(),
		\t\t\t\t'file' => $e->getFile(),
		\t\t\t\t'line' => $e->getLine(),
		\t\t\t]);
		\t\t} catch (\\Throwable $e) {
		\t\t\tUpdateLog::error('Throwable in contracts/edit.blade.php update @php block', [
		\t\t\t\t'exception_class' => get_class($e),
		\t\t\t\t'message' => $e->getMessage(),
		\t\t\t\t'file' => $e->getFile(),
		\t\t\t\t'line' => $e->getLine(),
		\t\t\t]);
		\t\t}
	} catch (\Throwable $e) {
		\Log::error('contracts/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
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
        @php
	try {
		($plan = Utility::getChatGPTSettings())
		        @if ($plan?->{PlansConstants::COL_GPT} == 1)
		            <div class="{{ VC::TX_END }}">
		              @php
		\t\t\t\t\tuse Illuminate\\Support\\Facades\\Log as AiLog;
		\t\t\t\t\t$aiGenerateContractRouteBase ??= 'generate';
		\t\t\t\t\t$aiGenerateContractRouteKebab ??= '';
		\t\t\t\t\t$aiGenerateContractResolvedName ??= null;
		\t\t\t\t\t$aiGenerateContractTopic ??= 'contract';
		\t\t\t\t\t$aiGenerateContractUrl ??= '#';
		\t\t\t\t\t$aiGenerateContractLang ??= 'en';
		\t\t\t\t\t$aiGenerateContractGuardMsg ??= '';
		\t\t\t\t\t$aiGenerateContractLinkId ??= 'ai-generate-contract-link';
		\t\t\t\t\ttry {
		\t\t\t\t\t\t$aiGenerateContractRouteKebab = Str::kebab($aiGenerateContractRouteBase);
		\t\t\t\t\t\t$aiGenerateContractResolvedName = Route::has($aiGenerateContractRouteBase)
		\t\t\t\t\t\t\t? $aiGenerateContractRouteBase
		\t\t\t\t\t\t\t: (Route::has($aiGenerateContractRouteKebab) ? $aiGenerateContractRouteKebab : null);
		\t\t\t\t\t\t$aiGenerateContractUrl = $aiGenerateContractResolvedName ? (route($aiGenerateContractResolvedName, [$aiGenerateContractTopic]) ?? '#') : '#';
		\t\t\t\t\t\t$aiGenerateContractLang = $lang ?? (Utility::fetchUserLang() ?? 'en');
		\t\t\t\t\t\t$aiGenerateContractGuardMsg = Utility::fetchLinkMessage($aiGenerateContractLang, ViewsConstants::CTC, 'generate_ai_contract_route_unavailable') ?? 'Generate AI contract route is unavailable. Please contact technical support or your domain administrator.';
		\t\t\t\t\t} catch (\\Error $e) {
		\t\t\t\t\t\tAiLog::error('Error in contracts/edit.blade.php AI generate @php block', [
		\t\t\t\t\t\t\t'exception_class' => get_class($e),
		\t\t\t\t\t\t\t'message' => $e->getMessage(),
		\t\t\t\t\t\t\t'file' => $e->getFile(),
		\t\t\t\t\t\t\t'line' => $e->getLine(),
		\t\t\t\t\t\t]);
		\t\t\t\t\t} catch (\\Exception $e) {
		\t\t\t\t\t\tAiLog::error('Exception in contracts/edit.blade.php AI generate @php block', [
		\t\t\t\t\t\t\t'exception_class' => get_class($e),
		\t\t\t\t\t\t\t'message' => $e->getMessage(),
		\t\t\t\t\t\t\t'file' => $e->getFile(),
		\t\t\t\t\t\t\t'line' => $e->getLine(),
		\t\t\t\t\t\t]);
		\t\t\t\t\t} catch (\\Throwable $e) {
		\t\t\t\t\t\tAiLog::error('Throwable in contracts/edit.blade.php AI generate @php block', [
		\t\t\t\t\t\t\t'exception_class' => get_class($e),
		\t\t\t\t\t\t\t'message' => $e->getMessage(),
		\t\t\t\t\t\t\t'file' => $e->getFile(),
		\t\t\t\t\t\t\t'line' => $e->getLine(),
		\t\t\t\t\t\t]);
		\t\t\t\t\t}
	} catch (\Throwable $e) {
		\Log::error('contracts/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp
              <a href="{{ $aiGenerateContractUrl }}"
                id="{{ $aiGenerateContractLinkId }}"
                data-size="md"
                class="{{ VC::BT_SM_PM }} btn-icon"
                data-ajax-popup-over="true"
                data-url="{{ $aiGenerateContractUrl }}"
                data-bs-placement="top"
                data-title="{{ __('Generate content with AI') }}"
                data-guard-msg="{{ base64_encode($aiGenerateContractGuardMsg) }}"
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
                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                        form.setAttribute('data-failed-route','true');
                    } catch (err) {}
                });
            } catch (err) {}
        })();
    </script>
  {!! Form::close() !!}
@else
    <div class="{{ VC::TXCT }}">
        <h5 class="{{ VC::TX_DNG }}">{{ __('Contract data is not available.') }}</h5>
    </div>
@endif
