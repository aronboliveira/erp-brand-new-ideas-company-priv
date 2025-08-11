@php
	use App\Config\Constants\ViewClassNamesConstants;
	use Illuminate\Support\Facades\{Log, Route};
	use Modules\LandingPage\Config\Constants\RoutesResourcesConstants as R;
	$landingItems??=[];
	$landingArea??=[];
	try {
		$landingItems=[
			R::LP      =>__('Top Bar'),
			R::CT_PG   =>__('Custom Page'),
			R::HM      =>__('Home'),
			R::FT      =>__('Features'),
			R::DV      =>__('Discover'),
			R::SST     =>__('Screenshots'),
			R::PRC_PLN =>__('Pricing Plan'),
			R::FQ      =>__('FAQ'),
			R::TTMN    =>__('Testimonials'),
			R::JU      =>__('Join Us'),
		];
		$landingArea=array_map(
			fn($key)=>$key.'.index',
			array_keys($landingItems)
		);
	} catch (\Error $e) {
		Log::error(
			'Error computing landing items',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'landingItems'=>$landingItems,
				'landingArea'=>$landingArea
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception computing landing items',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'landingItems'=>$landingItems,
				'landingArea'=>$landingArea
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable computing landing items',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'landingItems'=>$landingItems,
				'landingArea'=>$landingArea
			]
		);
	}
@endphp
<li class="dash-item dash-hasmenu{{ request()->routeIs(...$landingArea) ? ' active' : '' }}">
    <a href="javascript:void(0)" class="dash-link">
        <span class="dash-micon"><i class="ti ti-license"></i></span>
        <span class="dash-mtext">{{ __('Landing Page') }}</span>
        <span class="dash-arrow"><i class="{{ ViewClassNamesConstants::TI_CHV_RT }}"></i></span>
    </a>
    <div class="dash-submenu">
        @foreach($landingItems as $prefix => $label)
            @php $routeName = $prefix . '.index'; @endphp
            <a href="{{ Route::has($routeName) ? route($routeName) : '#' }}"
               class="dash-link{{ Route::has($routeName) && request()->routeIs($routeName) ? ' active' : '' }}"
               {{ Route::has($routeName) ? '' : 'aria-disabled="true"' }}>
                {{ $label }}
            </a>
        @endforeach
    </div>
</li>
