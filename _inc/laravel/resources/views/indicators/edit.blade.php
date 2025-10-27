@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants as ST};
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};
    use Collective\Html\FormFacade as Form;

    $lang         = Utility::fetchUserLang();
    $hasIndicator = !empty($indicator ?? null) && data_get($indicator, 'id');

    $updateUrl   = '#';
    $updateGuard = Utility::fetchLinkMessage($lang, VW::IND, 'update_route_unavailable')
        ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');

    $designationUrl   = Route::has(VW::EMP.'.json') ? route(VW::EMP.'.json') : '#';
    $desgUnavailable  = Utility::fetchLinkMessage($lang, VW::EMP, 'designation_fetch_unavailable')
        ?? __('Failed to fetch designations.');
    $desgFailed       = Utility::fetchLinkMessage($lang, VW::EMP, 'designation_fetch_failed')
        ?? __('Failed to fetch designation data.');
    $designationLabel = Utility::fetchLinkMessage($lang, VW::EMP, 'designation_default')
        ?? __('Select any Designation');

    if (Route::has(VW::IND.'.update') && $hasIndicator) {
        $updateUrl = route(VW::IND.'.update', $indicator->id);
    }
@endphp

@if(!$hasIndicator)
    <div class="alert alert-warning mb-0" role="alert">{{ __('The requested indicator was not found or is unavailable.') }}</div>
