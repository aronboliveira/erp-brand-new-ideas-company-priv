@php


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
                                        <h5>{{ __('Feature') }}</h5>
                                    </div>
                                </div>
                            </div>
                            {{ Form::open(array('route' => VW::FT.'.store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('Title', __('Title'), ['class' => 'form-label']) }}
                                                {{ Form::text(LPSC::FT_TTL_K,!empty($lpSettings[LPSC::FT_TTL_K]) ? $lpSettings[LPSC::FT_TTL_K] : null, ['class' => 'form-control', 'placeholder' => __('Enter Title')]) }}
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
                                                {{ Form::text('feature_heading',!empty($lpSettings['feature_heading']) ? $lpSettings['feature_heading'] : null, ['class' => 'form-control', 'placeholder' => __('Enter Heading')]) }}
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
                                                {{ Form::text('feature_description', !empty($lpSettings['feature_description']) ? $lpSettings['feature_description'] : null, ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
                                                @error('mail_port')
                                                <span class="invalid-mail_port" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('Buy Now Link', __('Buy Now Link'), ['class' => 'form-label']) }}
                                                {{ Form::text('feature_buy_now_link', !empty($lpSettings['feature_buy_now_link']) ? $lpSettings['feature_buy_now_link'] : null, ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
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
                                <div class="{{ VC::R_ALC }}">
                                    <div class="{{ VC::CLMS9 }}">
                                        {{-- <h5>{{ __('Menu Bar') }}</h5> --}}
                                    </div>
                                    <div class="{{ VC::CLMS_JCE3 }}">
                                        @php
                                            $createFeatureRoute = VW::FT.'.create';
                                            $canCreateFeature   = Route::has($createFeatureRoute);
@endphp
                                        <a
                                            data-size="lg"
                                            data-url="{{ $canCreateFeature ? route($createFeatureRoute) : '#' }}"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Create') }}"
                                            class="{{ VC::BT_SM_PM }} {{ $canCreateFeature ? '' : 'disabled' }}"
                                            {{ $canCreateFeature ? '' : 'aria-disabled="true"' }}
                                        >
                                            <i class="{{ VC::TI_PLS_LG }}"></i>
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
                                           @if (Utility::isFilled($feature_of_features ?? []))
                                                @php
                                                    $ff_no = 1;
@endphp
                                                @foreach ($feature_of_features as $key => $value)
                                                    <tr>
                                                        <td>{{ $ff_no++ }}</td>
                                                        <td>{{ !empty($value['feature_heading']) ? $value['feature_heading'] : __('No heading available for feature') }}</td>
                                                        <td>
                                                            <span>
                                                                <div class="action-btn {{ VC::BG_P }} ms-2">
                                                                    @if(Route::has(VW::FT.'.edit'))
                                                                        <a href="#"
                                                                           class="{{ VC::BT_SM_CT }}"
                                                                           data-url="{{ route(VW::FT.'.edit', $key) }}"
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
                                                                    @if(Route::has(VW::FT.'.destroy'))
                                                                        {!! Form::open([
                                                                            'method' => 'GET',
                                                                            'route'  => [VW::FT.'.destroy', $key],
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
                                            @else
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="text-center">
                                                            {{ __('No data available for features') }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="{{ VC::CLMS10 }}">
                                        <h5>{{ __('Feature') }}</h5>
                                    </div>
                                </div>
                            </div>
                            @php
                                try {
                                    $lang = Utility::fetchUserLang();

                                    $logo = Utility::getFile('uploads/logo');
                                    $lp = is_array($lpSettings ?? null) ? $lpSettings : [];

                                    $heading = $lp['highlight_feature_heading'] ?? __('No heading available for feature highlight');
                                    $desc    = $lp['highlight_feature_description'] ?? __('No description available for feature highlight');
                                    $img     = $lp['highlight_feature_image'] ?? null;

                                    $storeBase     = R::FT . '.highlight.store';
                                    $storeKebab    = Str::kebab($storeBase);
                                    $storeResolved = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
                                    $storeUrl      = $storeResolved ? route($storeResolved) : '#';
                                    $storeGuard    = Utility::fetchLinkMessage($lang, R::FT, 'highlight_store_route_unavailable')
                                                    ?? __('Store Highlight Feature route is unavailable. Please contact technical support or your domain administrator.');
                                } catch (\Throwable $e) {
                                    \Log::error('Modules/LandingPage/Resources/views/landingpage/features/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            {{ Form::open([
                                'url'               => $storeUrl,
                                'method'            => 'post',
                                'enctype'           => 'multipart/form-data',
                                'id'                => 'highlight-feature-store-form',
                                'data-url'          => $storeUrl,
                                'data-guard-msg'    => $storeGuard,
                                'data-sv-localized' => 'true'
                            ]) }}
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="{{ VC::CM6 }}">
                                            <div class="form-group">
                                                {{ Form::label('highlight_feature_heading', __('Heading'), ['class' => 'form-label']) }}
                                                {{ Form::text('highlight_feature_heading', $heading, ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
                                                @error('highlight_feature_heading')
                                                <span class="invalid-mail_port" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="{{ VC::CM6 }}">
                                            <div class="form-group">
                                                {{ Form::label('highlight_feature_description', __('Description'), ['class' => 'form-label']) }}
                                                {{ Form::text('highlight_feature_description', $desc, ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
                                                @error('highlight_feature_description')
                                                <span class="invalid-mail_port" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="{{ VC::CM6 }}">
                                            <div class="form-group">
                                                {{ Form::label('Logo', __('Logo'), ['class' => 'form-label']) }}
                                                <div class="logo-content mt-2">
                                                    <img id="image1" src="{{ $img ? asset($logo . '/' . $img) : asset("assets/images/logo-light.webp") }}" class="big-logo img_setting" alt="{{__('Feature Highlight Image')}}"
                                                    onerror='this.src="{{ asset("assets/images/logo-light.webp") }}"'/>
                                                </div>
                                                <div class="choose-files mt-4">
                                                    <label for="highlight_feature_image">
                                                        <div class="{{ VC::BG_P }} dark_logo_update" style="cursor:pointer;">
                                                            <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
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
                                    <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                </div>
                                <script defer src="{{ asset('assets/js/routes/features/highlight/store.js') }}"></script>
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

                                            try {
                                                $createFeatureRoute = VW::FT.'.create';
                                                $canCreateFeature   = Route::has($createFeatureRoute);
                                                $createFeatureUrl   = $canCreateFeature
                                                    ? route($createFeatureRoute)
                                                    : '#';
                                            } catch (\Throwable $e) {
                                                \Log::error('Modules/LandingPage/Resources/views/landingpage/features/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <a
                                            data-size="lg"
                                            data-url="{{ $createFeatureUrl }}"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Create') }}"
                                            class="{{ VC::BT_SM_PM }} {{ $canCreateFeature ? '' : 'disabled' }}"
                                            {{ $canCreateFeature ? '' : 'aria-disabled="true"' }}
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
                                            @if (Utility::isFilled($other_features ?? []))
                                                @php
                                                    try {
                                                        $of_no = 1;
                                                        Log::info('Other Features data:');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('Modules/LandingPage/Resources/views/landingpage/features/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                @foreach ($other_features as $key => $value)
                                                    <tr>
                                                        <td>{{ $of_no++ }}</td>
                                                        <td>{{ !empty($value['other_features_heading']) ? $value['other_features_heading'] : __('No heading available') }}</td>
                                                        @php
                                                            try {
                                                                $editRoute    = VW::FT.'.edit';
                                                                $deleteRoute  = VW::FT.'.delete';
                                                                $canEdit      = Route::has($editRoute);
                                                                $canDelete    = Route::has($deleteRoute);
                                                                $editUrl      = $canEdit   ? route($editRoute,   $key) : '#';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('Modules/LandingPage/Resources/views/landingpage/features/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <td>
                                                            <span>
                                                                <div class="action-btn {{ VC::BG_P }} ms-2">
                                                                    <a
                                                                        href="{{ $editUrl }}"
                                                                        class="{{ VC::BT_SM_CT }} {{ $canEdit ? '' : 'disabled' }}"
                                                                        {{ $canEdit ? "data-url={$editUrl}" : 'aria-disabled="true"' }}
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Edit Page') }}"
                                                                        data-size="lg"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        data-original-title="{{ __('Edit') }}"
                                                                    >
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    @if($canDelete)
                                                                        {!! Form::open([
                                                                            'method' => 'GET',
                                                                            'route'  => [$deleteRoute, $key],
                                                                            'id'     => 'delete-form-' . $key
                                                                        ]) !!}
                                                                            <a
                                                                                href="#"
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
                                                                        <a
                                                                            href="#"
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
                                            @else
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="text-center">
                                                            {{ __('No data available for other features') }}
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
