@php
    try {
        $settings = Utility::settings();
    } catch (\Throwable $e) {
        \Log::error('partials/admin/footer — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<footer class="dash-footer">
    <div class="footer-wrapper">
        <div class="py-1 {{ VC::DFL }} flex-wrap {{ VC::ALC }} {{ VC::JCB }}">
            <p class="{{ VC::MB0 }} {{ VC::TXT_MT }}"> &copy;
                {{ date('Y') }} {{ $settings[SC::FT_TXT] ? $settings[SC::FT_TXT] : config('app.name', 'ERPNovaBrand New Ideas Company') }}
            </p>
            <nav class="{{ VC::MB0 }}">
                <a href="{{ route('terms_and_conditions') }}" class="{{ VC::TXT_MT }} small {{ VC::ME3 }}">{{ __('Terms and Conditions') }}</a>
                <a href="{{ route('privacy_policy') }}" class="{{ VC::TXT_MT }} small {{ VC::ME3 }}">{{ __('Privacy Policy') }}</a>
                <a href="{{ route('about_us') }}" class="{{ VC::TXT_MT }} small">{{ __('About Us') }}</a>
            </nav>
        </div>
    </div>
</footer>
<!-- Required Js -->
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/dash.js') }}"></script>
<script defer src="{{ asset('js/jquery.form.js') }}"></script>
<script defer src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
<script defer src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script defer src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
<script defer src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script defer src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
<script defer src="{{ asset('js/moment.min.js') }}"></script>
<script defer src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>
<script defer src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script defer src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>
<!-- Apex Chart -->
<script defer src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
<script defer src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
<script defer src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script defer src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>
<script defer src="{{ asset('js/jscolor.js') }}"></script>
<script defer src="{{ asset('js/popper.min.js') }}"></script>
{{--<script src="{{ asset ('js/bootstrap.min.js') }}"></script>--}}
<script>
    var site_currency_symbol_position = '{{ $settings[SC::CR_SB_P] }}';
    var site_currency_symbol = '{{ $settings[SC::CR_SB] }}';
</script>
<script src="{{ asset('js/custom.js') }}"></script>
@if($message = Session::get('success'))
    <script>
        show_toastr('success', {!! json_encode($message) !!});
    </script>
@endif
@if($message = Session::get('error'))
    <script>
        show_toastr('error', {!! json_encode($message) !!});
    </script>
@endif
@if($settings['enable_cookie'] == 'on')
    @includeIf(ExtendingLayoutsConstants::CKC)
@endif
@stack('script-page')
@stack('old-datatable-js')
<script defer src="{{ asset('assets/js/routes/partials/admin/footer.js') }}"></script>
