@php
$navItems??=[];
	try {
		$navItems=[
			['view'=>ViewsConstants::PPL,'label'=>__('Pipeline')],
			['view'=>ViewsConstants::LD_STG,'label'=>__('Lead Stages')],
			['view'=>ViewsConstants::STG,'label'=>__('Deal Stages')],
			['view'=>ViewsConstants::SRC,'label'=>__('Sources')],
			['view'=>ViewsConstants::LBL,'label'=>__('Labels')],
			['view'=>ViewsConstants::CTC_TP,'label'=>__('Contract Type')]
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

<div class="{{ ViewClassNamesConstants::CD_STK }}" style="top: 30px">
  <div class="{{ ViewClassNamesConstants::LG_FLSH }}" id="useradd-sidenav">
    @foreach($navItems as $item)
    @php
      $routeName??='';
      try {
        $routeName=$item['view'].'.index';
      } catch (\Error $e) {
        Log::error(
          'Error setting route name',
          [
            'exception_class'=>get_class($e),
            'message'=>$e->getMessage(),
            'file'=>$e->getFile(),
            'line'=>$e->getLine()
          ]
        );
      } catch (\Exception $e) {
        Log::error(
          'Exception setting route name',
          [
            'exception_class'=>get_class($e),
            'message'=>$e->getMessage(),
            'file'=>$e->getFile(),
            'line'=>$e->getLine()
          ]
        );
      } catch (\Throwable $e) {
        Log::error(
          'Throwable setting route name',
          [
            'exception_class'=>get_class($e),
            'message'=>$e->getMessage(),
            'file'=>$e->getFile(),
            'line'=>$e->getLine()
          ]
        );
      }
@endphp
      <a
        href="{{ Route::has($routeName) ? route($routeName) : '#' }}"
        class="{{ ViewClassNamesConstants::LGI_ACT_NBD }} {{ Route::has($routeName) && request()->routeIs($routeName) ? 'active' : '' }}"
      >
        {{ $item['label'] }}
        <div class="{{ VC::FEND }}">
          <i class="{{ ViewClassNamesConstants::TI_CHV_RT }}"></i>
        </div>
      </a>
    @endforeach
  </div>
</div>

<script>
  (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') && console.log(
    'Current route:',
    '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Route::currentRouteAction() }}'
  );
</script>
