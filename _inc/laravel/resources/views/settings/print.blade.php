@php
	use App\Config\Constants\{
		BillsConstants,
		ExtendingLayoutsConstants,
		SettingsConstants,
		StacksConstants,
		ViewClassNamesConstants,
		ViewsConstants,
		YieldingConstants
	};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	$data ??= [];
	$logo ??= '';
	$company_logo ??= '';
	$company_favicon ??= '';
	$lang ??= '';
	try {
		$data = Utility::prepareCommonViewData() ?: [];
		$logo = $data[SettingsConstants::LOGO] ?? '';
		$company_logo = Utility::getValByName(SettingsConstants::CPN_LG) ?: '';
		$company_favicon = $data[SettingsConstants::FAV_ICN] ?? '';
		$lang = Utility::getValByName(SettingsConstants::DEF_LNG) ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error preparing bill layout view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception preparing bill layout view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable preparing bill layout view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	}
    $data = Utility::fallbackSettings($data);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Settings')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Print-Settings')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>
    <script>
        $(document).on("change", "select[name='invoice_template'], input[name='invoice_color']", function () {
            var template = $("select[name='invoice_template']").val();
            var color = $("input[name='invoice_color']:checked").val();
            $('#invoice_frame').attr('src', '{{url('/invoices/preview')}}/' + template + '/' + color);
        });

        $(document).on("change", "select[name='proposal_template'], input[name='proposal_color']", function () {
            var template = $("select[name='proposal_template']").val();
            var color = $("input[name='proposal_color']:checked").val();
            $('#proposal_frame').attr('src', '{{url('/'.ViewsConstants::PPS.'/preview')}}/' + template + '/' + color);
        });

        $(document).on("change", "select[name=BillsConstants::COL_POS_TMP], input[name='bill_color']", function () {
            var template = $("select[name=BillsConstants::COL_POS_TMP]").val();
            var color = $("input[name='bill_color']:checked").val();
            $('#bill_frame').attr('src', '{{url('/bill/preview')}}/' + template + '/' + color);
        });

        document.getElementById('proposal_logo').onchange = function () {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('proposal_image').src = src
        }
        document.getElementById('invoice_logo').onchange = function () {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('invoice_image').src = src
        }
        document.getElementById('bill_logo').onchange = function () {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('bill_image').src = src
        }
    </script>
