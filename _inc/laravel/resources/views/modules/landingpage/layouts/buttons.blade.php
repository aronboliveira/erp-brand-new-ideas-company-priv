@php
	$lpSettings=[];
	$menubarStatus='';
	$rawPages='';
	$decoded=null;
	$pages=[];
	$hasDbPages=false;
	try {
		$lpSettings=\Modules\LandingPage\Entities\LandingPageSetting::settings()?:[];
	} catch (\Throwable $e) {
		Log::error('Failed fetching landing page settings', ['error' => $e->getMessage()]);
		$lpSettings=[];
	}
	$menubarStatus=$lpSettings[LPSC::MB_STT_K]??'off';
	$rawPages=$lpSettings[LPSC::MB_PG_K]??'[]';
	try {
		$decoded=json_decode($rawPages);
		$pages=(is_array($decoded)||is_object($decoded))?$decoded:[];
		$hasDbPages=count((array)$pages)>0;
	} catch (\Throwable $e) {
		$pages=[];
	}
@endphp
{{-- DB-driven menubar pages --}}
@if ($menubarStatus === 'on' && $hasDbPages)
    @foreach ($pages as $page)
        @php
            $loginRequired=false;
            $template='';
            $name='';
            try {
                $loginRequired=data_get($page,'login')==='on';
                $template=data_get($page,'template_name')?:'';
                $name=data_get($page,LPSC::MB_PG_NM,'')?:'';
            } catch (\Throwable $e) {}
@endphp
        @if ($loginRequired && $template === 'page_content')
            <li class="nav-item">
                <a class="nav-link"
                href="{{ (Route::has('custom.page') && data_get($page, LPSC::PG_SLG))
                        ? route('custom.page', data_get($page, LPSC::PG_SLG))
                        : '#' }}">
                    {{ __($name) }}
                </a>
            </li>
        @elseif ($loginRequired && $template === 'page_url')
            <li class="nav-item">
                <a class="nav-link" target="_blank"
                   href="{{ data_get($page, 'page_url', '#') }}">
                    {{ __($name) }}
                </a>
            </li>
        @endif
    @endforeach
@endif
{{-- Fallback: default nav links when DB has no menubar pages --}}
@if (!$hasDbPages)
    @php
        $defaultNavLinks = [
            ['slug' => 'about_us',             'label' => __('About Us')],
            ['slug' => 'privacy_policy',       'label' => __('Privacy Policy')],
            ['slug' => 'terms_and_conditions', 'label' => __('Terms & Conditions')],
        ];
    @endphp
    @foreach ($defaultNavLinks as $navLink)
        <li class="nav-item">
            <a class="nav-link"
               href="{{ Route::has('custom.page') ? route('custom.page', $navLink['slug']) : url($navLink['slug']) }}">
                {{ $navLink['label'] }}
            </a>
        </li>
    @endforeach
@endif
