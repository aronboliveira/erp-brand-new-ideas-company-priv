@php
    use App\Config\Constants\{PlansConstants, ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route};
    use Illuminate\Support\Str;
    
    $lang = method_exists('Utility', 'fetchUserLang') ? Utility::fetchUserLang() : 'en';
    $storeBaseName = VW::PRM;
    $storeKebabName = Str::kebab($storeBaseName);
    $storeResolvedName = Route::has($storeBaseName) ? $storeBaseName : (Route::has($storeKebabName) ? $storeKebabName : null);
    $storeUrl = $storeResolvedName ? route($storeResolvedName) : '#';
    $formId = 'create_promotion';
    $formGuardMsg = 'Create promotion route is unavailable. Please contact technical support or your domain administrator.';
    if (method_exists('Utility', 'fetchLinkMessage')) {
        $fetchedMsg = Utility::fetchLinkMessage($lang, VW::PRM, 'create_promotion_unavailable');
        $formGuardMsg = $fetchedMsg ?? $formGuardMsg;
    }
@endphp

{!! Form::open([
    'url'  => $storeUrl,
    'method' => 'post',
    'id'   => $formId,
    'data-guard-msg' => $formGuardMsg,
]) !!}
    <div class="modal-body">
        @php
            $plan = null;
            $hasGPT = false;
            if (method_exists('Utility', 'getChatGPTSettings')) {
                $plan = Utility::getChatGPTSettings();
                if (isset($plan) && is_object($plan)) {
                    $hasGPT = data_get($plan, PlansConstants::COL_GPT) == 1;
                }
            }
        @endphp
        
        @if ($hasGPT)
            @php
                $aiGenBase = 'generate';
                $aiGenKebab = Str::kebab($aiGenBase);
                $aiGenResolved = Route::has($aiGenBase) ? $aiGenBase : (Route::has($aiGenKebab) ? $aiGenKebab : null);
                $aiGenParams = ['promotion'];
                $aiGenUrl = $aiGenResolved ? route($aiGenResolved, $aiGenParams) : '#';
                $aiLinkId = 'promotion-ai-generate-link';
                
                $aiGuardMsg = 'Generate promotion content route is unavailable. Please contact technical support or your domain administrator.';
                if (method_exists('Utility', 'fetchLinkMessage')) {
                    $fetchedAiMsg = Utility::fetchLinkMessage($lang, VW::PRM, 'generate_promotion_unavailable');
                    $aiGuardMsg = $fetchedAiMsg ?? $aiGuardMsg;
                }
            @endphp
            <div class="text-end">
                <a href="{{ $aiGenUrl }}"
                   id="{{ $aiLinkId }}"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-url="{{ $aiGenUrl }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}"
                   data-guard-msg="{{ $aiGuardMsg }}">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="col-lg-6 {{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                @if(isset($employees) && Utility::isFilled($employees) ?? [])
                    {{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
                @else
                    <select name="employee_id" class="{{ VC::FM_CT_SL }}" required disabled>
                        <option value="">{{ __('No employees available') }}</option>
                    </select>
                @endif
            </div>

            <div class="col-lg-6 {{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('designation_id', __('Designation'), ['class' => VC::FM_LB]) }}
                @if(isset($designations) && Utility::isFilled($designations) ?? [])
                    {{ Form::select('designation_id', $designations, null, ['class' => VC::FM_CT_SL]) }}
                @else
                    <select name="designation_id" class="{{ VC::FM_CT_SL }}" disabled>
                        <option value="">{{ __('No designations available') }}</option>
                    </select>
                @endif
            </div>

            <div class="col-lg-6 {{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('promotion_title', __('Promotion Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('promotion_title', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="col-lg-6 {{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('promotion_date', __('Promotion Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('promotion_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
{!! Form::close() !!}

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            try {
                const guardToast = (msg) => {
                    try {
                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            container.className = 'position-fixed top-0 end-0 p-3';
                            document.body.appendChild(container);
                        }
                        if (hasBootstrap) {
                            const toast = document.createElement('div');
                            toast.className = 'toast';
                            toast.setAttribute('role', 'alert');
                            toast.setAttribute('aria-live', 'assertive');
                            toast.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg ?? 'Requested route is unavailable. Please contact technical support or your domain administrator.';
                            toast.appendChild(body);
                            container.appendChild(toast);
                            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                            toast.addEventListener('hidden.bs.toast', function () { try { toast.remove(); } catch (err) {} });
                            inst.show();
                        } else {
                            alert(msg ?? 'Requested route is unavailable. Please contact technical support or your domain administrator.');
                        }
                    } catch (err) {}
                };

                const formEl = document.getElementById('{{ $formId }}');
                if (formEl && !(formEl.hasAttribute('data-submit-listener') && formEl.getAttribute('data-submit-listener') === 'true')) {
                    formEl.setAttribute('data-submit-listener', 'true');
                    formEl.addEventListener('submit', function (e) {
                        try {
                            const action = formEl.getAttribute('action') || '#';
                            if (action !== '#') return;
                            e.preventDefault();
                            const msg = formEl.getAttribute('data-guard-msg') || 'Create promotion route is unavailable. Please contact technical support or your domain administrator.';
                            guardToast(msg);
                            formEl.setAttribute('data-failed-route', 'true');
                        } catch (err) {}
                    }, { passive: false });
                }

                const ai = document.getElementById('{{ isset($aiLinkId) ? $aiLinkId : 'x' }}');
                if (ai && !(ai.hasAttribute('data-ai-listener') && ai.getAttribute('data-ai-listener') === 'true')) {
                    ai.setAttribute('data-ai-listener', 'true');
                    ai.addEventListener('click', function (e) {
                        try {
                            const href = ai.getAttribute('href') || '#';
                            const url = ai.getAttribute('data-url') || href || '#';
                            if (href !== '#' || url !== '#') return;
                            e.preventDefault();
                            const msg = ai.getAttribute('data-guard-msg') || 'Generate promotion content route is unavailable. Please contact technical support or your domain administrator.';
                            guardToast(msg);
                            ai.setAttribute('data-failed-route', 'true');
                        } catch (err) {}
                    }, { passive: false });
                }
            } catch (error) {}
        })();
    </script>
@endpush
