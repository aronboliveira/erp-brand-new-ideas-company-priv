@php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants as VC,YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	use Modules\LandingPage\Config\Constants\{
		ExtendingLandingPageLayoutConstants as E,
		RoutesResourcesConstants              as R,
		SettingsConstants                     as LandingPageSettingsConstants
	};
    use Nwidart\Modules\Facades\Module;
    
	$lpSettings ??= [];
	$logo       ??= '';
    $lang = Utility::fetchUserLang() ?? app()->getLocale();
	try {
		$lpSettings = \Modules\LandingPage\Entities\LandingPageSetting
			::landingPageSetting() ?: [];
		$logo       = Utility::getFile('uploads/landing_page_image') ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error fetching landing page settings and logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching landing page settings and logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching landing page settings and logo',
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
@section('breadcrumb')
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
@push(StacksConstants::ADM_SCR_PG)
<script src="{{ Module::asset('LandingPage:js/plugins/tinymce.js')}}" referrerpolicy="origin"></script>

@endpush

@section('breadcrumb')
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
                                        <h5>{{ __('Testimonials') }}</h5>
                                    </div>
                                </div>
                            </div>
                            {{ Collective\Html\FormFacade::open(array('route' => R::TTMN.'.store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
                                @csrf
                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Collective\Html\FormFacade::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                                                {{ Collective\Html\FormFacade::text($lpSettings[LandingPageSettingsConstants::TM_HDG_K],$lpSettings[LandingPageSettingsConstants::TM_HDG_K], ['class' => 'form-control', 'placeholder' => __('Enter Heading')]) }}
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
                                                {{ Collective\Html\FormFacade::text($lpSettings[LandingPageSettingsConstants::TM_DESC_K], $lpSettings[LandingPageSettingsConstants::TM_DESC_K], ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
                                                @error($lpSettings[LandingPageSettingsConstants::TM_DESC_K])
                                                <span class="invalid-testimonials_description" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                {{ Collective\Html\FormFacade::label('Long Description', __('Long Description'), ['class' => 'form-label']) }}
                                                {{ Collective\Html\FormFacade::textarea($lpSettings[LandingPageSettingsConstants::TM_LONG_DESC_K], $lpSettings[LandingPageSettingsConstants::TM_LONG_DESC_K], ['class' => 'form-control', 'placeholder' => __('Enter Long Description')]) }}
                                                @error($lpSettings[LandingPageSettingsConstants::TM_LONG_DESC_K])
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
                            {{ Collective\Html\FormFacade::close() }}
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="{{ VC::CLMS9 }}">
                                        {{-- <h5>{{ __('Menu Bar') }}</h5> --}}
                                    </div>
                                    <div class="{{ VC::CLMS_JCE3 }}">
                                        @php $createRoute = R::TTMN.'.create'; @endphp
                                        <a
                                            data-size="lg"
                                            data-url="{{ Route::has($createRoute) ? route($createRoute) : '#' }}"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Discover Feature Create') }}"
                                            class="btn btn-sm btn-primary {{ Route::has($createRoute) ? '' : 'disabled' }}"
                                            {{ Route::has($createRoute) ? '' : 'aria-disabled="true"' }}
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
                                           @if (is_array($testimonials) || is_object($testimonials))
                                            @php
                                                $no = 1
                                            @endphp
                                                @foreach ($testimonials as $key => $value)
                                                    <tr>
                                                        <td>{{ $no++ }}</td>
                                                        <td>{{ $value[LandingPageSettingsConstants::TM_TTL_K] }}</td>
                                                        <td>
                                                            <span>
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    @if(Route::has(R::TTMN.'.edit'))
                                                                        <a href="#"
                                                                           class="mx-3 btn btn-sm align-items-center"
                                                                           data-url="{{ route(R::TTMN.'.edit', $key) }}"
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
                                                                    @if(Route::has(R::TTMN.'.delete'))
                                                                        {!! Collective\Html\FormFacade::open([
                                                                            'method' => 'GET',
                                                                            'route'  => [R::TTMN.'.delete', $key],
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
                                                                                <i class="ti ti-trash text-white"></i>
                                                                            </a>
                                                                        {!! Collective\Html\FormFacade::close() !!}
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



