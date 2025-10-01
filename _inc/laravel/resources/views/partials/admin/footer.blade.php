@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        SettingsConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Log, Session};
    Log::debug('Loading admin footer data...');
    $settings = Utility::settings();
    Log::debug('Loading admin footer template...');
@endphp
<footer class="dash-footer">
    <div class="footer-wrapper">
        <div class="py-1">
            <p class="mb-0 text-muted"> &copy;
                {{ date('Y') }} {{ $settings[SettingsConstants::FT_TXT] ? $settings[SettingsConstants::FT_TXT] : config('app.name', 'ERPNovaPrestech') }}
            </p>
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
    var site_currency_symbol_position = '{{ $settings['site_currency_symbol_position'] }}';
    var site_currency_symbol = '{{ $settings['site_currency_symbol'] }}';
</script>
<script src="{{ asset('js/custom.js') }}"></script>
@if($message = Session::get('success'))
    <script>
        show_toastr('success', '{!! $message !!}');
    </script>
@endif
@if($message = Session::get('error'))
    <script>
        show_toastr('error', '{!! $message !!}');
    </script>
@endif
@if($settings['enable_cookie'] == 'on')
    @includeIf(ExtendingLayoutsConstants::CKC)
@endif
@stack('script-page')
@stack('old-datatable-js')
<script defer src="{{ asset('assets/js/routes/partials/admin/footer.js') }}"></script>
