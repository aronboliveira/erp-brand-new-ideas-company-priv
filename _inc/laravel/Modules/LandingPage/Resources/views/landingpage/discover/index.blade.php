@php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,
        ViewClassNamesConstants as VC,YieldingConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
	use Illuminate\Support\Facades\{Log,Route};
	use Modules\LandingPage\Config\Constants\{ExtendingLandingPageLayoutConstants as E,
        RoutesResourcesConstants as R,
        SettingsConstants as LPC};
    use Nwidart\Modules\Facades\Module;
    $discover_of_features ??= [];
	$lpSettings ??= [];
	$logo ??= '';
    $lang = Utility::fetchUserLang();
	try {
        $lpSettings=\Modules\LandingPage\Entities\LandingPageSetting::landingPageSetting()?:[];
        $discover_of_features ??= !empty($lpSettings[LPC::DC_OF_FTS_K]) 
            ? $lpSettings[LPC::DC_OF_FTS_K]
            : [];
		$logo= Utility::getFile('uploads/landing_page_image')?:'';
	} catch (\Error $e) {
		Log::error(
			'Error fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
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
                            <div class="card-header">
                                <div class="row">
                                    <div class="{{ VC::CLMS10 }}">
                                        <h5>{{ __('Discover') }}</h5>
                                    </div>
                                </div>
                            </div>
                            {{ Form::open(array('route' => R::DV.'.store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                                                {{ Form::text(LPC::DC_HDG_K, !empty($lpSettings[LPC::DC_HDG_K]) ? $lpSettings[LPC::DC_HDG_K] : __('No heading found for discover'), ['class' => 'form-control', 'placeholder' => __('Enter Heading')]) }}
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
                                                {{ Form::text(LPC::DC_DESC_K, !empty($lpSettings[LPC::DC_DESC_K]) ? $lpSettings[LPC::DC_DESC_K] : __('No description found for discover'), ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
                                                @error('mail_port')
                                                    <span class="invalid-mail_port" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('Live Demo Link', __('Live Demo Link'), ['class' => 'form-label']) }}
                                                {{ Form::text(LPC::DC_DEMO_LNK_K, !empty($lpSettings[LPC::DC_DEMO_LNK_K]) ?$lpSettings[LPC::DC_DEMO_LNK_K] : __('No demo link available'), ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
                                                @error(LPC::DC_DEMO_LNK_K)
                                                    <span class="invalid-mail_port" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('Buy Now Link', __('Buy Now Link'), ['class' => 'form-label']) }}
                                                {{ Form::text(LPC::DC_BUY_LNK_K, !empty($lpSettings[LPC::DC_BUY_LNK_K]) ? $lpSettings[LPC::DC_BUY_LNK_K] : __('No buy now link available'), ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
                                                @error(LPC::DC_BUY_LNK_K)
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
                                    @php 
                                        $discoverCreateRoute = R::DV.'.create';
                                        $canCreateDiscover   = Route::has($discoverCreateRoute);
                                        $discoverCreateUrl   = $canCreateDiscover 
                                            ? route($discoverCreateRoute) 
                                            : '#';
                                    @endphp
                                    <div class="{{ VC::CLMS_JCE3 }}">
                                        <a 
                                            data-size="lg"
                                            data-url="{{ $discoverCreateUrl }}"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Discover Feature Create') }}"
                                            class="{{ VC::BT_SM_PM }} {{ $canCreateDiscover ? '' : 'disabled' }}"
                                            {{ $canCreateDiscover ? '' : 'aria-disabled="true"' }}
                                        >
                                            <i class="{{ VC::TI_PLS_LG }}"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                {{-- <div class="justify-content-end d-flex">

                                    <a data-size="lg" data-url="{{ route('users.create') }}" data-ajax-popup="true"  data-bs-toggle="tooltip" title="{{__('Create')}}"  class="{{ VC::BT_SM_PM }}">
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
                                           @if (Utility::isFilled($discover_of_features))
                                                @php
                                                    $no = 1;
                                                @endphp
                                                @foreach ($discover_of_features as $key => $feature)
                                                    @if(Utility::isFilled($feature))
                                                        <tr>
                                                            <td>{{ $no++ }}</td>
                                                            <td>{{ !empty($feature[LPC::DC_HDG_K]) ? $feature[LPC::DC_HDG_K] : __('Unnamed Feature') }}</td>
                                                            @php
                                                                $editRoute    = R::DV.'.edit';
                                                                $deleteRoute  = R::DV.'.delete';
                                                                $canEdit      = Route::has($editRoute);
                                                                $canDelete    = Route::has($deleteRoute);
                                                                $editUrl      = $canEdit   ? route($editRoute, $key)    : '#';
                                                            @endphp
                                                            <td>
                                                                <span>
                                                                    <div class="action-btn {{ VC::BG_P }} ms-2">
                                                                        @if($canEdit)
                                                                            <a href="{{ $editUrl }}"
                                                                            class="{{ VC::BT_SM_CT }}"
                                                                            data-url="{{ $editUrl }}"
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
                                                                        @if($canDelete)
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
                                                    @endif
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="text-center">
                                                            {{ __('No discover feature found') }}
                                                        </div>
                                                    </td>
                                                </tr>
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


