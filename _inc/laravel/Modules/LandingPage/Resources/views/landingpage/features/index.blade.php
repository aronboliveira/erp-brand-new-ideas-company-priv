@php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants,YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	use Modules\LandingPage\Config\Constants\{ExtendingLandingPageLayoutConstants as E,RoutesResourcesConstants as R,SettingsConstants as LandingPageSettingsConstants};
    use Nwidart\Modules\Facades\Module;
    
    $lang = Utility::fetchUserLang();
	$lpSettings ??= [];
	$logo ??= '';
	try {
		$lpSettings=\Modules\LandingPage\Entities\LandingPageSetting
			::landingPageSetting()?:[];
		$logo=Utility::getFile('uploads/landing_page_image')?:'';
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

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="{{ ViewClassNamesConstants::CD_STK }}" style="top:30px">
                        <div class="{{ ViewClassNamesConstants::LG_FLSH }}" id="useradd-sidenav">
                            @include(R::LP.'::'.E::LOS.'.tab')
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    {{--  Start for all settings tab --}}
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="{{ ViewClassNamesConstants::CLMS10 }}">
                                        <h5>{{ __('Feature') }}</h5>
                                    </div>
                                </div>
                            </div>
                            {{ Collective\Html\FormFacade::open(array('route' => 'features.store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Collective\Html\FormFacade::label('Title', __('Title'), ['class' => 'form-label']) }}
                                                {{ Collective\Html\FormFacade::text(LandingPageSettingsConstants::FT_TTL_K,$lpSettings[LandingPageSettingsConstants::FT_TTL_K], ['class' => 'form-control', 'placeholder' => __('Enter Title')]) }}
                                                @error('mail_host')
                                                <span class="invalid-mail_driver" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Collective\Html\FormFacade::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                                                {{ Collective\Html\FormFacade::text('feature_heading',$lpSettings['feature_heading'], ['class' => 'form-control', 'placeholder' => __('Enter Heading')]) }}
                                                @error('mail_host')
                                                <span class="invalid-mail_driver" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Collective\Html\FormFacade::label('Description', __('Description'), ['class' => 'form-label']) }}
                                                {{ Collective\Html\FormFacade::text('feature_description', $lpSettings['feature_description'], ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
                                                @error('mail_port')
                                                <span class="invalid-mail_port" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Collective\Html\FormFacade::label('Buy Now Link', __('Buy Now Link'), ['class' => 'form-label']) }}
                                                {{ Collective\Html\FormFacade::text('feature_buy_now_link', $lpSettings['feature_buy_now_link'], ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
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
                                    <button class="{{ ViewClassNamesConstants::BT_PR_PRM10 }}" type="submit" >{{ __('Save Changes') }}</button>
                                </div>
                            {{ Collective\Html\FormFacade::close() }}
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="{{ ViewClassNamesConstants::CLMS9 }}">
                                        {{-- <h5>{{ __('Menu Bar') }}</h5> --}}
                                    </div>
                                    <div class="{{ ViewClassNamesConstants::CLMS_JCE3 }}">
                                        @php
                                            $createFeatureRoute = 'features.create';
                                            $canCreateFeature   = Route::has($createFeatureRoute);
                                        @endphp
                                        <a
                                            data-size="lg"
                                            data-url="{{ $canCreateFeature ? route($createFeatureRoute) : '#' }}"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Create') }}"
                                            class="{{ ViewClassNamesConstants::BT_SM_PM }} {{ $canCreateFeature ? '' : 'disabled' }}"
                                            {{ $canCreateFeature ? '' : 'aria-disabled="true"' }}
                                        >
                                            <i class="{{ ViewClassNamesConstants::TI_PLS_LG }}"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
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
                                           @if (is_array($feature_of_features) || is_object($feature_of_features))
                                           @php
                                               $ff_no = 1
                                           @endphp
                                                @foreach ($feature_of_features as $key => $value)
                                                    <tr>
                                                        <td>{{ $ff_no++ }}</td>
                                                        <td>{{ $value['feature_heading'] }}</td>
                                                        <td>
                                                            <span>
                                                                <div class="action-btn {{ ViewClassNamesConstants::BG_P }} ms-2">
                                                                    @if(Route::has('features.edit'))
                                                                        <a href="#"
                                                                           class="mx-3 btn btn-sm align-items-center"
                                                                           data-url="{{ route('features.edit', $key) }}"
                                                                           data-ajax-popup="true"
                                                                           data-title="{{ __('Edit Page') }}"
                                                                           data-size="lg"
                                                                           data-bs-toggle="tooltip"
                                                                           title="{{ __('Edit') }}"
                                                                           data-original-title="{{ __('Edit') }}"
                                                                        >
                                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                                        </a>
                                                                    @else
                                                                        <a href="#"
                                                                           class="{{ ViewClassNamesConstants::BT_SM_CT_DSB }}"
                                                                           aria-disabled="true"
                                                                           data-bs-toggle="tooltip"
                                                                           title="{{ __('Edit') }}"
                                                                        >
                                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                                    @if(Route::has('features.destroy'))
                                                                        {!! Collective\Html\FormFacade::open([
                                                                            'method' => 'GET',
                                                                            'route'  => ['features.destroy', $key],
                                                                            'id'     => 'delete-form-' . $key
                                                                        ]) !!}
                                                                            <a href="#"
                                                                               class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                                               data-bs-toggle="tooltip"
                                                                               title="{{ __('Delete') }}"
                                                                               data-original-title="{{ __('Delete') }}"
                                                                               data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                               data-confirm-yes="document.getElementById('delete-form-{{ $key }}').submit();"
                                                                            >
                                                                                <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                                            </a>
                                                                        {!! Collective\Html\FormFacade::close() !!}
                                                                    @else
                                                                        <a href="#"
                                                                           class="{{ ViewClassNamesConstants::BT_SM_CT_DSB }}"
                                                                           aria-disabled="true"
                                                                           data-bs-toggle="tooltip"
                                                                           title="{{ __('Delete') }}"
                                                                        >
                                                                            <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
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
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="{{ ViewClassNamesConstants::CLMS10 }}">
                                        <h5>{{ __('Feature') }}</h5>
                                    </div>
                                </div>
                            </div>
                            {{ Collective\Html\FormFacade::open(array('route' => R::FT . '.highlight.store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Collective\Html\FormFacade::label('highlight_feature_heading', __('Heading'), ['class' => 'form-label']) }}
                                                {{ Collective\Html\FormFacade::text('highlight_feature_heading', $lpSettings['highlight_feature_heading'], ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
                                                @error('highlight_feature_heading')
                                                    <span class="invalid-mail_port" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Collective\Html\FormFacade::label('highlight_feature_heading', __('Description'), ['class' => 'form-label']) }}
                                                {{ Collective\Html\FormFacade::text('highlight_feature_description', $lpSettings['highlight_feature_description'], ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
                                                @error('highlight_feature_description')
                                                <span class="invalid-mail_port" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Collective\Html\FormFacade::label('Logo', __('Logo'), ['class' => 'form-label']) }}
                                                <div class="logo-content mt-4">
                                                    <img id="image1" src="{{ asset($logo.'/'. $lpSettings['highlight_feature_image']) }}"
                                                        class="big-logo img_setting">
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="highlight_feature_image">
                                                        <div class=" {{ ViewClassNamesConstants::BG_P }} dark_logo_update" style="cursor: pointer;"> <i class="ti ti-upload px-1">
                                                            </i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" name="highlight_feature_image" id="highlight_feature_image" class="form-control file" data-filename="highlight_feature_image">
                                                    </label>
                                                </div>
                                                @error('highlight_feature_image')
                                                <div class="row">
                                                    <span class="invalid-logo" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <input class="{{ ViewClassNamesConstants::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                </div>
                            {{ Collective\Html\FormFacade::close() }}
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="{{ ViewClassNamesConstants::CLMS9 }}">
                                        {{-- <h5>{{ __('Menu Bar') }}</h5> --}}
                                    </div>
                                    <div class="{{ ViewClassNamesConstants::CLMS_JCE3 }}">
                                        @php 
                                            $createFeatureRoute = 'features.create';
                                            $canCreateFeature   = Route::has($createFeatureRoute);
                                            $createFeatureUrl   = $canCreateFeature 
                                                ? route($createFeatureRoute) 
                                                : '#';
                                        @endphp
                                        <a
                                            data-size="lg"
                                            data-url="{{ $createFeatureUrl }}"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Create') }}"
                                            class="{{ ViewClassNamesConstants::BT_SM_PM }} {{ $canCreateFeature ? '' : 'disabled' }}"
                                            {{ $canCreateFeature ? '' : 'aria-disabled="true"' }}
                                        >
                                            <i class="{{ ViewClassNamesConstants::TI_PLS_LG }}"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                {{-- <div class="justify-content-end d-flex">
                                    <a data-size="lg" data-url="{{ route('users.create') }}" data-ajax-popup="true"  data-bs-toggle="tooltip" title="{{__('Create')}}"  class="{{ ViewClassNamesConstants::BT_SM_PM }}">
                                        <i class="{{ ViewClassNamesConstants::TI_PLS_LG }}"></i>
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
                                            @if (is_array($feature_of_features) || is_object($feature_of_features))
                                            @php
                                                $of_no = 1
                                            @endphp
                                                @foreach ($other_features as $key => $value)
                                                    <tr>
                                                        <td>{{ $of_no++ }}</td>
                                                        <td>{{ $value['other_features_heading'] }}</td>
                                                        @php
                                                            $editRoute    = 'features.edit';
                                                            $deleteRoute  = 'features.delete';
                                                            $canEdit      = Route::has($editRoute);
                                                            $canDelete    = Route::has($deleteRoute);
                                                            $editUrl      = $canEdit   ? route($editRoute,   $key) : '#';
                                                        @endphp
                                                        <td>
                                                            <span>
                                                                <div class="action-btn {{ ViewClassNamesConstants::BG_P }} ms-2">
                                                                    <a
                                                                        href="{{ $editUrl }}"
                                                                        class="mx-3 btn btn-sm align-items-center {{ $canEdit ? '' : 'disabled' }}"
                                                                        {{ $canEdit ? "data-url={$editUrl}" : 'aria-disabled="true"' }}
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Edit Page') }}"
                                                                        data-size="lg"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        data-original-title="{{ __('Edit') }}"
                                                                    >
                                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                                    @if($canDelete)
                                                                        {!! Collective\Html\FormFacade::open([
                                                                            'method' => 'GET',
                                                                            'route'  => [$deleteRoute, $key],
                                                                            'id'     => 'delete-form-' . $key
                                                                        ]) !!}
                                                                            <a
                                                                                href="#"
                                                                                class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                                                data-bs-toggle="tooltip"
                                                                                title="{{ __('Delete') }}"
                                                                                data-original-title="{{ __('Delete') }}"
                                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                                data-confirm-yes="document.getElementById('delete-form-{{ $key }}').submit();"
                                                                            >
                                                                                <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                                            </a>
                                                                        {!! Collective\Html\FormFacade::close() !!}
                                                                    @else
                                                                        <a
                                                                            href="#"
                                                                            class="{{ ViewClassNamesConstants::BT_SM_CT_DSB }}"
                                                                            aria-disabled="true"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                        >
                                                                            <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
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

