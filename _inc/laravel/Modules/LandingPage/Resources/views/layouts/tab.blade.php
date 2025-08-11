@php
    use App\Config\Constants\ViewClassNamesConstants;
    use Illuminate\Support\Facades\Route;
    use Modules\LandingPage\Config\Constants\RoutesResourcesConstants as R;
    $menuItems = [
        [R::LP,      __('Top Bar')],
        [R::CT_PG,   __('Custom Page')],
        [R::HM,      __('Home')],
        [R::FT,      __('Features')],
        [R::DV,      __('Discover')],
        [R::SST,     __('Screenshots')],
        [R::PRC_PLN, __('Pricing Plan')],
        [R::FQ,      __('FAQ')],
        [R::TTMN,    __('Testimonials')],
        [R::JU,      __('Join Us')],
    ];
@endphp
<div class="list-group">
    @foreach($menuItems as [$prefix, $label])
        @php
            $routeName = "{$prefix}.index";
            $exists    = Route::has($routeName);
            $url       = $exists ? route($routeName) : '#';
            $isActive  = $exists && Route::currentRouteNamed($routeName);
        @endphp
        <a href="{{ $url }}"
           class="{{ ViewClassNamesConstants::LGI_ACT_NBD }}{{ $isActive ? ' active' : '' }}"
           {{ $exists ? '' : 'aria-disabled="true"' }}>
            {{ $label }}
            <div class="float-end">
                <i class="{{ ViewClassNamesConstants::TI_CHV_RT }}"></i>
            </div>
        </a>
    @endforeach
</div>
<script>
    console.log(
        'Current route:',
        '{{ Route::currentRouteName() ?? Route::currentRouteAction() }}'
    );
</script>
