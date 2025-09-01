@php
    use App\Config\Constants\{
        PlansConstants,
        UsersConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Auth, Facades\Route, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $storeBase  = VW::WRN;
    $storeTry   = [$storeBase, $storeBase . '.store', Str::kebab($storeBase), Str::kebab($storeBase) . '.store'];
    $storeName  = collect($storeTry)->first(fn($n) => Route::has($n));
    $storeUrl   = $storeName ? route($storeName) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, VW::WRN, 'store_warning_route_unavailable') ?? 'Store warning route is unavailable. Please contact technical support or your domain administrator.';
    $formId     = 'create_warning';
@endphp

{!! Form::open([
    'url'                  => $storeUrl,
    'method'               => 'post',
    'id'                   => $formId,
    'data-resolved-action' => $storeUrl,
    'data-guard-msg'       => $storeGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if(($plan?->{PlansConstants::COL_GPT} ?? 0) == 1)
            @php
                $genBase  = 'generate';
                $genName  = Route::has($genBase) ? $genBase : (Route::has(Str::kebab($genBase)) ? Str::kebab($genBase) : null);
                $genUrl   = $genName ? route($genName, ['warning']) : '#';
                $genGuard = Utility::fetchLinkMessage($lang, VW::WRN, 'ai_generate_content_unavailable') ?? 'AI content generation for warnings is unavailable. Please contact technical support or your domain administrator.';
                $genId    = 'warning-ai-generate-link';
            @endphp
            <div class="text-end">
                <a id="{{ $genId }}"
                   href="{{ $genUrl }}"
                   data-url="{{ $genUrl }}"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-title="{{ __('Generate content with AI') }}"
                   data-bs-placement="top"
                   data-guard-msg="{{ $genGuard }}"
                   data-sv-localized="true"
                   class="{{ VC::BT_SM_PM }} btn-icon">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
            <script defer src="{{ asset('assets/js/routes/warnings/generate.js') }}"></script>
        @endif

        <div class="row">
            @if(!empty($user?->{UsersConstants::COL_TP}) && strtolower($user->{UsersConstants::COL_TP}) !== 'employee')
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('warning_by', __('Warning By'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('warning_by', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
                </div>
            @endif

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('warning_to', __('Warning To'), ['class' => VC::FM_LB]) }}
                {{ Form::select('warning_to', $employees, null, ['class' => VC::FM_CT_SL]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('subject', __('Subject'), ['class' => VC::FM_LB]) }}
                {{ Form::text('subject', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('warning_date', __('Warning Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('warning_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>

    <script defer src="{{ asset('assets/js/routes/warnings/store.js') }}"></script>
{!! Form::close() !!}
