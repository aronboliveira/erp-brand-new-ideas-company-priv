{{ Collective\Html\FormFacade::model($lead, array('route' => array('leads.convert.to.deal', $lead->id), 'method' => 'POST')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('name', __('Deal Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', $lead->subject, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('price', __('Price'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::number('price', 0, array('class' => 'form-control','min'=>0)) }}
        </div>
        <div class="col-sm-12 col-md-12">
            <div class="d-flex radio-check">
                <div class="orm-check form-check-inline form-group col-md-6">
                    <input type="radio" name="client_check" value="new" id="new_client" class="form-check-input" @if(empty($exist_client)) checked @endif/>
                    <label class="form-check-label form-label" for="new_client">{{__('New Client')}}</label>
                </div>
                <div class="orm-check form-check-inline form-group col-md-6">
                    <input type="radio" name="client_check" value="exist" id="existing_client" class="form-check-input" @if(!empty($exist_client)) checked @endif/>
                    <label class="form-check-label form-label" for="existing_client">{{__('Existing Client')}}</label>
                </div>
            </div>
        </div>
        <div class="col-6 exist_client d-none form-group">
            {{ Collective\Html\FormFacade::label('clients', __('Client'),['class'=>'form-label']) }}
            <select name="clients" id="clients" class="form-control select">
                <option value="">{{ __('Select Client') }}</option>
                @foreach($clients as $client)
                    <option value="{{ $client->email }}" @if($lead->email == $client->email) selected @endif>{{ $client->name }} ({{ $client->email }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 new_client form-group">
            {{ Collective\Html\FormFacade::label('client_name', __('Client Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('client_name', $lead->name, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 new_client form-group">
            {{ Collective\Html\FormFacade::label('client_email', __('Client Email'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('client_email', $lead->email, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 new_client form-group">
            {{ Collective\Html\FormFacade::label('client_password', __('Client Password'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('client_password',null, array('class' => 'form-control','required'=>'required')) }}
        </div>
    </div>
    <div class="row px-3 text-sm">
        <div class="col-12 pl-0 pb-2 font-bold text-dark">{{__('Copy To')}}</div>
        <div class="col-3 custom-control custom-checkbox form-switch">
            {{ Collective\Html\FormFacade::checkbox('is_transfer[]','products',false,['class' => 'form-check-input','id'=>'is_transfer_products','checked'=>'checked']) }}
            {{ Collective\Html\FormFacade::label('is_transfer_products', __('Products'),['class'=>'custom-control-label']) }}
        </div>
        <div class="col-3 custom-control custom-checkbox form-switch">
            {{ Collective\Html\FormFacade::checkbox('is_transfer[]','sources',false,['class' => 'form-check-input','id'=>'is_transfer_sources','checked'=>'checked']) }}
            {{ Collective\Html\FormFacade::label('is_transfer_sources', __('Sources'),['class'=>'custom-control-label']) }}
        </div>
        <div class="col-3 custom-control custom-checkbox form-switch">
            {{ Collective\Html\FormFacade::checkbox('is_transfer[]','files',false,['class' => 'form-check-input','id'=>'is_transfer_files','checked'=>'checked']) }}
            {{ Collective\Html\FormFacade::label('is_transfer_files', __('Files'),['class'=>'custom-control-label']) }}
        </div>
        <div class="col-3 custom-control custom-checkbox form-switch">
            {{ Collective\Html\FormFacade::checkbox('is_transfer[]','discussion',false,['class' => 'form-check-input','id'=>'is_transfer_discussion','checked'=>'checked']) }}
            {{ Collective\Html\FormFacade::label('is_transfer_discussion', __('Discussion'),['class'=>'custom-control-label']) }}
        </div>
        <div class="col-3 custom-control custom-checkbox form-switch">
            {{ Collective\Html\FormFacade::checkbox('is_transfer[]','notes',false,['class' => 'form-check-input','id'=>'is_transfer_notes','checked'=>'checked']) }}
            {{ Collective\Html\FormFacade::label('is_transfer_notes', __('Notes'),['class'=>'custom-control-label']) }}
        </div>
        <div class="col-3 custom-control custom-checkbox form-switch">
            {{ Collective\Html\FormFacade::checkbox('is_transfer[]','calls',false,['class' => 'form-check-input','id'=>'is_transfer_calls','checked'=>'checked']) }}
            {{ Collective\Html\FormFacade::label('is_transfer_calls', __('Calls'),['class'=>'custom-control-label']) }}
        </div>
        <div class="col-3 custom-control custom-checkbox form-switch">
            {{ Collective\Html\FormFacade::checkbox('is_transfer[]','emails',false,['class' => 'form-check-input','id'=>'is_transfer_emails','checked'=>'checked']) }}
            {{ Collective\Html\FormFacade::label('is_transfer_emails', __('Emails'),['class'=>'custom-control-label']) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>

{{Collective\Html\FormFacade::close()}}
<script async src="{{ asset('assets/js/routes/leads/lang/convert.js') }}"></script>
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
