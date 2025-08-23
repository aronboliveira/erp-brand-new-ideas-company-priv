@php
    use App\Config\Constants\{PermissionsConstants, PlansConstants, UsersConstants, ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Auth, Facades\Route, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);

    $storeBase       = VW::LV . '.store';
    $storeKebab      = Str::kebab($storeBase);
    $storeResolved   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl        = $storeResolved ? route($storeResolved) : '#';
    $formId          = 'create_leave';
    $formGuardMsg    = Utility::fetchLinkMessage($lang, VW::LV, 'create_leave_unavailable') ?? 'Create leave route is unavailable. Please contact technical support or your domain administrator.';

    $grammarBase     = 'grammar';
    $grammarKebab    = Str::kebab($grammarBase);
    $grammarResolved = Route::has($grammarBase) ? $grammarBase : (Route::has($grammarKebab) ? $grammarKebab : null);
    $grammarParams   = ['grammar'];
    $grammarUrl      = $grammarResolved ? route($grammarResolved, $grammarParams) : '#';
    $grammarLinkId   = 'grammar-check-link';
    $grammarGuardMsg = Utility::fetchLinkMessage($lang, VW::LV, 'grammar_leave_unavailable') ?? 'Grammar check route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{!! Form::open(['url' => $storeUrl, 'method' => 'post', 'id' => $formId, 'data-guard-msg' => $formGuardMsg]) !!}
    <div class="modal-body">
        @php
            $plan = Utility::getChatGPTSettings();
        @endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            @php
                $aiBase       = 'generate';
                $aiKebab      = Str::kebab($aiBase);
                $aiResolved   = Route::has($aiBase) ? $aiBase : (Route::has($aiKebab) ? $aiKebab : null);
                $aiParams     = ['leave'];
                $aiUrl        = $aiResolved ? route($aiResolved, $aiParams) : '#';
                $aiLinkId     = 'leave-ai-generate-link';
                $aiGuardMsg   = Utility::fetchLinkMessage($lang, VW::LV, 'generate_leave_unavailable') ?? 'Generate leave content route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <div class="text-end">
                <a href="{{ $aiUrl }}"
                   id="{{ $aiLinkId }}"
                   class="btn btn-primary btn-icon btn-sm"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-url="{{ $aiUrl }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}"
                   data-guard-msg="{{ $aiGuardMsg }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        @if($user?->{UsersConstants::COL_TP} === UsersConstants::CPN || strtolower($user?->{UsersConstants::COL_TP}) == PermissionsConstants::HR)
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                        {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('leave_type_id', __('Leave Type'), ['class' => 'form-label']) }}
                    <select name="leave_type_id" id="leave_type_id" class="form-control select">
                        <option value="">{{ __('Select Leave Type') }}</option>
                        @foreach($leavetypes as $leave)
                            <option value="{{ $leave->id }}">{{ $leave->title }} (<p class="float-right pr-5">{{ $leave->days }}</p>)</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                    {{ Form::date('start_date', null, ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                    {{ Form::date('end_date', null, ['class' => 'form-control']) }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('leave_reason', __('Leave Reason'), ['class' => 'form-label']) }}
                    {{ Form::textarea('leave_reason', null, ['class' => 'form-control', 'placeholder' => __('Leave Reason')]) }}
                </div>
            </div>
        </div>

        <div class="row">
            @php
                $grammarTitle = __('Grammar check with AI');
            @endphp
            <div class="col-md-12 text-end">
                <a href="{{ $grammarUrl }}"
                   data-size="md"
                   class="btn btn-primary btn-icon btn-sm text-right"
                   data-ajax-popup-over="true"
                   id="{{ $grammarLinkId }}"
                   data-url="{{ $grammarUrl }}"
                   data-bs-placement="top"
                   data-title="{{ $grammarTitle }}"
                   data-guard-msg="{{ $grammarGuardMsg }}">
                    <i class="ti ti-rotate"></i> <span>{{ $grammarTitle }}</span>
                </a>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('remark', __('Remark'), ['class' => 'form-label']) }}
                    {{ Form::textarea('remark', null, ['class' => 'form-control grammar_textarea', 'placeholder' => __('Leave Remark')]) }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{!! Form::close() !!}

<script defer>
    (() => {
        try {
            const guardToast = (msg) => {
                try {
                    const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast);
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

            const f = document.getElementById('{{ $formId }}');
            if (f && !(f.hasAttribute('data-submit-listener') && f.getAttribute('data-submit-listener') === 'true')) {
                f.setAttribute('data-submit-listener', 'true');
                f.addEventListener('submit', function (e) {
                    try {
                        const action = f.getAttribute('action') || '#';
                        if (action !== '#') return;
                        e.preventDefault();
                        const msg = f.getAttribute('data-guard-msg') || 'Create leave route is unavailable. Please contact technical support or your domain administrator.';
                        guardToast(msg);
                        f.setAttribute('data-failed-route', 'true');
                    } catch (err) {}
                }, { passive: false });
            }

            const g = document.getElementById('{{ $grammarLinkId }}');
            if (g && !(g.hasAttribute('data-grammar-listener') && g.getAttribute('data-grammar-listener') === 'true')) {
                g.setAttribute('data-grammar-listener', 'true');
                g.addEventListener('click', function (e) {
                    try {
                        const href = g.getAttribute('href') || '#';
                        const url  = g.getAttribute('data-url') || href || '#';
                        if (href !== '#' || url !== '#') return;
                        e.preventDefault();
                        const msg = g.getAttribute('data-guard-msg') || 'Grammar check route is unavailable. Please contact technical support or your domain administrator.';
                        guardToast(msg);
                        g.setAttribute('data-failed-route', 'true');
                    } catch (err) {}
                }, { passive: false });
            }

            const ai = document.getElementById('leave-ai-generate-link');
            if (ai && !(ai.hasAttribute('data-ai-listener') && ai.getAttribute('data-ai-listener') === 'true')) {
                ai.setAttribute('data-ai-listener', 'true');
                ai.addEventListener('click', function (e) {
                    try {
                        const href = ai.getAttribute('href') || '#';
                        const url  = ai.getAttribute('data-url') || href || '#';
                        if (href !== '#' || url !== '#') return;
                        e.preventDefault();
                        const msg = ai.getAttribute('data-guard-msg') || 'Generate leave content route is unavailable. Please contact technical support or your domain administrator.';
                        guardToast(msg);
                        ai.setAttribute('data-failed-route', 'true');
                    } catch (err) {}
                }, { passive: false });
            }
        } catch (error) {}
    })();
</script>
