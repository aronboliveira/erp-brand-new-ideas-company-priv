@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{
        ViewsConstants,
        PlansConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;

    $lang        = Utility::fetchUserLang();
    $routeName   = ViewsConstants::CPN_PL . '.update';
    $updateRoute = Route::has($routeName)
        ? route($routeName, $companyPolicy->id)
        : (Route::has(Str::kebab($routeName))
            ? route(Str::kebab($routeName), $companyPolicy->id)
            : '#');
    $formId      = 'companyPolicyUpdateForm_' . $companyPolicy->id;
    $guardMsg    = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPN_PL,
        'company_policy_update_route_unavailable'
    ) ?? 'Company Policy update route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@if(!empty($companyPolicy) && isset($companyPolicy->id))
    {{ Form::model($companyPolicy, [
        'route'          => [$updateRoute],
        'method'         => 'PUT',
        'enctype'        => 'multipart/form-data',
        'id'             => $formId,
        'data-url'       => $updateRoute,
        'data-guard-msg' => $guardMsg,
    ]) }}
        <div class="modal-body">
            @php $plan = Utility::getChatGPTSettings(); @endphp
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                @php
                    $aiGenerateRouteBase           = 'generate';
                    $aiGenerateRouteKebab          = Str::kebab($aiGenerateRouteBase);
                    $aiGenerateResolvedName        = Route::has($aiGenerateRouteBase) ? $aiGenerateRouteBase : (Route::has($aiGenerateRouteKebab) ? $aiGenerateRouteKebab : null);
                    $aiGenerateTopic               = 'company policy';
                    $aiGenerateUrl                 = $aiGenerateResolvedName ? route($aiGenerateResolvedName, [$aiGenerateTopic]) : '#';
                    $aiGenerateLang                = isset($lang) ? $lang : Utility::fetchUserLang();
                    $aiGenerateGuardMsg            = Utility::fetchLinkMessage($aiGenerateLang, ViewsConstants::CPN_PL, 'generate_ai_company_policy_route_unavailable') ?? 'Generate AI company policy route is unavailable. Please contact technical support or your domain administrator.';
                    $aiGenerateCompanyPolicyLinkId = 'ai-generate-company-policy-link';
                @endphp
                <div class="{{ VC::FEND }}">
                    <a
                        id="{{ $aiGenerateCompanyPolicyLinkId }}"
                        href="{{ $aiGenerateUrl }}"
                        data-size="md"
                        class="{{ VC::BT_PRM }} {{ VC::BT_LG }} btn-icon btn-sm"
                        data-ajax-popup-over="true"
                        data-url="{{ $aiGenerateUrl }}"
                        data-bs-placement="top"
                        data-title="{{ __('Generate content with AI') }}"
                        data-guard-msg="{{ $aiGenerateGuardMsg }}"
                        data-sv-localized="true"
                    >
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
                <script defer src="{{ asset('assets/js/routes/companyPolicies/generate.js') }}"></script>
            @endif
            <div class="{{ VC::RW }}">
                <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                    {{ Form::label('branch', __('Branch'), ['class' => VC::FM_LB]) }}
                    @if((is_array($branch) && count($branch)) || ($branch instanceof \Illuminate\Support\Collection && !$branch->isEmpty()))
                        {{ Form::select('branch', $branch, null, ['class' => VC::FM_CT . ' select', 'required' => 'required']) }}
                    @else
                        {{ Form::select('branch', ['' => __('No branches available')], null, ['class' => VC::FM_CT . ' select', 'disabled' => 'disabled']) }}
                    @endif
                </div>
                <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                    {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => VC::FM_CT,'required'=>'required']) }}
                </div>
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                    {{ Form::textarea('description', null, ['class' => VC::FM_CT]) }}
                </div>
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('attachment', __('Attachment'), ['class' => VC::FM_LB]) }}
                    <div class="choose-file {{ VC::FM_G }}">
                        <label for="attachment" class="{{ VC::FM_LB }}">
                            @php $policyPath = Utility::getFile('uploads/companyPolicy/'); @endphp
                            <input type="file"
                                class="{{ VC::FM_CT }}"
                                name="attachment"
                                id="attachment">
                            <img id="preview"
                                width="25%"
                                class="mt-3"
                                src="{{ $companyPolicy->attachment ? $policyPath.$companyPolicy->attachment : $policyPath.'default.png' }}" />
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script async src="{{ asset('assets/js/routes/companyPolicies/lang/edit.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/companyPolicies/preview.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="alert alert-danger">
        {{ __('Company Policy not found.') }}
    </div>
@endif



