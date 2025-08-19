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
    $formId          = 'store_leave';
    $formGuardMsg    = Utility::fetchLinkMessage($lang, VW::LV, 'store_leave_unavailable') ?? 'Store leave route is unavailable. Please contact technical support or your domain administrator.';
    $grammarBase     = 'grammar';
    $grammarKebab    = Str::kebab($grammarBase);
    $grammarResolved = Route::has($grammarBase) ? $grammarBase : (Route::has($grammarKebab) ? $grammarKebab : null);
    $grammarParams   = ['grammar'];
    $grammarUrl      = $grammarResolved ? route($grammarResolved, $grammarParams) : '#';
    $grammarLinkId   = 'grammar-check-link';
    $grammarGuardMsg = Utility::fetchLinkMessage($lang, 'generics', 'grammar_check_route_unavailable') ?? 'Grammar check route is unavailable. Please contact technical support or your domain administrator.';
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
                   class="{{ VC::BT_SM_PM }} btn-icon"
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
            <div class="{{ VC::RW }}">
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                        {{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
                    </div>
                </div>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('leave_type_id', __('Leave Type'), ['class' => VC::FM_LB]) }}
                    <select name="leave_type_id" id="leave_type_id" class="{{ VC::FM_CT_SL }}">
                        <option value="">{{ __('Select Leave Type') }}</option>
                        @foreach($leavetypes as $leave)
                            <option value="{{ $leave->id }}">{{ $leave->title }} (<p class="float-right pr-5">{{ $leave->days }}</p>)</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('start_date', null, ['class' => VC::FM_CT]) }}
                </div>
            </div>
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('end_date', null, ['class' => VC::FM_CT]) }}
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('leave_reason', __('Leave Reason'), ['class' => VC::FM_LB]) }}
                    {{ Form::textarea('leave_reason', null, ['class' => VC::FM_CT, 'placeholder' => __('Leave Reason')]) }}
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            @php
                $grammarTitle = __('Grammar check with AI');
            @endphp
            <div class="col-md-12 text-end">
                <a href="{{ $grammarUrl }}"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon text-right"
                   data-ajax-popup-over="true"
                   id="{{ $grammarLinkId }}"
                   data-url="{{ $grammarUrl }}"
                   data-bs-placement="top"
                   data-title="{{ $grammarTitle }}"
                   data-guard-msg="{{ $grammarGuardMsg }}">
                    <i class="ti ti-rotate"></i> <span>{{ $grammarTitle }}</span>
                </a>
            </div>
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('remark', __('Remark'), ['class' => VC::FM_LB]) }}
                    {{ Form::textarea('remark', null, ['class' => VC::FM_CT.' grammar_textarea', 'placeholder' => __('Leave Remark')]) }}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/leaves/store.js') }}">
    </script>
{!! Form::close() !!}
