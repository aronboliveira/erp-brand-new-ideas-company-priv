@php
    use App\Config\Constants\{PlansConstants, ViewsConstants, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\Utility;
    use Illuminate\Support\{Facades\Route, Str};
    use Collective\Html\FormFacade as Form;

    $lang = Utility::fetchUserLang();

    $storeFormId = 'store_termination';
    $generateLinkId = 'termination-generate-link';

    $tmnStoreName = ViewsConstants::TMN . '.store';
    $tmnKebabStoreName = Str::kebab($tmnStoreName);
    $tmnBaseName = ViewsConstants::TMN;
    $tmnKebabBaseName = Str::kebab($tmnBaseName);
    $terminationStoreResolved = Route::has($tmnStoreName)
        ? $tmnStoreName
        : (Route::has($tmnKebabStoreName)
            ? $tmnKebabStoreName
            : (Route::has($tmnBaseName)
                ? $tmnBaseName
                : (Route::has($tmnKebabBaseName) ? $tmnKebabBaseName : null)));
    $terminationStoreUrl = $terminationStoreResolved ? route($terminationStoreResolved) : '#';
    $terminationStoreGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::TMN, 'route_store_termination_unavailable') ?? 'Store termination route is unavailable. Please contact technical support or your domain administrator.';

    $generateBase = 'generate';
    $generateResolved = Route::has($generateBase)
        ? $generateBase
        : (Route::has(Str::kebab($generateBase)) ? Str::kebab($generateBase) : null);
    $generateUrl = $generateResolved ? route($generateResolved, ['termination']) : '#';
    $generateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::TMN, 'route_generate_termination_unavailable') ?? 'Generate termination route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{!! Form::open([
    'url'    => $terminationStoreUrl,
    'method' => 'post',
    'id'     => $storeFormId,
    'data-url' => $terminationStoreUrl,
    'data-guard-msg' => $terminationStoreGuardMsg,
    'data-sv-localized' => 'true',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                <a  href="{{ $generateUrl }}"
                    id="{{ $generateLinkId }}"
                    data-size="md"
                    class="{{ VC::BT_SM_PM }} btn-icon"
                    data-ajax-popup-over="true"
                    data-url="{{ $generateUrl }}"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}"
                    data-guard-msg="{{ $generateGuardMsg }}"
                    data-sv-localized="true">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="row">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                {{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('termination_type', __('Termination Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('termination_type', $terminationtypes, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('notice_date', __('Notice Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('notice_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('termination_date', __('Termination Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('termination_date', null, ['class' => VC::FM_CT]) }}
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
    <script src="{{ asset('assets/js/routes/terminations/store.js') }}" defer></script>
    <script src="{{ asset('assets/js/routes/ai/generate/termination.js') }}" defer></script>
{!! Form::close() !!}

