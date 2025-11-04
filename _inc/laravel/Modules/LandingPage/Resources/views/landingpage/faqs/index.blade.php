@php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants as VC,YieldingConstants};
	use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
	use Illuminate\Support\Facades\{Log, Route};
	use Modules\LandingPage\Config\Constants\{
		ExtendingLandingPageLayoutConstants as E,
		RoutesResourcesConstants                as R,
		SettingsConstants                       as LPC
	};
    use Nwidart\Modules\Facades\Module;
    
    $lang = Utility::fetchUserLang();
	$lpSettings ??= [];
	$logo       ??= '';
	try {
		$lpSettings = \Modules\LandingPage\Entities\LandingPageSetting::landingPageSetting() ?: [];
		$logo       = Utility::getFile('uploads/landing_page_image')            ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'        =>$e->getMessage(),
				'file'           =>$e->getFile(),
				'line'           =>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'        =>$e->getMessage(),
				'file'           =>$e->getFile(),
				'line'           =>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'        =>$e->getMessage(),
				'file'           =>$e->getFile(),
				'line'           =>$e->getLine()
			]
		);
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Landing Page') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        {{ __('Landing Page') }}
    </li>
@endsection

@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href=" {{ asset('Modules/landingpage/css/summernote/summernote-bs4.css')}}" />
@endpush

@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('Modules/landingpage/js/plugins/summernote-bs4.js')}}" referrerpolicy="origin"></script>
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        {{ __('Landing Page') }}
    </li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="{{ VC::CD_STK }}" style="top:30px">
                        <div class="{{ VC::LG_FLSH }}" id="useradd-sidenav">
                            @include(R::LP.'::'.E::LOS.'.tab')
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    {{--  Start for all settings tab --}}
                        <div class="card">
                            {{ Form::open(array('route' => R::FQ.'.store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
                                @csrf
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col-6">
                                            <h5 class="mb-2">{{ __('FAQ') }}</h5>
                                        </div>
                                        <div class="col switch-width text-end">
                                            <div class="form-group mb-0">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" data-toggle="switchbutton" data-onstyle="primary" class="" name="faq_status"
                                                        id="faq_status"  {{ isset($lpSettings[LPC::FAQ_STT_K]) && $lpSettings[LPC::FAQ_STT_K] == 'on' ? 'checked="checked"' : '' }}>
                                                    <label class="custom-control-label" for="faq_status"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('Title', __('Title'), ['class' => 'form-label']) }}
                                                {{ Form::text(LPC::FAQ_TTL_K, !empty($lpSettings[LPC::FAQ_TTL_K]) ? $lpSettings[LPC::FAQ_TTL_K] : null, ['class' => 'form-control', 'placeholder' => __('Enter Title')]) }}
                                                @error('mail_host')
                                                    <span class="invalid-mail_driver" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                                                {{ Form::text(LPC::FAQ_HDG_K, !empty($lpSettings[LPC::FAQ_HDG_K]) ? $lpSettings[LPC::FAQ_HDG_K] : null, ['class' => 'form-control', 'placeholder' => __('Enter Heading')]) }}
                                                @error('mail_host')
                                                    <span class="invalid-mail_driver" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('Description', __('Description'), ['class' => 'form-label']) }}
                                                {{ Form::text(LPC::FAQ_DESC_K, !empty($lpSettings[LPC::FAQ_DESC_K]) ? $lpSettings[LPC::FAQ_DESC_K] : null, ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
                                                @error('mail_port')
                                                    <span class="invalid-mail_port" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button class="{{ VC::BT_PR_PRM10 }}" type="submit" >{{ __('Save Changes') }}</button>
                                </div>
                            {{ Form::close() }}
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="{{ VC::CLMS9 }}">
                                        {{-- <h5>{{ __('Menu Bar') }}</h5> --}}
                                    </div>
                                    <div class="{{ VC::CLMS_JCE3 }}">
                                        @php
                                            $faqCreateRoute = R::FQ.'.create';
                                            $canCreateFaq   = Route::has($faqCreateRoute);
                                            $faqCreateUrl   = $canCreateFaq ? route($faqCreateRoute) : '#';
                                        @endphp
                                        <a
                                            data-size="lg"
                                            data-url="{{ $faqCreateUrl }}"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Discover Feature Create') }}"
                                            class="btn btn-sm btn-primary {{ $canCreateFaq ? '' : 'disabled' }}"
                                            {{ $canCreateFaq ? '' : 'aria-disabled="true"' }}
                                        >
                                            <i class="{{ VC::TI_PLS_LG }}"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">

                                {{-- <div class="justify-content-end d-flex">

                                    <a data-size="lg" data-url="{{ route('users.create') }}" data-ajax-popup="true"  data-bs-toggle="tooltip" title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                                        <i class="{{ VC::TI_PLS_LG }}"></i>
                                    </a>
                                </div> --}}

                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{__('No')}}</th>
                                                <th>{{__('Name')}}</th>
                                                <th>{{__('Action')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                           @if (Utility::isFilled($faqs))
                                            @php
                                                $no = 1
                                            @endphp
                                                @foreach ($faqs as $key => $value)
                                                    <tr>
                                                        <td>{{ $no++ }}</td>
                                                        <td>{{ !empty($value['faq_questions']) ? $value['faq_questions'] : __('No heading available') }}</td>
                                                        @php
                                                            $editRoute    = R::FQ.'.edit';
                                                            $deleteRoute  = R::FQ.'.delete';
                                                            $canEditFaq   = Route::has($editRoute);
                                                            $canDeleteFaq = Route::has($deleteRoute);
                                                        @endphp
                                                        <td>
                                                            <span>
                                                                <div class="action-btn {{ VC::BG_P }} ms-2">
                                                                    @if($canEditFaq)
                                                                        <a href="{{ route($editRoute, $key) }}"
                                                                        class="{{ VC::BT_SM_CT }}"
                                                                        data-url="{{ route($editRoute, $key) }}"
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Edit Page') }}"
                                                                        data-size="lg"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        data-original-title="{{ __('Edit') }}"
                                                                        >
                                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                                        </a>
                                                                    @else
                                                                        <a href="#"
                                                                        class="{{ VC::BT_SM_CT_DSB }}"
                                                                        aria-disabled="true"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        >
                                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    @if($canDeleteFaq)
                                                                        {!! Form::open([
                                                                            'method' => 'GET',
                                                                            'route'  => [$deleteRoute, $key],
                                                                            'id'     => 'delete-form-' . $key
                                                                        ]) !!}
                                                                            <a href="#"
                                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                            data-original-title="{{ __('Delete') }}"
                                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="document.getElementById('delete-form-{{ $key }}').submit();"
                                                                            >
                                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                            </a>
                                                                        {!! Form::close() !!}
                                                                    @else
                                                                        <a href="#"
                                                                        class="{{ VC::BT_SM_CT_DSB }}"
                                                                        aria-disabled="true"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}"
                                                                        >
                                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    {{--  End for all settings tab --}}
                </div>
            </div>
        </div>
    </div>
@endsection

