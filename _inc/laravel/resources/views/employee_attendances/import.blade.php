@php
    use App\Config\Constants\ExtendingLayoutsConstants as EL;
    use App\Config\Constants\ViewsConstants as VW;
    use App\Models\Utility;
@endphp

@extends(EL::ADM)

@section('page-title')
    {{ __('Import Employee Attendance') }}
@endsection

@section('content')
<div class="row">
    <div class="{{ VC::C12 }}">
        <div class="card">
            <div class="{{ VC::CD_HD }}">
                <h5>{{ __('Import Employee Attendance') }}</h5>
            </div>
            <div class="{{ VC::CD_BD }}">
                <p class="{{ VC::TXT_MT }}">{{ __('Upload a CSV or Excel file to bulk-import employee attendance records.') }}</p>
                <form method="POST" action="{{ Route::has(VW::EMP_ATD . '.import.store') ? route(VW::EMP_ATD . '.import.store') : '#' }}" enctype="multipart/form-data">
                    @csrf
                    <div class="{{ VC::FM_GB3 }}">
                        <label for="file" class="{{ VC::FM_LB }}">{{ __('Select File') }}</label>
                        <input type="file" name="file" id="file" class="{{ VC::FM_CT }}" accept=".csv,.xlsx,.xls" required>
                    </div>
                    <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Import') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
