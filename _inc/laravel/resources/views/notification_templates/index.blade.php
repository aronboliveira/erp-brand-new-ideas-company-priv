@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth, Route};
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ $notification_template->name }}
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
                    <ul class="list-unstyled mb-0 m-2 me-0">
                        <li class="{{ ViewClassNamesConstants::LNG_DD_IT }}">
                            <a class="{{ ViewClassNamesConstants::EM_DRP_NO_ARROW }}" data-bs-toggle="dropdown"
                               href="#" role="button" aria-haspopup="false" aria-expanded="false"
                               id="dropdownLanguage">
                            <span
                                class="drp-text hide-mob text-primary me-2">{{ucfirst($LangName->full_name)}}</span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="{{ ViewClassNamesConstants::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage">
                                @foreach ($languages as $code => $language)
                                    <a href="{{ route('notification_templates.index', [$notification_template->id, $code]) }}"
                                       class="dropdown-item {{ $curr_noti_tempLang->lang == $code ? 'text-primary' : '' }}">
                                        {{ucFirst($language)}}
                                    </a>
                                @endforeach
                            </div>
                        </li>
                    </ul>
                    <ul class="list-unstyled mb-0 m-2 me-2">
                        <li class="{{ ViewClassNamesConstants::LNG_DD_IT }}">
                            <a class="{{ ViewClassNamesConstants::EM_DRP_NO_ARROW }}" data-bs-toggle="dropdown"
                               href="#" role="button" aria-haspopup="false" aria-expanded="false"
                               id="dropdownLanguage">
                                <span class="drp-text hide-mob text-primary">{{ __('Template: ') }}{{ $notification_template->name }}</span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="{{ ViewClassNamesConstants::DRP_MN_EM }}" aria-labelledby="dropdownLanguage">
                                @foreach ($notification_templates as $notification)
                                    <a href="{{ route('notification_templates.index', [$notification->id,(Request::segment(3)?Request::segment(3):Auth::user()->lang)]) }}"
                                       class="dropdown-item {{$notification->name == $notification_template->name ? 'text-primary' : '' }}">{{ $notification->name }}
                                    </a>
                                @endforeach
                            </div>
                        </li>
                    </ul>

                    {{-- start for ai module--}}
                    @php
                    $user = \App\Models\User::find(Auth::user()->creatorId());
                    $plan= \App\Models\Plan::getPlan($user->plan);
                @endphp
                    @if($plan->chatgpt == 1)
                        <ul class="list-unstyled mb-0 mt-3">
                            <div class="">
                                <a href="#" data-size="md" class="btn btn-primary btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['notification template']) }}"
                                   data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                                    <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
                                </a>
                            </div>
                        </ul>
                    @endif

                    {{-- end for ai module--}}
                </div>
            </div>
        </div>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body ">
                    <h5 class= "font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-header card-body">
                                <div class="row text-xs">
                                    <h6 class="font-weight-bold mb-4">{{__('Variables')}}</h6>

                                    @php
                                        $variables = json_decode($curr_noti_tempLang->variables);
                                    @endphp
                                    @if(!empty($variables) > 0)
                                        @foreach  ($variables as $key => $var)
                                            <div class="col-6 pb-1">
                                                <p class="mb-1">{{__($key)}} : <span class="pull-right text-primary">{{ '{'.$var.'}' }}</span></p>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    {{Collective\Html\FormFacade::model($curr_noti_tempLang,array('route' => array('notification-templates.update', $curr_noti_tempLang->parent_id), 'method' => 'PUT')) }}
                    <div class="row">
                        <div class="form-group col-12">
                            {{Collective\Html\FormFacade::label('content',__('Notification Message'),['class'=>'form-label text-dark'])}}
                            {{Collective\Html\FormFacade::textarea('content',$curr_noti_tempLang->content,array('class'=>'form-control','required'=>'required','rows'=>'04','placeholder'=>'EX. Hello, {company_name}'))}}
                            <small>{{ __('A variable is to be used in such a way.')}} <span class="text-primary">{{ __('Ex. Hello, {user_name}')}}</span></small>
                        </div>
                    </div>
                    <hr>
                    <div class="col-md-12 text-end">
                        {{Collective\Html\FormFacade::hidden('lang',null)}}
                        <input type="submit" value="{{__('Save Changes')}}" class="{{ ViewClassNamesConstants::BT_PR_PRM10 }}">
                    </div>
                    {{ Collective\Html\FormFacade::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

