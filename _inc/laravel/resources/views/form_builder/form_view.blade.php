@php
	use App\Config\Constants\{
		DatabaseConstants,
		ExtendingLayoutsConstants,
		SettingsConstants
	};
	use App\Models\Utility;
	use Illuminate\Support\Facades\Log;
	$data ??= [];
	$colorSettings ??= [];
	$logo ??= '';
	$company_favicon ??= '';
	$favicon ??= '';
	$meta_title ??= '';
	$meta_desc ??= '';
	$meta_image ??= '';
	$meta_logo ??= '';
	$get_cookie ??= '';
	$faviconUrl ??= '';
	try {
		$data = Utility::prepareCommonViewData() ?: [];
		$colorSettings = $data[SettingsConstants::CLR_STG] ?? [];
		$logo = $data[SettingsConstants::LOGO] ?? '';
		$company_favicon = $data[SettingsConstants::FAV_ICN] ?? '';
		$favicon = $data[SettingsConstants::FAV_ICN] ?? '';
		$meta_title = $data[SettingsConstants::MT_TTL_K] ?? '';
		$meta_desc = $data[SettingsConstants::MT_DESC_LONG] ?? '';
		$meta_image = $data[SettingsConstants::MT_IMG_K] ?? '';
		$meta_logo = $data[SettingsConstants::MT_LOGO] ?? '';
		$get_cookie = $data[SettingsConstants::CK_STG] ?? '';
		$faviconUrl = Utility::getCompanyLogo() ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error fetching layout meta/view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching layout meta/view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching layout meta/view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	}
    $data = Utility::fallbackSettings($data);
@endphp
<html lang="{{ str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
    <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
    <head>
        <title>{{(Utility::getValByName('title_text')) ? Utility::getValByName('title_text') : config('app.name', 'ERPNovaPrestech')}} - Form Builder</title>
        {{--    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>--}}
        {{--    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>--}}
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc
        ])
        @include('fragments.og', [
            'meta_title' => $meta_title, 
            'meta_desc' => $meta_desc, 
            'meta_image' => $meta_image,
            'meta_logo' => $meta_logo
        ])
        @include('fragments.x', [
            'meta_title' => $meta_title, 
            'meta_desc' => $meta_desc, 
            'meta_image' => $meta_image,
            'meta_logo' => $meta_logo
        ])
        @include('fragments.favicon', ['faviconUrl' => $faviconUrl])
        @include('fragments.stylesheets', ['settings' => $colorSettings]);
    </head>
    <body class="theme-4">
        <div class="dash-content">
            <div class="min-vh-100 py-5 d-flex align-items-center">
                <div class="w-100">
                    <div class="row justify-content-center">
                        <div class="col-sm-8 col-lg-5">
                            <div class="row justify-content-center mb-3">
                                <a class="{{ ViewClassNamesConstants::NVB_BR }}" href="#">
                                    <img src="{{asset(Storage::url('uploads/logo/'.SettingsConstants::CPN_LG_DK_DEF))}}" class="navbar-brand-img big-logo">
                                </a>
                            </div>
                            <div class="card shadow zindex-100 mb-0">
                                @if($form->is_active == 1)
                                    {{Collective\Html\FormFacade::open(array('route'=>array('form.view.store'),'method'=>'post'))}}
                                    <div class="card-body px-md-5 py-5">
                                        <div class="mb-4">
                                            <h6 class="h3">{{$form->name}}</h6>
                                        </div>
                                        <input type="hidden" value="{{$code}}" name="code">
                                        @if($objFields && $objFields->count() > 0)
                                            @foreach($objFields as $objField)
                                                @if($objField->type == 'text')
                                                    <div class="form-group">
                                                        {{ Collective\Html\FormFacade::label('field-'.$objField->id, __($objField->name),['class'=>'form-label']) }}
                                                        {{ Collective\Html\FormFacade::text('field['.$objField->id.']', null, array('class' => 'form-control','required'=>'required','id'=>'field-'.$objField->id)) }}
                                                    </div>
                                                @elseif($objField->type == 'email')
                                                    <div class="form-group">
                                                        {{ Collective\Html\FormFacade::label('field-'.$objField->id, __($objField->name),['class'=>'form-label']) }}
                                                        {{ Collective\Html\FormFacade::email('field['.$objField->id.']', null, array('class' => 'form-control','required'=>'required','id'=>'field-'.$objField->id)) }}
                                                    </div>
                                                @elseif($objField->type == 'number')
                                                    <div class="form-group">
                                                        {{ Collective\Html\FormFacade::label('field-'.$objField->id, __($objField->name),['class'=>'form-label']) }}
                                                        {{ Collective\Html\FormFacade::number('field['.$objField->id.']', null, array('class' => 'form-control','required'=>'required','id'=>'field-'.$objField->id)) }}
                                                    </div>
                                                @elseif($objField->type == 'date')
                                                    <div class="form-group">
                                                        {{ Collective\Html\FormFacade::label('field-'.$objField->id, __($objField->name),['class'=>'form-label']) }}
                                                        {{ Collective\Html\FormFacade::date('field['.$objField->id.']', null, array('class' => 'form-control','required'=>'required','id'=>'field-'.$objField->id)) }}
                                                    </div>
                                                @elseif($objField->type == 'textarea')
                                                    <div class="form-group">
                                                        {{ Collective\Html\FormFacade::label('field-'.$objField->id, __($objField->name),['class'=>'form-label']) }}
                                                        {{ Collective\Html\FormFacade::textarea('field['.$objField->id.']', null, array('class' => 'form-control','required'=>'required','id'=>'field-'.$objField->id)) }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                        <div class="mt-4 text-end">

                                            {{Collective\Html\FormFacade::submit(__('Submit'),array('class'=>'btn btn-primary'))}}
                                        </div>
                                    </div>

                                    {{Collective\Html\FormFacade::close()}}
                                @else
                                    <div class="page-title"><h5>{{__('Form is not active.')}}</h5></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('partials.admin.footer')

        @if($get_cookie['enable_cookie'] == 'on')
            @includeIf(ExtendingLayoutsConstants::CKC)
        @endif

    </body>
</html>