@endpush
@section('content')
    <div class="col-sm-12 mt-4">
        <div class="card">
            <div class="card-body">
                @php
                    $tabs = [
                        ['id' => 'proposal', 'label' => __('Proposal Print Setting')],
                        ['id' => 'invoice',  'label' => __('Invoice Print Setting')],
                        ['id' => 'bill',     'label' => __('Bill Print Setting')],
                    ];
                @endphp
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    @foreach($tabs as $index => $tab)
                        <li class="nav-item" role="presentation">
                            <a
                                class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                id="pills-{{ $tab['id'] }}-tab"
                                data-bs-toggle="pill"
                                href="#pills-{{ $tab['id'] }}"
                                role="tab"
                                aria-controls="pills-{{ $tab['id'] }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                            >
                                {{ $tab['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <!--Proposal Print Setting-->
                    <div class="tab-pane fade show active" id="pills-proposal" role="tabpanel" aria-labelledby="pills-proposal-tab">

                        <div   class="bg-none">
                            <div class="row company-setting">
                                <div class="col-md-3">
                                    <div class="card-body">
                                        <h5></h5>
                                        <form id="setting-form" method="post" action="{{route(ViewsConstants::PPS_TMP . 'settings')}}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">
                                                <label for="address" class="col-form-label">{{__('Proposal Template')}}</label>
                                                <select class="form-control select2" name="proposal_template">
                                                    @foreach(App\Models\Utility::templateData()['templates'] as $key => $template)
                                                        <option value="{{$key}}" {{(isset($settings[BillsConstants::COL_PPS_TMP]) && $settings[BillsConstants::COL_PPS_TMP] == $key) ? 'selected' : ''}}>{{$template}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">{{__('Color Input')}}</label>
                                                <div class="row gutters-xs">
                                                    @foreach(App\Models\Utility::templateData()['colors'] as $key => $color)
                                                        <div class="col-auto">
                                                            <label class="colorinput">
                                                                <input name="proposal_color" type="radio" value="{{$color}}" class="colorinput-input" {{(isset($settings['proposal_color']) && $settings['proposal_color'] == $color) ? 'checked' : ''}}>
                                                                <span class="colorinput-color" style="background: #{{$color}}"></span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">{{__('Proposal Logo')}}</label>
                                                <div class="choose-files">
                                                    <label for="proposal_logo">
                                                        <div class=" bg-primary proposal_logo_update"> <i class="ti ti-upload px-1"></i>{{__('Choose file here')}}</div>
                                                        <input type="file" class="form-control file" name="proposal_logo" id="proposal_logo" data-filename="proposal_logo_update">
                                                        <img id="proposal_image" class="mt-2" style="width:25%;"/>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group mt-2 text-end">
                                                <input type="submit" value="{{__('Save')}}" class="{{ ViewClassNamesConstants::BT_PR_PRM10 }}">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    @if(isset($settings[BillsConstants::COL_PPS_TMP]) && isset($settings['proposal_color']))
                                        <iframe id="proposal_frame" class="w-100 h-100" frameborder="0" src="{{route(ViewsConstants::PPS.'.preview',[$settings[BillsConstants::COL_PPS_TMP],$settings['proposal_color']])}}"></iframe>
                                    @else
                                        <iframe id="proposal_frame" class="w-100 h-100" frameborder="0" src="{{route(ViewsConstants::PPS.'.preview',['template1','fffff'])}}"></iframe>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Invoice Setting-->
                    <div class="tab-pane fade" id="pills-invoice" role="tabpanel" aria-labelledby="pills-invoice-tab">
                        <div class="bg-none">
                            <div class="row company-setting">
                                <div class="col-md-3">
                                    <div class="card-body">
                                        <h5></h5>
                                        <form id="setting-form" method="post" action="{{route('template.setting')}}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">
                                                <label for="address" class="col-form-label">{{__('Invoice Template')}}</label>
                                                <select class="form-control select2" name="invoice_template">
                                                    @foreach(Utility::templateData()['templates'] as $key => $template)
                                                        <option value="{{$key}}" {{(isset($settings[BillsConstants::COL_INV_TMP]) && $settings[BillsConstants::COL_INV_TMP] == $key) ? 'selected' : ''}}>{{$template}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">{{__('Color Input')}}</label>
                                                <div class="row gutters-xs">
                                                    @foreach(Utility::templateData()['colors'] as $key => $color)
                                                        <div class="col-auto">
                                                            <label class="colorinput">
                                                                <input name="invoice_color" type="radio" value="{{$color}}" class="colorinput-input" {{(isset($settings['invoice_color']) && $settings['invoice_color'] == $color) ? 'checked' : ''}}>
                                                                <span class="colorinput-color" style="background: #{{$color}}"></span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">{{__('Invoice Logo')}}</label>
                                                <div class="choose-files">
                                                    <label for="invoice_logo">
                                                        <div class=" bg-primary invoice_logo_update"> <i class="ti ti-upload px-1"></i>{{__('Choose file here')}}</div>
                                                        <input type="file" class="form-control file" name="invoice_logo" id="invoice_logo" data-filename="invoice_logo_update">
                                                        <img id="invoice_image" class="mt-2" style="width:25%;"/>

                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group mt-2 text-end">
                                                <input type="submit" value="{{__('Save')}}" class="{{ ViewClassNamesConstants::BT_PR_PRM10 }}">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    @if(isset($settings[BillsConstants::COL_INV_TMP]) && isset($settings['invoice_color']))
                                        <iframe id="invoice_frame" class="w-100 h-100" frameborder="0" src="{{route(ViewsConstants::INV.'.preview',[$settings[BillsConstants::COL_INV_TMP],$settings['invoice_color']])}}"></iframe>
                                    @else
                                        <iframe id="invoice_frame" class="w-100 h-100" frameborder="0" src="{{route(ViewsConstants::INV.'.preview',['template1','fffff'])}}"></iframe>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Bill Setting-->
                    <div class="tab-pane fade" id="pills-bill" role="tabpanel" aria-labelledby="pills-bill-tab">
                        <div class="bg-none">
                            <div class="row company-setting">
                                <div class="col-md-3">
                                    <div class="card-body">
                                        <h5></h5>
                                        <form id="setting-form" method="post" action="{{route(ViewsConstants::BIL_TMP . 'settings')}}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">
                                                <label for="address" class="form-label">{{__('Bill Template')}}</label>
                                                <select class="form-control" name="bill_template">
                                                    @foreach(App\Models\Utility::templateData()['templates'] as $key => $template)
                                                        <option value="{{$key}}" {{(isset($settings[BillsConstants::COL_POS_TMP]) && $settings[BillsConstants::COL_POS_TMP] == $key) ? 'selected' : ''}}>{{$template}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">{{__('Color Input')}}</label>
                                                <div class="row gutters-xs">
                                                    @foreach(Utility::templateData()['colors'] as $key => $color)
                                                        <div class="col-auto">
                                                            <label class="colorinput">
                                                                <input name="bill_color" type="radio" value="{{$color}}" class="colorinput-input" {{(isset($settings['bill_color']) && $settings['bill_color'] == $color) ? 'checked' : ''}}>
                                                                <span class="colorinput-color" style="background: #{{$color}}"></span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">{{__('Bill Logo')}}</label>
                                                <div class="choose-files">
                                                    <label for="bill_logo">
                                                        <div class=" bg-primary bill_logo_update"> <i class="ti ti-upload px-1"></i>{{__('Choose file here')}}</div>
                                                        <input type="file" class="form-control file" name="bill_logo" id="bill_logo" data-filename="bill_logo_update">
                                                        <img id="bill_image" class="mt-2" style="width:25%;"/>

                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group mt-2 text-end">
                                                <input type="submit" value="{{__('Save')}}" class="{{ ViewClassNamesConstants::BT_PR_PRM10 }}">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    @if(isset($settings[BillsConstants::COL_POS_TMP]) && isset($settings['bill_color']))
                                        <iframe id="bill_frame" class="w-100 h-100" frameborder="0" src="{{route(ViewsConstants::BIL.'.preview',[$settings[BillsConstants::COL_POS_TMP],$settings['bill_color']])}}"></iframe>
                                    @else
                                        <iframe id="bill_frame" class="w-100 h-100" frameborder="0" src="{{route(ViewsConstants::BIL.'.preview',['template1','fffff'])}}"></iframe>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection
