@php
	use App\Config\Constants\{PermissionsConstants,ViewClassNamesConstants,ViewsConstants};
	use Illuminate\Support\Facades\{Log,Route};
	$navItems??=[];
	try {
		$navItems=[
			['route'=>ViewsConstants::BRC.'.index','pattern'=>ViewsConstants::BRC.'*','label'=>__('Branch')],
			['route'=>ViewsConstants::DPT.'.index','pattern'=>ViewsConstants::DPT.'*','label'=>__('Department')],
			['route'=>ViewsConstants::DSG.'.index','pattern'=>ViewsConstants::DSG.'*','label'=>__('Designation')],
			['route'=>ViewsConstants::LV_TP.'.index','pattern'=>ViewsConstants::LV_TP.'*','label'=>__('Leave Type')],
			['route'=>ViewsConstants::DOC.'.index','pattern'=>ViewsConstants::DOC.'*','label'=>__('Document Type')],
			['route'=>ViewsConstants::PY_SLP_TP.'.index','pattern'=>ViewsConstants::PY_SLP_TP.'*','label'=>__('Payslip Type')],
			['route'=>ViewsConstants::ALW_OPT.'.index','pattern'=>ViewsConstants::ALW_OPT.'*','label'=>__('Allowance Option')],
			['route'=>ViewsConstants::LN_OPT.'.index','pattern'=>ViewsConstants::LN_OPT.'*','label'=>__('Loan Option')],
			['route'=>ViewsConstants::DDT_OPT.'.index','pattern'=>ViewsConstants::DDT_OPT.'*','label'=>__('Deduction Option')],
			['route'=>ViewsConstants::GL_TP.'.index','pattern'=>ViewsConstants::GL_TP.'*','label'=>__('Goal Type')],
			['route'=>ViewsConstants::TNG_TP.'.index','pattern'=>ViewsConstants::TNG_TP.'*','label'=>__('Training Type')],
			['route'=>ViewsConstants::AWD_TP.'.index','pattern'=>ViewsConstants::AWD_TP.'*','label'=>__('Award Type')],
			['route'=>ViewsConstants::TMN_TP.'.index','pattern'=>ViewsConstants::TMN_TP.'*','label'=>__('Termination Type')],
			['route'=>ViewsConstants::JB_CAT.'.index','pattern'=>ViewsConstants::JB_CAT.'*','label'=>__('Job Category')],
			['route'=>ViewsConstants::JB_STG.'.index','pattern'=>ViewsConstants::JB_STG.'*','label'=>__('Job Stage')],
			['route'=>ViewsConstants::PFM_TP.'.index','pattern'=>ViewsConstants::PFM_TP.'*','label'=>__('Performance Type'),'can'=>PermissionsConstants::MNG_PRF_TP],
			['route'=>ViewsConstants::CPT.'.index','pattern'=>ViewsConstants::CPT.'*','label'=>__('Competencies')]
		];
	} catch (\Error $e) {
		Log::error(
			'Error fetching nav items',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching nav items',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching nav items',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	}
@endphp
<div class="{{ ViewClassNamesConstants::CD_STK }}" style="top:30px">
    <div class="{{ ViewClassNamesConstants::LG_FLSH }}" id="useradd-sidenav">
        @foreach($navItems as $item)
            @if(! isset($item['can']) || auth()->user()->can($item['can']))
                @php
                    $exists = Route::has($item['route']);
                @endphp
                <a
                    href="{{ $exists ? route($item['route']) : '#' }}"
                    class="{{ ViewClassNamesConstants::LGI_ACT_NBD }} {{ $exists && request()->is($item['pattern']) ? 'active' : '' }}"
                >
                    {{ $item['label'] }}
                    <div class="float-end">
                        <i class="{{ ViewClassNamesConstants::TI_CHV_RT }}"></i>
                    </div>
                </a>
            @endif
        @endforeach
    </div>
</div>
<script>
    console.log(
        'Current route:',
        '{{ Route::currentRouteName() ?? Route::currentRouteAction() }}'
    );
</script>
