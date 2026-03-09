@php
    try {
$lang               = Utility::fetchUserLang();
        $importRouteName    = ViewsConstants::CST . '.import';
        $importUrl          = Route::has($importRouteName)
            ? route($importRouteName)
            : '#';
        $formId             = 'customer-csv-import-form';
        $guardMsg           = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CST,
            'customers_import_route_unavailable'
        ) ?? 'Customer CSV import route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('customers/import — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'            => $importUrl,
    'method'         => 'post',
    'enctype'        => 'multipart/form-data',
    'id'             => $formId,
    'data-url'       => $importUrl,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }} mb-6">
                {{ Form::label('file', __('Download sample customer CSV file'), ['class' => VC::FM_LB]) }}
                <a href="{{ asset(Storage::url('uploads/sample/sample-customer.csv')) }}" class="{{ VC::BT_SM_PM }}">
                    <i class="{{ VC::TI_DWN }}"></i> {{ __('Download') }}
                </a>
            </div>

            <div class="{{ VC::C12 }}">
                {{ Form::label('file', __('Select CSV File'), ['class' => VC::FM_LB]) }}
                <div class="choose-file {{ VC::FM_G }}">
                    <label for="file" class="{{ VC::FM_LB }}">
                        <input
                            type="file"
                            class="{{ VC::FM_CT }}"
                            name="file"
                            id="file"
                            data-filename="upload_file"
                            required
                        >
                    </label>
                    <p class="upload_file"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input
            type="button"
            value="{{ __('Cancel') }}"
            class="{{ VC::BT_LG }}"
            data-bs-dismiss="modal"
        >
        <input
            type="submit"
            value="{{ __('Upload') }}"
            class="{{ VC::BT_PRM }}"
        >
    </div>
    <script defer src="{{ asset('assets/js/routes/customers/import.js') }}"></script>
{{ Form::close() }}
