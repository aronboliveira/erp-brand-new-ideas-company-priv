@php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants,ViewsConstants,YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{File,Log,Route};
	use Modules\LandingPage\Config\Constants\{ExtendingLandingPageLayoutConstants as E,RoutesResourcesConstants as R,SettingsConstants as LandingPageSettingsConstants};
    use Nwidart\Modules\Facades\Module;
    Log::debug('Loaded settings for Menubar blade...');
	$lpSettings ??= [];
	$logo ??= '';
	try {
		$lpSettings=\Modules\LandingPage\Entities\LandingPageSetting::landingPageSetting()?:[];
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
    Log::debug('Sucessfully loaded settings for Menubar blade');
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
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href=" {{ asset('Modules/landingpage/css/summernote/summernote-bs4.css')}}" />
@endpush

@push(StacksConstants::ADM_SCR_PG)
    <script>
        document.getElementById('site_logo').onchange = function () {
                var src = URL.createObjectURL(this.files[0])
                document.getElementById('image').src = src
            }
    </script>
    <script src="{{ asset('Modules/landingpage/js/plugins/summernote-bs4.js')}}" referrerpolicy="origin"></script>
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
                                    <h5>{{ __('Custom Page') }}</h5>
                                </div>
                            </div>
                        </div>
                        {{ Collective\Html\FormFacade::open(array('route' => 'custom_pages.store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Site Logo', __('Site Logo'), ['class' => 'form-label']) }}
                                            <div class="logo-content mt-4">
                                                <img
                                                    id="image"
                                                    src="{{ File::exists($logo.'/'.$lpSettings['site_logo']) ? $logo.'/'.$lpSettings['site_logo'] : asset('assets/images/logo-light.webp') }}"
                                                    height="60px"
                                                    fetchpriority="auto"
                                                    decoding="async"
                                                    loading="lazy"
                                                />
                                            </div>
                                            <div class="choose-files mt-5">
                                                <label for="site_logo">
                                                    <div class="{{ ViewClassNamesConstants::BG_P }} company_logo_update" style="cursor: pointer;">
                                                        <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                    </div>
                                                    <input type="file" name="site_logo" id="site_logo" class="form-control file" data-filename="site_logo">
                                                </label>
                                            </div>
                                            @error('site_logo')
                                                <div class="row">
                                                    <span class="invalid-logo" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Site Description', __('Site Description'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::text(LandingPageSettingsConstants::SD_K, $lpSettings[LandingPageSettingsConstants::SD_K], ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
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
                                <input class="{{ ViewClassNamesConstants::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                            </div>
                        {{ Collective\Html\FormFacade::close() }}
                    </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="{{ ViewClassNamesConstants::CLMS9 }}">
                                        <h5>{{ __('Menu Bar') }}</h5>
                                    </div>
                                    <div class="{{ ViewClassNamesConstants::CLMS_JCE3 }}">
                                        @php
                                            Log::debug('Loading creation route for custom pages...');
                                            $createRoute     = R::CT_PG . '.create';
                                            $canCreate       = Route::has($createRoute);
                                            Log::debug('Successfully loaded creation route for custom pages',
                                                [
                                                    'create_route' => $createRoute,
                                                ]);
                                        @endphp
                                        <a
                                            data-size="lg"
                                            data-url="{{ $canCreate ? route($createRoute) : '#' }}"
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
                                            @if (is_array($pages) || is_object($pages))
                                                @php
                                                  $no = 1
                                                @endphp
                                                @foreach ($pages as $key => $value)
                                                    <tr>
                                                        <td>{{ $no++ }}</td>
                                                        <td>{{ $value[LandingPageSettingsConstants::MB_PG_NM] }}</td>
                                                        <td>
                                                            @php
                                                                Log::debug('Loading routes for stateful routes for custom pages...');
                                                                $editRoute     = R::CT_PG . '.edit';
                                                                $destroyRoute  = R::CT_PG . '.destroy';
                                                                $slug          = $value[LandingPageSettingsConstants::PG_SLG] ?? '';
                                                                $canEdit       = Route::has($editRoute);
                                                                $canDestroy    = Route::has($destroyRoute)
                                                                                && ! in_array($slug, ['terms_and_conditions','about_us','privacy_policy']);
                                                                Log::debug('Successfully loaded stateful routes for custom pages',
                                                                    [
                                                                        'edit_route' => $editRoute,
                                                                        'destroy_route' => $destroyRoute,
                                                                    ]);
                                                            @endphp
                                                            <span>
                                                                <div class="action-btn {{ ViewClassNamesConstants::BG_P }} ms-2">
                                                                    @if($canEdit)
                                                                        <a href="#"
                                                                        class="mx-3 btn btn-sm align-items-center"
                                                                        data-url="{{ route($editRoute, $key) }}"
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Edit Page') }}"
                                                                        data-size="lg"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        data-original-title="{{ __('Edit') }}">
                                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                                        </a>
                                                                    @else
                                                                        <a href="#"
                                                                        class="{{ ViewClassNamesConstants::BT_SM_CT_DSB }}"
                                                                        aria-disabled="true"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}">
                                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                                    @if($canDestroy)
                                                                        {!! Collective\Html\FormFacade::open([
                                                                            'method' => 'DELETE',
                                                                            'route'  => [$destroyRoute, $key],
                                                                            'id'     => 'delete-form-' . $key
                                                                        ]) !!}
                                                                            <a href="#"
                                                                            class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                            data-original-title="{{ __('Delete') }}"
                                                                             data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="document.getElementById('delete-form-{{ $key }}').submit();">
                                                                                <i class="ti ti-trash text-white"></i>
                                                                            </a>
                                                                        {!! Collective\Html\FormFacade::close() !!}
                                                                    @else
                                                                        <a href="#"
                                                                        class="{{ ViewClassNamesConstants::BT_SM_CT_DSB }}"
                                                                        aria-disabled="true"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}">
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
                                    <script>
                                        (function(){
                                          const img = document.getElementById('image');
                                          if (!img || img.hasAttribute('data-reloading-active')) return;
                                          img.setAttribute('data-reloading-active', 'true');
                                          const fallbacks = [
                                            'public/assets/logo-light.webp',
                                            'public/assets/logo-light.png',
                                            'public/assets/logo-light.jpg',
                                            'public/assets/logo-light.jpeg',
                                            'public/assets/images/logo-light.webp',
                                            'public/assets/images/logo-light.png',
                                            'public/assets/images/logo-light.jpg',
                                            'public/assets/images/logo-light.jpeg',
                                            'public/logo-light.webp',
                                            'public/logo-light.png',
                                            'public/logo-light.jpg',
                                            'public/logo-light.jpeg'
                                          ];
                                          img.setAttribute('data-reload-attempt', img.getAttribute('data-reload-attempt') || '0');
                                          img.addEventListener('error', function() {
                                            let attempt = parseInt(this.getAttribute('data-reload-attempt'), 10);
                                            if (!Number.isFinite(attempt) || attempt < 0) attempt = 0;
                                            if (attempt === 0) {
                                              this.setAttribute('data-original-opacity', getComputedStyle(this).opacity || '1');
                                              this.style.transition = (this.style.transition || '') + 'opacity 0.25s ease-in-out';
                                              this.style.opacity = '0';
                                            }
                                            if (attempt >= fallbacks.length) {
                                              this.style.opacity = this.getAttribute('data-original-opacity') || '1';
                                              this.removeAttribute('data-reload-attempt');
                                              this.removeAttribute('data-original-opacity');
                                            } else {
                                              this.setAttribute('data-reload-attempt', String(attempt + 1));
                                              this.src = window.location.origin + '/' + fallbacks[attempt];
                                            }
                                          });
                                          img.addEventListener('load', function() {
                                            this.style.opacity = this.getAttribute('data-original-opacity') || '1';
                                            this.removeAttribute('data-reload-attempt');
                                            this.removeAttribute('data-original-opacity');
                                          });
                                        })();
                                    </script>
                                </div>
                            </div>
                        </div>
                    {{--  End for all settings tab --}}
                </div>
            </div>
        </div>
    </div>
@endsection

