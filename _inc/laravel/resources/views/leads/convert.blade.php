@php
    try {
$lang = Utility::fetchUserLang();
        $hasLead = !empty($lead ?? null) && data_get($lead, 'id');

        $convertBase     = VW::LD.'.convert.to.deal';
        $convertKebab    = Str::kebab($convertBase);
        $convertResolved = Route::has($convertBase) ? $convertBase : (Route::has($convertKebab) ? $convertKebab : null);
        $convertUrl      = ($convertResolved && $hasLead) ? route($convertResolved, $lead->id) : '#';
        $convertGuard    = Utility::fetchLinkMessage($lang, VW::LD, 'convert_to_deal_route_unavailable') ?? __('Convert to Deal route is unavailable. Please contact technical support or your domain administrator.');

        $leadName   = data_get($lead ?? [], 'subject', __('No subject available for lead'));
        $leadClient = data_get($lead ?? [], 'name', __('No client name available for lead'));
        $leadEmail  = data_get($lead ?? [], 'email', __('No email available for lead'));
    } catch (\Throwable $e) {
        \Log::error('leads/convert — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if(!$hasLead)
    <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('The requested lead was not found or is unavailable.') }}</div>
@else
    {{ Form::model($lead, [
        'url'               => $convertUrl,
        'method'            => 'POST',
        'id'                => 'lead-convert-form',
        'data-url'          => $convertUrl,
        'data-guard-msg'    => $convertGuard,
        'data-sv-localized' => 'true'
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('name', __('Deal Name'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('name', $leadName, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('price', __('Price'), ['class' => VC::FM_LB]) }}
                    {{ Form::number('price', 0, ['class' => VC::FM_CT, 'min' => 0]) }}
                </div>

                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::DFL }} radio-check">
                        <div class="form-check form-check-inline {{ VC::FM_GCB6 }}">
                            <input type="radio" name="client_check" value="new" id="new_client" class="form-check-input" @if(empty($exist_client)) checked @endif/>
                            <label class="form-check-label {{ VC::FM_LB }}" for="new_client">{{ __('New Client') }}</label>
                        </div>
                        <div class="form-check form-check-inline {{ VC::FM_GCB6 }}">
                            <input type="radio" name="client_check" value="exist" id="existing_client" class="form-check-input" @if(!empty($exist_client)) checked @endif/>
                            <label class="form-check-label {{ VC::FM_LB }}" for="existing_client">{{ __('Existing Client') }}</label>
                        </div>
                    </div>
                </div>

                <div class="{{ VC::FM_GCB6 }} exist_client d-none">
                    {{ Form::label('clients', __('Client'), ['class' => VC::FM_LB]) }}
                    <select name="clients" id="clients" class="{{ VC::FM_CT_SL }}">
                        <option value="">{{ __('Select Client') }}</option>
                        @foreach(($clients ?? []) as $c)
                            <option value="{{ $c->email }}" @if(($leadEmail ?? null) === ($c->email ?? null)) selected @endif>{{ $c->name ?? '' }} ({{ $c->email ?? '' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="{{ VC::FM_GCB6 }} new_client">
                    {{ Form::label('client_name', __('Client Name'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('client_name', $leadClient, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
                <div class="{{ VC::FM_GCB6 }} new_client">
                    {{ Form::label('client_email', __('Client Email'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('client_email', $leadEmail, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
                <div class="{{ VC::FM_GCB6 }} new_client">
                    {{ Form::label('client_password', __('Client Password'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('client_password', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
            </div>

            <div class="{{ VC::RW }} px-3 text-sm">
                <div class="{{ VC::C12 }} pl-0 pb-2 fw-bold text-dark">{{ __('Copy To') }}</div>

                <div class="{{ VC::C3 }} {{ VC::CST_CT_CB }} form-switch">
                    {{ Form::checkbox('is_transfer[]','products',true,['class' => 'form-check-input','id'=>'is_transfer_products']) }}
                    {{ Form::label('is_transfer_products', __('Products'),['class'=>'custom-control-label']) }}
                </div>
                <div class="{{ VC::C3 }} {{ VC::CST_CT_CB }} form-switch">
                    {{ Form::checkbox('is_transfer[]','sources',true,['class' => 'form-check-input','id'=>'is_transfer_sources']) }}
                    {{ Form::label('is_transfer_sources', __('Sources'),['class'=>'custom-control-label']) }}
                </div>
                <div class="{{ VC::C3 }} {{ VC::CST_CT_CB }} form-switch">
                    {{ Form::checkbox('is_transfer[]','files',true,['class' => 'form-check-input','id'=>'is_transfer_files']) }}
                    {{ Form::label('is_transfer_files', __('Files'),['class'=>'custom-control-label']) }}
                </div>
                <div class="{{ VC::C3 }} {{ VC::CST_CT_CB }} form-switch">
                    {{ Form::checkbox('is_transfer[]','discussion',true,['class' => 'form-check-input','id'=>'is_transfer_discussion']) }}
                    {{ Form::label('is_transfer_discussion', __('Discussion'),['class'=>'custom-control-label']) }}
                </div>
                <div class="{{ VC::C3 }} {{ VC::CST_CT_CB }} form-switch">
                    {{ Form::checkbox('is_transfer[]','notes',true,['class' => 'form-check-input','id'=>'is_transfer_notes']) }}
                    {{ Form::label('is_transfer_notes', __('Notes'),['class'=>'custom-control-label']) }}
                </div>
                <div class="{{ VC::C3 }} {{ VC::CST_CT_CB }} form-switch">
                    {{ Form::checkbox('is_transfer[]','calls',true,['class' => 'form-check-input','id'=>'is_transfer_calls']) }}
                    {{ Form::label('is_transfer_calls', __('Calls'),['class'=>'custom-control-label']) }}
                </div>
                <div class="{{ VC::C3 }} {{ VC::CST_CT_CB }} form-switch">
                    {{ Form::checkbox('is_transfer[]','emails',true,['class' => 'form-check-input','id'=>'is_transfer_emails']) }}
                    {{ Form::label('is_transfer_emails', __('Emails'),['class'=>'custom-control-label']) }}
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}" id="lead-convert-submit">
        </div>
        <script async src="{{ asset('assets/js/routes/leads/lang/convert.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/leads/convert.js') }}"></script>
        <script defer>
          (() => {
            const getLocale = () => {
              const raw =
                (window.appLocale || document.documentElement.lang || "en").toLowerCase();
              return raw === "pt-br" ? "pt-br" : raw.split("-")[0];
            };
            const tr = (key) => {
              const loc = getLocale();
              const dict =
                (window.translations && window.translations[loc]) ||
                (window.translations && window.translations.en) ||
                {};
              return dict[key] || key;
            };
            const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));
            const toggleClass = (nodes, c, add) =>
              nodes.forEach((n) => n && (add ? n.classList.add(c) : n.classList.remove(c)));
            const setRequired = (sels, on) =>
              sels.forEach((s) => {
                const el = document.querySelector(s);
                if (el) on ? el.setAttribute("required", "required") : el.removeAttribute("required");
              });
            const applyMode = (mode) => {
              try {
                const existBlocks = qsa(".exist_client");
                const newBlocks = qsa(".new_client");
                const req = ["#client_name", "#client_email", "#client_password"];
                if (mode === "exist") {
                  toggleClass(existBlocks, "d-none", false);
                  toggleClass(newBlocks, "d-none", true);
                  setRequired(req, false);
                } else {
                  toggleClass(existBlocks, "d-none", true);
                  toggleClass(newBlocks, "d-none", false);
                  setRequired(req, true);
                }
              } catch {
                console.warn(tr("request_failed"));
              }
            };
            const init = () => {
              try {
                const radios = qsa('input[name="client_check"]');
                if (radios.length === 0) {
                  console.warn(tr("element_unavailable"));
                  return;
                }
                const checked = radios.find((r) => r.checked);
                applyMode(checked ? checked.value : "new");
                radios.forEach((r) =>
                  r.addEventListener("click", () => applyMode(r.value || "new"))
                );
              } catch {
                console.warn(tr("request_failed"));
              }
            };
            if (document.readyState === "loading") {
              document.addEventListener("DOMContentLoaded", init, { once: true });
            } else {
              init();
            }
          })();
        </script>
    {{ Form::close() }}
@endif
