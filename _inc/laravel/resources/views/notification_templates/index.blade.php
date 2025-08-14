@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PlansConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Plan,User,Utility};
    use Illuminate\Support\Facades\{Auth, Route, Request};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@if(isset($notification_template) && !empty($notification_template))
    @php
        $tempName = $notification_template->name;
    @endphp
    @section(YieldingConstants::ADM_PG_TTL)
        {{ $tempName }}
    @endsection
    @section(YieldingConstants::ADM_BDC)
        <li class="breadcrumb-item">
            <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
            {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('Notification Template') }}</li>
    @endsection
    @push('pre-purpose-css-page')
        <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    @endpush
    @section(YieldingConstants::ADM_ACT_BTN)
        <div class="row">
            <div class="text-end mb-3">
                <div class="text-end">
                    <div class="d-flex justify-content-end drp-languages">
                        @if(isset($languages) && (is_array($languages) && count($languages) > 0 || $languages instanceof \Illuminate\Support\Collection && $languages->count() > 0))
                            <ul class="list-unstyled mb-0 m-2 me-0">
                                <li class="{{ VC::LNG_DD_IT }}">
                                    <a class="{{ VC::EM_DRP_NO_ARROW }}" data-bs-toggle="dropdown"
                                    href="#" role="button" aria-haspopup="false" aria-expanded="false"
                                    id="dropdownLanguage">
                                        @php
                                            $langName = data_get($LangName, 'full_name', '');
                                            $displayLangName = !empty($langName) ? ucfirst(e($langName)) : __('Select Language');
                                        @endphp
                                        <span class="drp-text hide-mob text-primary me-2">{{ $displayLangName }}</span>
                                        <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                    </a>
                                    <div class="{{ VC::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage">
                                        @foreach ($languages as $code => $language)
                                            @if(!empty($code) && !empty($language))
                                                @php
                                                    $notificationId = data_get($notification_template, 'id');
                                                    $currentLang = data_get($curr_noti_tempLang, 'lang', '');
                                                    $isActive = ($currentLang === $code);
                                                @endphp
                                                
                                                @if(!empty($notificationId))
                                                    <a href="{{ route(ViewsConstants::NTF_TMP.'.index', [$notificationId, $code]) }}"
                                                    class="dropdown-item {{ $isActive ? 'text-primary' : '' }}">
                                                        {{ ucfirst(e($language)) }}
                                                    </a>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                </li>
                            </ul>
                        @endif
                        @if(isset($notification_templates) && is_countable($notification_templates) && count($notification_templates) > 0)
                            <ul class="list-unstyled mb-0 m-2 me-2">
                                <li class="{{ VC::LNG_DD_IT }}">
                                    <a class="{{ VC::EM_DRP_NO_ARROW }}" data-bs-toggle="dropdown"
                                    href="#" role="button" aria-haspopup="false" aria-expanded="false"
                                    id="dropdownTemplate">
                                        @php
                                            $displayTempName = isset($tempName) && !empty($tempName) ? e($tempName) : __('No Template');
                                        @endphp
                                        <span class="drp-text hide-mob text-primary">{{ __('Template: ') }}{{ $displayTempName }}</span>
                                        <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                    </a>
                                    <div class="{{ VC::DRP_MN_EM }}" aria-labelledby="dropdownTemplate">
                                        @foreach ($notification_templates as $notification)
                                            @if(isset($notification) && is_object($notification))
                                                @php
                                                    $notificationId = data_get($notification, 'id');
                                                    $notificationName = data_get($notification, 'name', __('Unnamed Template'));
                                                    $requestSegment = '';
                                                    if (class_exists('Request') && method_exists('Request', 'segment')) {
                                                        try {
                                                            $requestSegment = Request::segment(3);
                                                        } catch (Exception $e) {
                                                            $requestSegment = '';
                                                        }
                                                    }
                                                    $userLang = '';
                                                    if (isset($user) && is_object($user))
                                                        $userLang = data_get($user, 'lang', '');
                                                    $languageParam = !empty($requestSegment) ? $requestSegment : (!empty($userLang) ? $userLang : DatabaseConstants::DEFAULT_LANG);
                                                    $isActiveTemplate = (isset($tempName) && $notificationName === $tempName);
                                                @endphp
                                                
                                                @if(!empty($notificationId))
                                                    <a href="{{ route(ViewsConstants::NTF_TMP.'.index', [$notificationId, $languageParam]) }}"
                                                    class="dropdown-item {{ $isActiveTemplate ? 'text-primary' : '' }}">
                                                        {{ e($notificationName) }}
                                                    </a>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                </li>
                            </ul>
                        @endif
                        @if(isset($user) && is_object($user) && method_exists($user, 'creatorId'))
                            @php
                                $planUser = null;
                                $plan = null;
                                $showAiButton = false;
                                try {
                                    $creatorId = $user->creatorId();
                                    if (!empty($creatorId) && class_exists('User'))
                                        $planUser = User::find($creatorId);
                                    if (isset($planUser) && is_object($planUser)) {
                                        $userPlan = data_get($planUser, 'plan');
                                        $defaultPlan = defined('DatabaseConstants::DEFAULT_PLAN') ? DatabaseConstants::DEFAULT_PLAN : 1;
                                        $planId = !empty($userPlan) ? $userPlan : $defaultPlan;
                                        if (class_exists('Plan') && method_exists('Plan', 'getPlan'))
                                            $plan = Plan::getPlan($planId);
                                    }
                                    if (isset($plan) && is_object($plan)) {
                                        $gptFeature = defined('PlansConstants::COL_GPT') ? PlansConstants::COL_GPT : 'gpt';
                                        $gptEnabled = data_get($plan, $gptFeature, 0);
                                        $showAiButton = ($gptEnabled == 1);
                                    }
                                } catch (Exception $e) {
                                    $showAiButton = false;
                                }
                            @endphp
                            @if($showAiButton)
                                <div class="float-end">
                                    @php
                                        $generateRoute = '';
                                        try {
                                            if (Route::has('generate'))
                                                $generateRoute = route('generate', ['notification template']);
                                        } catch (Exception $e) {
                                            $generateRoute = '#';
                                        }
                                    @endphp
                                    <a href="#" 
                                       data-size="md" 
                                       class="btn btn-primary btn-icon btn-sm" 
                                       data-ajax-popup-over="true" 
                                       data-url="{{ $generateRoute }}"
                                       data-bs-placement="top" 
                                       data-title="{{ __('Generate content with AI') }}">
                                        <i class="{{ VC::FAS_RB }}"></i> 
                                        <span>{{ __('Generate with AI') }}</span>
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @section(YieldingConstants::ADM_CTT)
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="card">
                                <div class="card-header card-body">
                                    <div class="row text-xs">
                                        <h6 class="font-weight-bold mb-4">{{ __('Variables') }}</h6>
                                        @php
                                            $variables = [];
                                            $variablesRaw = data_get($curr_noti_tempLang, 'variables', '');
                                            if (!empty($variablesRaw)) {
                                                try {
                                                    $decoded = json_decode($variablesRaw, true);
                                                    $variables = is_array($decoded) ? $decoded : [];
                                                } catch (Exception $e) {
                                                    $variables = [];
                                                }
                                            }
                                        @endphp
                                        @if(!empty($variables) && is_array($variables) && count($variables) > 0)
                                            @foreach($variables as $key => $var)
                                                @if(!empty($key) && !empty($var))
                                                    <div class="col-6 pb-1">
                                                        <p class="mb-1">
                                                            {{ __(e($key)) }} : 
                                                            <span class="pull-right text-primary">{{ '{' . e($var) . '}' }}</span>
                                                        </p>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="col-12">
                                                <p class="mb-1 text-muted">{{ __('No variables available') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(isset($curr_noti_tempLang) && is_object($curr_noti_tempLang))
                            @php
                                $parentId = data_get($curr_noti_tempLang, 'parent_id');
                                $formContent = data_get($curr_noti_tempLang, 'content', '');
                                $formLang = data_get($curr_noti_tempLang, 'lang', '');
                            @endphp
                            
                            @if(!empty($parentId))
                                {{ Form::model($curr_noti_tempLang, ['route' => [ViewsConstants::NFT_TMP.'.update', $parentId], 'method' => 'PUT']) }}
                                <div class="row">
                                    <div class="form-group col-12">
                                        {{ Form::label('content', __('Notification Message'), ['class' => 'form-label text-dark']) }}
                                        {{ Form::textarea('content', e($formContent), [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'rows' => '04',
                                            'placeholder' => __('EX. Hello, {company_name}')
                                        ]) }}
                                        <small>
                                            {{ __('A variable is to be used in such a way.') }} 
                                            <span class="text-primary">{{ __('Ex. Hello, {user_name}') }}</span>
                                        </small>
                                    </div>
                                </div>
                                <hr />
                                <div class="col-md-12 text-end">
                                    {{ Form::hidden('lang', e($formLang)) }}
                                    <input type="submit" 
                                        value="{{ __('Save Changes') }}" 
                                        class="{{ VC::BT_PR_PRM10 }}">
                                </div>
                                {{ Form::close() }}
                            @else
                                <div class="alert alert-warning">
                                    {{ __('Unable to load notification template form. Missing template ID.') }}
                                </div>
                            @endif
                        @else
                            <div class="alert alert-danger">
                                {{ __('Notification template data is not available.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endsection
@else
    <p>{{ __('No notification template found.') }}</p>
@endif
