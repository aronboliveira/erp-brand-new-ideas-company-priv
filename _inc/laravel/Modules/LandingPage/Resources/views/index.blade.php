@php


	$currentFragment??='';
	try {
		$currentFragment=parse_url(request()->getRequestUri()?:'',PHP_URL_FRAGMENT)??'';
	} catch (\Error $e) {
		Log::error(
			'Error parsing current URL fragment',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'route'=>request()?->getRequestUri()??'Undefined URI'
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception parsing current URL fragment',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'route'=>request()?->getRequestUri()??'Undefined URI'
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable parsing current URL fragment',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'route'=>request()?->getRequestUri()??'Undefined URI'
			]
		);
	}
@endphp
@foreach($sections as $id => $label)
    <a href="#{{ $id }}"
       class="{{ ViewClassNamesConstants::LGI_ACT_NBD }}
              {{ $currentFragment === $id ? ' active' : '' }}">
        {{ $label }}
        <div class="float-end"><i class="{{ ViewClassNamesConstants::TI_CHV_RT }}"></i></div>
    </a>
@endforeach
