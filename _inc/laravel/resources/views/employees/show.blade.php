@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Crypt,Route,Storage};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Employee')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route(ViewsConstants::EMP.'.index')}}">{{__('Employee')}}</a></li>
    <li class="breadcrumb-item">{{$employeesId}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @if(!empty($employee))
        <div class="{{ VC::FEND }} {{ VC::MT3 }} m-2">
            @can('edit employee')
                <a href="{{ route('employee.edit', Crypt::encrypt($employee->id)) }}"
                   data-bs-toggle="tooltip"
                   title="{{ __('Edit') }}"
                   class="{{ VC::BT_SM_PM }}">
                    <i class="{{ VC::TI_PC }}"></i>
                </a>
            @endcan
        </div>

        <div class="text-end">
            <div class="{{ VC::DFL }} {{ VC::JCE }} drp-languages">
                <ul class="list-unstyled {{ VC::MB0 }} m-2">
                    <li class="{{ VC::STT_DD_IT }}">
                        <a class="{{ VC::DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#"
                           role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="drp-text hide-mob text-primary">{{ __('Joining Letter') }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                        </a>
                        <div class="{{ VC::DRP_DSH_MN }}">
                            <a href="{{ route('joining_letter.download.pdf', $employee->id) }}"
                               class="btn-icon dropdown-item"
                               data-bs-toggle="tooltip" data-bs-placement="top"
                               target="_blank"><i class="{{ VC::TI_DWN }}">&nbsp;</i>{{ __('PDF') }}</a>
                            <a href="{{ route('joining_letter.download.doc', $employee->id) }}"
                               class="btn-icon dropdown-item"
                               data-bs-toggle="tooltip" data-bs-placement="top"
                               target="_blank"><i class="{{ VC::TI_DWN }}">&nbsp;</i>{{ __('DOC') }}</a>
                        </div>
                    </li>
                </ul>

                <ul class="list-unstyled {{ VC::MB0 }} m-2">
                    <li class="{{ VC::STT_DD_IT }}">
                        <a class="{{ VC::DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#"
                           role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="drp-text hide-mob text-primary">{{ __('Experience Certificate') }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                        </a>
                        <div class="{{ VC::DRP_DSH_MN }}">
                            <a href="{{ route('exp.download.pdf', $employee->id) }}"
                               class="btn-icon dropdown-item"
                               data-bs-toggle="tooltip" data-bs-placement="top"
                               target="_blank"><i class="{{ VC::TI_DWN }}">&nbsp;</i>{{ __('PDF') }}</a>
                            <a href="{{ route('exp.download.doc', $employee->id) }}"
                               class="btn-icon dropdown-item"
                               data-bs-toggle="tooltip" data-bs-placement="top"
                               target="_blank"><i class="{{ VC::TI_DWN }}">&nbsp;</i>{{ __('DOC') }}</a>
                        </div>
                    </li>
                </ul>

                <ul class="list-unstyled {{ VC::MB0 }} m-2">
                    <li class="{{ VC::STT_DD_IT }}">
                        <a class="{{ VC::DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#"
                           role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="drp-text hide-mob text-primary">{{ __('NOC') }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                        </a>
                        <div class="{{ VC::DRP_DSH_MN }}">
                            <a href="{{ route('noc.download.pdf', $employee->id) }}"
                               class="btn-icon dropdown-item"
                               data-bs-toggle="tooltip" data-bs-placement="top"
                               target="_blank"><i class="{{ VC::TI_DWN }}">&nbsp;</i>{{ __('PDF') }}</a>
                            <a href="{{ route('noc.download.doc', $employee->id) }}"
                               class="btn-icon dropdown-item"
                               data-bs-toggle="tooltip" data-bs-placement="top"
                               target="_blank"><i class="{{ VC::TI_DWN }}">&nbsp;</i>{{ __('DOC') }}</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    @endif
@endsection

@section(YieldingConstants::ADM_CTT)
    @if(!empty($employee))
        <div class="{{ VC::RW }}">
            <div class="col-xl-12">
                <div class="{{ VC::RW }}">
                    <div class="{{ VC::CS12 }} {{ VC::CM6 }}">
                        <div class="{{ VC::CD }}">
                            <div class="card-body employee-detail-body fulls-card">
                                <h5>{{ __('Personal Detail') }}</h5>
                                <hr>
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('EmployeeId') }} : </strong>
                                            <span>{{ $employeesId }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }} font-style">
                                            <strong class="font-bold">{{ __('Name') }} :</strong>
                                            <span>{{ !empty($employee) ? $employee->name : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }} font-style">
                                            <strong class="font-bold">{{ __('Email') }} :</strong>
                                            <span>{{ !empty($employee) ? $employee->email : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Date of Birth') }} :</strong>
                                            <span>{{ $user?->dateFormat(!empty($employee) ? $employee->dob : '') }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Phone') }} :</strong>
                                            <span>{{ !empty($employee) ? $employee->phone : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Address') }} :</strong>
                                            <span>{{ !empty($employee) ? $employee->address : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Salary Type') }} :</strong>
                                            <span>{{ !empty($employee->salary_type) ? $employee->salary_type->name : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Basic Salary') }} :</strong>
                                            <span>{{ !empty($employee) ? $employee->salary : '' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="{{ VC::CS12 }} {{ VC::CM6 }}">
                        <div class="{{ VC::CD }}">
                            <div class="card-body employee-detail-body fulls-card">
                                <h5>{{ __('Company Detail') }}</h5>
                                <hr>
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Branch') }} : </strong>
                                            <span>{{ !empty($employee->branch) ? $employee->branch->name : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }} font-style">
                                            <strong class="font-bold">{{ __('Department') }} :</strong>
                                            <span>{{ !empty($employee->department) ? $employee->department->name : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Designation') }} :</strong>
                                            <span>{{ !empty($employee->designation) ? $employee->designation->name : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Date Of Joining') }} :</strong>
                                            <span>{{ $user?->dateFormat(!empty($employee) ? $employee->company_doj : '') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="{{ VC::RW }}">
                    <div class="{{ VC::CS12 }} {{ VC::CM6 }}">
                        <div class="{{ VC::CD }}">
                            <div class="card-body employee-detail-body fulls-card">
                                <h5>{{ __('Document Detail') }}</h5>
                                <hr>
                                <div class="{{ VC::RW }}">
                                    @php
                                        $employeedoc = !empty($employee) ? $employee->documents()->pluck('document_value', __('document_id')) : [];
                                    @endphp
                                    @if(!$documents->isEmpty())
                                        @foreach($documents as $key => $document)
                                            <div class="{{ VC::CM6 }}">
                                                <div class="info {{ VC::TXSM }}">
                                                    <strong class="font-bold">{{ $document->name }} : </strong>
                                                    <span>
                                                        <a href="{{ (!empty($employeedoc[$document->id]) ? asset(Storage::url('uploads/document')).'/'.$employeedoc[$document->id] : '') }}" target="_blank">
                                                            {{ (!empty($employeedoc[$document->id]) ? $employeedoc[$document->id] : '') }}
                                                        </a>
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center">
                                            {{ __('No Document Type Added.!') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="{{ VC::CS12 }} {{ VC::CM6 }}">
                        <div class="{{ VC::CD }}">
                            <div class="card-body employee-detail-body fulls-card">
                                <h5>{{ __('Bank Account Detail') }}</h5>
                                <hr>
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Account Holder Name') }} : </strong>
                                            <span>{{ !empty($employee) ? $employee->account_holder_name : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }} font-style">
                                            <strong class="font-bold">{{ __('Account Number') }} :</strong>
                                            <span>{{ !empty($employee) ? $employee->account_number : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Bank Name') }} :</strong>
                                            <span>{{ !empty($employee) ? $employee->bank_name : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Bank Identifier Code') }} :</strong>
                                            <span>{{ !empty($employee) ? $employee->bank_identifier_code : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Branch Location') }} :</strong>
                                            <span>{{ !empty($employee) ? $employee->branch_location : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="info {{ VC::TXSM }}">
                                            <strong class="font-bold">{{ __('Tax Payer Id') }} :</strong>
                                            <span>{{ !empty($employee) ? $employee->tax_payer_id : '' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

