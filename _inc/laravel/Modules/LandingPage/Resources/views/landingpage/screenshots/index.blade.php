@php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants,YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	use Modules\LandingPage\Config\Constants\{ExtendingLandingPageLayoutConstants as E,RoutesResourcesConstants as R};
	$lpSettings ??= [];
	$logo ??= '';
	try {
		$lpSettings=\Modules\LandingPage\Entities\LandingPageSetting::landingPageSetting()?:[];
		$logo=Utility::getFile('uploads/landing_page_image')?:'';
	} catch(\Error $e) {
		Log::error(
			'Error fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch(\Exception $e) {
		Log::error(
			'Exception fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch(\Throwable $e) {
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
                                        <h5>{{ __('Screenshots') }}</h5>
                                    </div>
                                </div>
                            </div>
                            {{ Collective\Html\FormFacade::open(array('route' => R::SST.'.store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
                                @csrf
                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Collective\Html\FormFacade::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                                                {{ Collective\Html\FormFacade::text('screenshots_heading',$lpSettings['screenshots_heading'], ['class' => 'form-control', 'placeholder' => __('Enter Heading')]) }}
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
                                                {{ Collective\Html\FormFacade::text('screenshots_description', $lpSettings['screenshots_description'], ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
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
                                        @php $canCreate = Route::has(R::SST.'.create'); @endphp
                                        <a
                                            data-size="lg"
                                            data-url="{{ $canCreate ? route(R::SST.'.create') : '#' }}"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Create') }}"
                                            class="btn btn-sm btn-primary {{ $canCreate ? '' : 'disabled' }}"
                                            {{ $canCreate ? '' : 'aria-disabled="true"' }}
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
                                           @if (is_array($screenshots) || is_object($screenshots))
                                               @php
                                                   $no = 1
                                               @endphp
                                                @foreach ($screenshots as $key => $value)
                                                    <tr>
                                                        <td>{{ $no++ }}</td>
                                                        <td>{{ $value['screenshots_heading'] }}</td>
                                                        <td>
                                                            <span>
                                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                                    @if(Route::has(R::SST.'.edit'))
                                                                        <a href="#"
                                                                           class="mx-3 btn btn-sm align-items-center"
                                                                           data-url="{{ route(R::SST.'.edit', $key) }}"
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
                                                                        >
                                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                                    @if(Route::has(R::SST.'.delete'))
                                                                        {!! Collective\Html\FormFacade::open([
                                                                            'method' => 'GET',
                                                                            'route'  => [R::SST.'.delete', $key],
                                                                            'id'     => 'delete-form-' . $key
                                                                        ]) !!}
                                                                            <a href="#"
                                                                               class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                                               data-bs-toggle="tooltip"
                                                                               title="{{ __('Delete') }}"
                                                                               data-original-title="{{ __('Delete') }}"
                                                                               data-confirm="{{ __('Are You Sure?') . '|' . __('This action cannot be undone. Do you want to continue?') }}"
                                                                               data-confirm-yes="document.getElementById('delete-form-{{ $key }}').submit();"
                                                                            >
                                                                                <i class="ti ti-trash text-white"></i>
                                                                            </a>
                                                                        {!! Collective\Html\FormFacade::close() !!}
                                                                    @else
                                                                        <a href="#"
                                                                           class="{{ ViewClassNamesConstants::BT_SM_CT_DSB }}"
                                                                           aria-disabled="true"
                                                                        >
                                                                            <i class="ti ti-trash text-white"></i>
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