@else
    {{ Form::model($indicator, [
        'url'                        => $updateUrl,
        'method'                     => 'PUT',
        'id'                         => 'indicator-edit-form',
        'data-url'                   => $updateUrl,
        'data-guard-msg'             => $updateGuard,
        'data-guard-msg-update_unavailable' => $updateGuard,
        'data-sv-localized'          => 'true'
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    <div class="form-group">
                        {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branches ?? [], null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                    </div>
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    <div class="form-group">
                        {{ Form::label('department', __('Department'), ['class' => 'form-label']) }}
                        {{ Form::select('department', $departments ?? [], null, [
                            'class'                                   => VC::FM_CT_SL,
                            'required'                                => 'required',
                            'id'                                      => 'department_id',
                            'data-url'                                => $designationUrl,
                            'data-default-opt'                        => $designationLabel,
                            'data-guard-msg'                          => $desgUnavailable,
                            'data-guard-msg-designation_fetch_unavailable' => $desgUnavailable,
                            'data-guard-msg-designation_fetch_failed' => $desgFailed,
                            'data-sv-localized'                       => 'true'
                        ]) }}
                    </div>
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    <div class="form-group">
                        {{ Form::label('designation', __('Designation'), ['class' => 'form-label']) }}
                        <select
                            class="select {{ VC::FM_CT }} select2-multiple"
                            id="designation_id"
                            name="designation"
                            data-toggle="select2"
                            data-placeholder="{{ $designationLabel }}"
                            data-current="{{ $indicator->designation }}"
                            required
                        ></select>
                    </div>
                </div>
            </div>

            @php
                $perfIsList = (is_array($performances ?? null) && count($performances ?? []) > 0) || (($performances ?? null) instanceof Collection && $performances->isNotEmpty());
                $ratings = $ratings ?? [];
            @endphp

            @if($perfIsList)
                @foreach($performances as $perf)
                    @php
                        $types = data_get($perf, 'types');
                        $typesIsList = Utility::isFilled($types ?? []);
                    @endphp
                    <div class="row">
                        <div class="{{ VC::FM_GCB12 }} mt-3">
                            <h6>{{ data_get($perf, 'name', __('Unnamed section')) }}</h6>
                            <hr class="mt-0">
                        </div>
                        @if($typesIsList)
                            @foreach($types as $t)
                                @php
                                    $typeId   = data_get($t, 'id');
                                    $typeName = data_get($t, 'name', __('Unnamed metric'));
                                    $current  = (int) data_get($ratings, $typeId, 0);
                                    $titles   = [
                                        5 => __('Excellent — 5 stars'),
                                        4 => __('Very good — 4 stars'),
                                        3 => __('Good — 3 stars'),
                                        2 => __('Needs improvement — 2 stars'),
                                        1 => __('Poor — 1 star')
                                    ];
                                @endphp
                                <div class="{{ VC::CM6 }}">{{ $typeName }}</div>
                                <div class="{{ VC::CM6 }}">
                                    <fieldset class="rating">
                                        @for($r = 5; $r >= 1; $r--)
                                            @php $idAttr = "rating-{$r}-{$typeId}"; @endphp
                                            <input class="stars" type="radio" id="{{ $idAttr }}" name="rating[{{ $typeId }}]" value="{{ $r }}" {{ $current === $r ? 'checked' : '' }}>
                                            <label class="full" for="{{ $idAttr }}" title="{{ $titles[$r] }}"></label>
                                        @endfor
                                    </fieldset>
                                </div>
                            @endforeach
                        @else
                            <div class="{{ VC::FM_GCB12 }}">
                                <div class="text-muted">{{ __('No performance metrics available for this section.') }}</div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="row">
                    <div class="{{ VC::FM_GCB12 }}">
                        <div class="text-muted">{{ __('No performance sections available.') }}</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script async src="{{ asset('assets/js/routes/indicators/lang/edit.js') }}"></script>
        <script defer>
            (() => {
              const ERR_FB = '# ERROR';
              const CLIENT_FLAG = 'data-client-localized';
              const GUARD_MSG = 'data-guard-msg';
              const LANG_KEY = 'erp-np-lang';
            
              const getMsg = (key, el) => {
                let msg = ERR_FB;
                if (el?.getAttribute(CLIENT_FLAG) === 'true') {
                  msg = el.getAttribute(GUARD_MSG) || msg;
                } else {
                  let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                    .toLowerCase().replace(/_/g, '-');
                  lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                  msg = window.translations?.[lang]?.[key]
                    || el?.getAttribute(GUARD_MSG)
                    || window.translations?.['en']?.[key]
                    || msg;
                  if (msg !== ERR_FB && el) {
                    el.setAttribute(GUARD_MSG, msg);
                    el.setAttribute(CLIENT_FLAG, 'true');
                  }
                }
                return msg;
              };
            
              const showError = message => {
                try {
                  const bsAvailable = !!document.querySelector('link[href*="bootstrap"]') && !!window.bootstrap?.Toast;
                  if (bsAvailable) {
                    const container = document.getElementById('toast-container') 
                      || (() => {
                        const c = document.createElement('div');
                        c.id = 'toast-container';
                        document.body.appendChild(c);
                        return c;
                      })();
                    const toastEl = document.createElement('div');
                    toastEl.className = 'toast';
                    toastEl.setAttribute('role','alert');
                    toastEl.setAttribute('aria-live','assertive');
                    toastEl.setAttribute('aria-atomic','true');
                    const body = document.createElement('div');
                    body.className = 'toast-body';
                    body.textContent = message;
                    toastEl.appendChild(body);
                    container.appendChild(toastEl);
                    window.bootstrap.Toast.getOrCreateInstance(toastEl).show();
                  } else {
                    alert(message);
                  }
                } catch {
                  alert(message);
                }
              };
            
              const fetchDesignation = did => {
                try {
                  if (!did) throw new Error('designation_fetch_unavailable');
                  const url = '{{ route(VW::EMP . ".json") }}';
                  if (!url || url === '#') throw new Error('designation_fetch_unavailable');
                  $.ajax({
                    url,
                    type: 'POST',
                    data: { department_id: did, _token: '{{ csrf_token() }}' },
                  })
                  .done(data => {
                    const sel = document.getElementById('designation_id');
                    if (!sel) return;
                    sel.innerHTML = '<option value="">' 
                      + (window.translations?.[navigator.language.slice(0,2)]?.designation_default 
                        || 'Select any Designation') 
                      + '</option>';
                    data.forEach((v, k) => {
                      const opt = document.createElement('option');
                      opt.value = k;
                      if (k == '{{ $indicator->designation }}') opt.selected = true;
                      opt.textContent = v;
                      sel.appendChild(opt);
                    });
                  })
                  .fail(() => { throw new Error('designation_fetch_failed'); });
                } catch (e) {
                  const el = document.getElementById('department_id');
                  showError(getMsg(e.message, el));
                }
              };
            
              const init = () => {
                const dep = $('#department_id');
                if (!dep.length) return;
                const did = dep.val();
                fetchDesignation(did);
                dep.off('change.designationListener')
                  .on('change.designationListener', () => {
                    fetchDesignation(dep.val());
                  });
                const fm = document.getElementById("indicator-edit-form");
                if (fm && fm.getAttribute("data-submit-guarded") !== "true") {
                  fm.setAttribute("data-submit-guarded", "true");
                  fm.addEventListener("submit", (e) => {
                    try {
                      const action = (fm.getAttribute("action") ?? "#").trim();
                      const url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
                      if (url === "#" || action === "#") {
                        e.preventDefault();
                        showError(fm.getAttribute("data-guard-msg") || window.translations?.[navigator.language.slice(0,2)]?.update_unavailable || 'Update route is unavailable. Please contact technical support or your domain administrator.');
                        fm.setAttribute("data-failed-route", "true");
                      }
                    } catch {}
                  });
                }
              };
            
              $(document).ready(init);
            
              new MutationObserver((muts, obs) => {
                muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
                  if (n.id === 'department_id') {
                    obs.disconnect();
                  }
                }));
              }).observe(document.body, { childList: true, subtree: true });
            })();
        </script>
    {{ Form::close() }}        
@endif    
    

