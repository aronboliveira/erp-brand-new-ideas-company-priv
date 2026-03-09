@php
$navItems??=[];
	try {
		$navItems=[
			['route'=>VW::BRC.'.index','pattern'=>VW::BRC.'*','label'=>__('Branch')],
			['route'=>VW::DPT.'.index','pattern'=>VW::DPT.'*','label'=>__('Department')],
			['route'=>VW::DSG.'.index','pattern'=>VW::DSG.'*','label'=>__('Designation')],
			['route'=>VW::LV_TP.'.index','pattern'=>VW::LV_TP.'*','label'=>__('Leave Type')],
			['route'=>VW::DOC.'.index','pattern'=>VW::DOC.'*','label'=>__('Document Type')],
			['route'=>VW::PY_SLP_TP.'.index','pattern'=>VW::PY_SLP_TP.'*','label'=>__('Payslip Type')],
			['route'=>VW::ALW_OPT.'.index','pattern'=>VW::ALW_OPT.'*','label'=>__('Allowance Option')],
			['route'=>VW::LN_OPT.'.index','pattern'=>VW::LN_OPT.'*','label'=>__('Loan Option')],
			['route'=>VW::DDT_OPT.'.index','pattern'=>VW::DDT_OPT.'*','label'=>__('Deduction Option')],
			['route'=>VW::GL_TP.'.index','pattern'=>VW::GL_TP.'*','label'=>__('Goal Type')],
			['route'=>VW::TNG_TP.'.index','pattern'=>VW::TNG_TP.'*','label'=>__('Training Type')],
			['route'=>VW::AWD_TP.'.index','pattern'=>VW::AWD_TP.'*','label'=>__('Award Type')],
			['route'=>VW::TMN_TP.'.index','pattern'=>VW::TMN_TP.'*','label'=>__('Termination Type')],
			['route'=>VW::JB_CAT.'.index','pattern'=>VW::JB_CAT.'*','label'=>__('Job Category')],
			['route'=>VW::JB_STG.'.index','pattern'=>VW::JB_STG.'*','label'=>__('Job Stage')],
			['route'=>VW::PFM_TP.'.index','pattern'=>VW::PFM_TP.'*','label'=>__('Performance Type'),'can'=>PMC::MNG_PRF_TP],
			['route'=>VW::CPT.'.index','pattern'=>VW::CPT.'*','label'=>__('Competencies')]
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
<div class="{{ VC::CD_STK }}" style="top:30px">
    <div class="{{ VC::LG_FLSH }}" id="useradd-sidenav">
        @foreach($navItems as $item)
            @if(! isset($item['can']) || auth()->user()->can($item['can']))
                @php
                    $exists = Route::has($item['route']);
@endphp
                <a
                    href="{{ $exists ? route($item['route']) : '#' }}"
                    class="{{ VC::LGI_ACT_NBD }} {{ $exists && request()->is($item['pattern']) ? 'active' : '' }}"
                >
                    {{ $item['label'] }}
                    <div class="{{ VC::FEND }}">
                        <i class="{{ VC::TI_CHV_RT }}"></i>
                    </div>
                </a>
            @endif
        @endforeach
    </div>
</div>
<script>
   (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') && console.log(
        'Current route:',
        '{{ Route::currentRouteName() ?? Route::currentRouteAction() }}'
    );
</script>
