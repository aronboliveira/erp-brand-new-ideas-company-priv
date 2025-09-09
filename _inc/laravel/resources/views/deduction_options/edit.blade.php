@php
    $deductionOptionUpdateBaseRouteName  = ViewsConstants::DDT_OPT.'.update';
    $deductionOptionUpdateKebabRouteName = Str::kebab($deductionOptionUpdateBaseRouteName);
    $deductionOptionUpdateResolvedName   = Route::has($deductionOptionUpdateBaseRouteName)
        ? $deductionOptionUpdateBaseRouteName
        : (Route::has($deductionOptionUpdateKebabRouteName) ? $deductionOptionUpdateKebabRouteName : null);
    $deductionOptionIdValue              = (string) data_get($deductionoption, 'id', '');
    $deductionOptionUpdateUrl            = ($deductionOptionUpdateResolvedName && $deductionOptionIdValue !== '')
        ? route($deductionOptionUpdateResolvedName, $deductionOptionIdValue)
        : '#';
    $deductionOptionUpdateFormId         = 'deduction-option-update-form-'.($deductionOptionIdValue === '' ? 'x' : $deductionOptionIdValue);
    $langValue                           = isset($lang) ? $lang : Utility::fetchUserLang();
    $deductionOptionUpdateGuardMessage   = Utility::fetchLinkMessage($langValue, ViewsConstants::DDT_OPT, 'update_deduction_option_route_unavailable')
        ?? 'Update deduction option route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@if(!empty($deductionoption) && isset($deductionoption->id))
    {{ Form::model($deductionoption, [
        'method'            => 'PUT',
        'url'               => $deductionOptionUpdateUrl,
        'id'                => $deductionOptionUpdateFormId,
        'data-url'          => $deductionOptionUpdateUrl,
        'data-guard-msg'    => $deductionOptionUpdateGuardMessage,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                        @php
                            $hasError = $errors->has('name');
                            $attrs = [
                                'id'               => 'name',
                                'class'            => trim(VC::FM_CT . ' ' . ($hasError ? 'is-invalid' : '')),
                                'placeholder'      => __('Enter Deduction Option Name'),
                                'required'         => 'required',
                                'aria-invalid'     => $hasError ? 'true' : 'false',
                                'aria-describedby' => $hasError ? 'name-error' : null,
                                'autocomplete'     => 'off',
                            ];
                        @endphp
                        {{ Form::text('name', null, $attrs) }}
                        @if($hasError)
                            <span id="name-error" class="invalid-feedback d-block" role="alert">
                                <strong class="text-danger">{{ $errors->first('name') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
        </div>
        <script defer>
            (() => {
                try {
                    const fm = document.getElementById('{{ $deductionOptionUpdateFormId }}');
                    if (!fm) { return; }
                    if (fm.getAttribute('data-submit-guarded') === 'true') { return; }
                    fm.setAttribute('data-submit-guarded','true');
                    fm.addEventListener('submit',(e) => {
                        try {
                            const action = fm.getAttribute('action') ?? '#';
                            const url    = fm.getAttribute('data-url') ?? action ?? '#';
                            if (url !== '#' && action !== '#') { return; }
                            e.preventDefault();
                            const msg = fm.getAttribute('data-guard-msg') ?? 'Update deduction option route is unavailable. Please contact technical support or your domain administrator.';
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
                            fm.setAttribute('data-failed-route','true');
                        } catch (err) {}
                    });
                } catch (err) {}
            })();
        </script>
    {{ Form::close() }}
@else
    <div class="modal-body">
        <div class="{{ VC::ALERT }} {{ VC::ALERT_D }} mb-0" role="alert">
            {{ __('Deduction Option data is not available.') }}
        </div>
    </div>
@endif