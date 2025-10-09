@php
	use App\Config\Constants\{ViewClassNamesConstants,ViewsConstants};
	use Illuminate\Support\Facades\{Log,Route};
	$navItems??=[];
	try {
		$navItems=[
			['route'=>ViewsConstants::TX.'.index','label'=>__('Taxes')],
			['route'=>ViewsConstants::PRD_SV_CAT.'.index','label'=>__('Category')],
			['route'=>ViewsConstants::PRD_SV_UNT.'.index','label'=>__('Unit')],
			['route'=>ViewsConstants::CST_FD.'.index','label'=>__('Custom Field')],
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
        @php
            $routeName??='';
            $exists??=false;
            $url??='';
            $isActive??=false;
            try {
                $routeName=$item['route']??'';
                $exists=$routeName&&Route::has($routeName);
                $url=$exists?route($routeName):'#';
                $isActive=$exists&&request()->routeIs($routeName);
            } catch (\Error $e) {
                Log::error(
                    'Error fetching data for nav route',
                    [
                        'exception_class'=>get_class($e),
                        'message'=>$e->getMessage(),
                        'file'=>$e->getFile(),
                        'line'=>$e->getLine()
                    ]
                );
            } catch (\Exception $e) {
                Log::error(
                    'Exception fetching data for nav route',
                    [
                        'exception_class'=>get_class($e),
                        'message'=>$e->getMessage(),
                        'file'=>$e->getFile(),
                        'line'=>$e->getLine()
                    ]
                );
            } catch (\Throwable $e) {
                Log::error(
                    'Throwable fetching data for nav route',
                    [
                        'exception_class'=>get_class($e),
                        'message'=>$e->getMessage(),
                        'file'=>$e->getFile(),
                        'line'=>$e->getLine()
                    ]
                );
            }
        @endphp
            <a href="{{ $url }}"
            class="list-group-item list-group-item-action border-0 {{ $isActive ? 'active' : '' }}">
                {{ $item['label'] }}
                <div class="float-end">
                    <i class="{{ ViewClassNamesConstants::TI_CHV_RT }}"></i>
                </div>
            </a>
        @endforeach
    </div>
</div>
<script>
    (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') && console.log(
        'Current route:',
        '{{ Route::currentRouteName() ?? Route::currentRouteAction() }}'
    );
</script>
