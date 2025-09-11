@php
    $hasEmployee = isset($employee) && !empty($employee);

    $empName    = !empty($employee->name)    ? $employee->name    : __('Name not provided');
    $empEmail   = !empty($employee->email)   ? $employee->email   : __('Email not provided');
    $empPhone   = !empty($employee->phone)   ? $employee->phone   : __('Phone not provided');
    $empAddress = !empty($employee->address) ? $employee->address : __('Address not provided');
@endphp

@if($hasEmployee)
    <div class="row">
        <div class="col-md-5">
            <h6>{{ __('Employee Details') }}</h6>
            <div class="bill-to">
                <small>
                    <span>{{ $empName }}</span><br>
                    <span>{{ $empEmail }}</span><br>
                    <span>{{ $empPhone }}</span><br>
                    <span>{{ $empAddress }}</span><br>
                </small>
            </div>
        </div>

        <div class="col-md-2">
            <a href="#" id="remove" class="text-sm">{{ __(' Remove') }}</a>
        </div>
    </div>
@else
    <div class="row">
        <div class="col-12 text-muted text-center">{{ __('Employee details not available.') }}</div>
    </div>
@endif
