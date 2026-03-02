@php
	use App\Config\Constants\{ExtendingLayoutsConstants, StacksConstants as ST, ViewClassNamesConstants as VC, ViewsConstants as VW, YieldingConstants};
	use App\Models\{Utility};
	use Illuminate\Support\{Collection, Str};
	use Illuminate\Support\Facades\{Auth, Crypt, Log, Route, Storage};
	use InvalidArgumentException;
	use RuntimeException;
	use TypeError;
	$user ??= null;
	$lang ??= DatabaseConstants::DEFAULT_LANG;
	$employee ??= null;
	$employeesId ??= (string)'';
	$documents ??= [];
	try {
		$user = Auth::user();
	} catch (InvalidArgumentException $e) {
		Log::error('auth_user_fetch_failed', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (RuntimeException $e) {
		Log::error('auth_user_runtime_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (TypeError $e) {
		Log::error('auth_user_type_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Error $e) {
		Log::error('auth_user_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Exception $e) {
		Log::error('auth_user_exception', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Throwable $e) {
		Log::error('auth_user_throwable', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	}
	try {
		$lang = (string)(Utility::fetchUserLang(user:$user) ?? '');
	} catch (InvalidArgumentException $e) {
		Log::error('fetch_user_lang_invalid_argument', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (RuntimeException $e) {
		Log::error('fetch_user_lang_runtime_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (TypeError $e) {
		Log::error('fetch_user_lang_type_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Error $e) {
		Log::error('fetch_user_lang_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Exception $e) {
		Log::error('fetch_user_lang_exception', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Throwable $e) {
		Log::error('fetch_user_lang_throwable', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	}
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
    @php
        $empIndexBase   = VW::EMP.'.index';
        $empIndexKebab  = Str::kebab($empIndexBase);
        $empIndexName   = Route::has($empIndexBase) ? $empIndexBase : (Route::has($empIndexKebab) ? $empIndexKebab : null);
        $empIndexUrl    = $empIndexName ? route($empIndexName) : '#';
        $empIndexGuard  = Utility::fetchLinkMessage($lang, VW::EMP, 'index_employee_route_unavailable') ?? 'Employee index route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <li class="breadcrumb-item">
        <a id="bc-employee-index-link"
           href="{{ $empIndexUrl }}"
           data-url="{{ $empIndexUrl }}"
           data-guard-msg="{{ $empIndexGuard }}"
           data-sv-localized="true">
            {{ __('Employee') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{!empty($employeesId) ? $employeesId : __('Employees ids not found') }}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
	@if(!empty($employee))
		<div class="{{ VC::FEND }} {{ VC::MT3 }} m-2">
			@can('edit employee')
				@php
						$empIdStr        = (string) data_get($employee ?? null, 'id', '');
						$encId           = $empIdStr !== '' ? Crypt::encrypt($empIdStr) : null;
						$empEditBase     = VW::EMP.'.edit';
						$empEditKebab    = Str::kebab($empEditBase);
						$empEditName     = Route::has($empEditBase) ? $empEditBase : (Route::has($empEditKebab) ? $empEditKebab : null);
						$empEditUrl      = ($empEditName && $encId) ? route($empEditName, $encId) : '#';
						$empEditGuardMsg = Utility::fetchLinkMessage($lang, VW::EMP, 'edit_employee_route_unavailable') ?? 'Edit employee route is unavailable. Please contact technical support or your domain administrator.';
						$editLinkId      = 'employee-edit-btn-'.($empIdStr !== '' ? $empIdStr : 'x');
				@endphp
				<a
						id="{{ $editLinkId }}"
						href="{{ $empEditUrl }}"
						data-url="{{ $empEditUrl }}"
						data-guard-msg="{{ $empEditGuardMsg }}"
						data-sv-localized="true"
						data-bs-toggle="tooltip"
						title="{{ __('Edit') }}"
						class="{{ VC::BT_SM_PM }}"
						{{ $empEditUrl === '#' ? 'aria-disabled=true' : '' }}
				>
						<i class="{{ VC::TI_PC }}"></i>
				</a>
				@push(ST::ADM_SCR_PG)
						<script defer src="{{ asset('assets/js/routes/employees/edit.js') }}"></script>
				@endpush
			@endcan
		</div>
		<div class="text-end">
			@php
					$empIdStr    = (string) data_get($employee ?? null, 'id', '');
					$empIdOrNull = $empIdStr !== '' ? $empIdStr : null;
					$jlPdfBase   = 'joining_letter.download.pdf';
					$jlPdfKebab  = Str::kebab($jlPdfBase);
					$jlPdfName   = Route::has($jlPdfBase) ? $jlPdfBase : (Route::has($jlPdfKebab) ? $jlPdfKebab : null);
					$jlPdfUrl    = ($jlPdfName && $empIdOrNull) ? route($jlPdfName, [$empIdOrNull]) : '#';
					$jlPdfId     = 'joining-letter-download-pdf-btn-'.($empIdOrNull ?? 'x');
					$jlPdfMsg    = Utility::fetchLinkMessage($lang, VW::EMP, 'download_joining_letter_pdf_unavailable') ?? 'Download joining letter (PDF) route is unavailable. Please contact technical support or your domain administrator.';
					$jlDocBase   = 'joining_letter.download.doc';
					$jlDocKebab  = Str::kebab($jlDocBase);
					$jlDocName   = Route::has($jlDocBase) ? $jlDocBase : (Route::has($jlDocKebab) ? $jlDocKebab : null);
					$jlDocUrl    = ($jlDocName && $empIdOrNull) ? route($jlDocName, [$empIdOrNull]) : '#';
					$jlDocId     = 'joining-letter-download-doc-btn-'.($empIdOrNull ?? 'x');
					$jlDocMsg    = Utility::fetchLinkMessage($lang, VW::EMP, 'download_joining_letter_doc_unavailable') ?? 'Download joining letter (DOC) route is unavailable. Please contact technical support or your domain administrator.';
					$expPdfBase  = 'exp.download.pdf';
					$expPdfKebab = Str::kebab($expPdfBase);
					$expPdfName  = Route::has($expPdfBase) ? $expPdfBase : (Route::has($expPdfKebab) ? $expPdfKebab : null);
					$expPdfUrl   = ($expPdfName && $empIdOrNull) ? route($expPdfName, [$empIdOrNull]) : '#';
					$expPdfId    = 'experience-certificate-download-pdf-btn-'.($empIdOrNull ?? 'x');
					$expPdfMsg   = Utility::fetchLinkMessage($lang, VW::EMP, 'download_experience_certificate_pdf_unavailable') ?? 'Download experience certificate (PDF) route is unavailable. Please contact technical support or your domain administrator.';
					$expDocBase  = 'exp.download.doc';
					$expDocKebab = Str::kebab($expDocBase);
					$expDocName  = Route::has($expDocBase) ? $expDocBase : (Route::has($expDocKebab) ? $expDocKebab : null);
					$expDocUrl   = ($expDocName && $empIdOrNull) ? route($expDocName, [$empIdOrNull]) : '#';
					$expDocId    = 'experience-certificate-download-doc-btn-'.($empIdOrNull ?? 'x');
					$expDocMsg   = Utility::fetchLinkMessage($lang, VW::EMP, 'download_experience_certificate_doc_unavailable') ?? 'Download experience certificate (DOC) route is unavailable. Please contact technical support or your domain administrator.';
					$nocPdfBase  = 'noc.download.pdf';
					$nocPdfKebab = Str::kebab($nocPdfBase);
					$nocPdfName  = Route::has($nocPdfBase) ? $nocPdfBase : (Route::has($nocPdfKebab) ? $nocPdfKebab : null);
					$nocPdfUrl   = ($nocPdfName && $empIdOrNull) ? route($nocPdfName, [$empIdOrNull]) : '#';
					$nocPdfId    = 'noc-download-pdf-btn-'.($empIdOrNull ?? 'x');
					$nocPdfMsg   = Utility::fetchLinkMessage($lang, VW::EMP, 'download_noc_pdf_unavailable') ?? 'Download NOC (PDF) route is unavailable. Please contact technical support or your domain administrator.';
					$nocDocBase  = 'noc.download.doc';
					$nocDocKebab = Str::kebab($nocDocBase);
					$nocDocName  = Route::has($nocDocBase) ? $nocDocBase : (Route::has($nocDocKebab) ? $nocDocKebab : null);
					$nocDocUrl   = ($nocDocName && $empIdOrNull) ? route($nocDocName, [$empIdOrNull]) : '#';
					$nocDocId    = 'noc-download-doc-btn-'.($empIdOrNull ?? 'x');
					$nocDocMsg   = Utility::fetchLinkMessage($lang, VW::EMP, 'download_noc_doc_unavailable') ?? 'Download NOC (DOC) route is unavailable. Please contact technical support or your domain administrator.';
			@endphp
			<div class="{{ VC::DFL }} {{ VC::JCE }} drp-languages">
					<ul class="list-unstyled {{ VC::MB0 }} m-2">
							<li class="{{ VC::STT_DD_IT }}">
									<a class="{{ VC::DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
											<span class="drp-text hide-mob text-primary">{{ __('Joining Letter') }}</span>
											<i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
									</a>
									<div class="{{ VC::DRP_DSH_MN }}">
											<a id="{{ $jlPdfId }}" href="{{ $jlPdfUrl }}" data-url="{{ $jlPdfUrl }}" data-guard-msg="{{ $jlPdfMsg }}" data-sv-localized="true" class="btn-icon dropdown-item" data-bs-toggle="tooltip" data-bs-placement="top" target="_blank" {{ $jlPdfUrl === '#' ? 'aria-disabled=true' : '' }}><i class="{{ VC::TI_DWN }}">&nbsp;</i>{{ __('PDF') }}</a>
											<a id="{{ $jlDocId }}" href="{{ $jlDocUrl }}" data-url="{{ $jlDocUrl }}" data-guard-msg="{{ $jlDocMsg }}" data-sv-localized="true" class="btn-icon dropdown-item" data-bs-toggle="tooltip" data-bs-placement="top" target="_blank" {{ $jlDocUrl === '#' ? 'aria-disabled=true' : '' }}><i class="{{ VC::TI_DWN }}">&nbsp;</i>{{ __('DOC') }}</a>
									</div>
							</li>
					</ul>
					<ul class="list-unstyled {{ VC::MB0 }} m-2">
							<li class="{{ VC::STT_DD_IT }}">
									<a class="{{ VC::DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
											<span class="drp-text hide-mob text-primary">{{ __('Experience Certificate') }}</span>
											<i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
									</a>
									<div class="{{ VC::DRP_DSH_MN }}">
											<a id="{{ $expPdfId }}" href="{{ $expPdfUrl }}" data-url="{{ $expPdfUrl }}" data-guard-msg="{{ $expPdfMsg }}" data-sv-localized="true" class="btn-icon dropdown-item" data-bs-toggle="tooltip" data-bs-placement="top" target="_blank" {{ $expPdfUrl === '#' ? 'aria-disabled=true' : '' }}><i class="{{ VC::TI_DWN }}">&nbsp;</i>{{ __('PDF') }}</a>
											<a id="{{ $expDocId }}" href="{{ $expDocUrl }}" data-url="{{ $expDocUrl }}" data-guard-msg="{{ $expDocMsg }}" data-sv-localized="true" class="btn-icon dropdown-item" data-bs-toggle="tooltip" data-bs-placement="top" target="_blank" {{ $expDocUrl === '#' ? 'aria-disabled=true' : '' }}><i class="{{ VC::TI_DWN }}">&nbsp;</i>{{ __('DOC') }}</a>
									</div>
							</li>
					</ul>
					<ul class="list-unstyled {{ VC::MB0 }} m-2">
							<li class="{{ VC::STT_DD_IT }}">
									<a class="{{ VC::DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
											<span class="drp-text hide-mob text-primary">{{ __('NOC') }}</span>
											<i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
									</a>
									<div class="{{ VC::DRP_DSH_MN }}">
											<a id="{{ $nocPdfId }}" href="{{ $nocPdfUrl }}" data-url="{{ $nocPdfUrl }}" data-guard-msg="{{ $nocPdfMsg }}" data-sv-localized="true" class="btn-icon dropdown-item" data-bs-toggle="tooltip" data-bs-placement="top" target="_blank" {{ $nocPdfUrl === '#' ? 'aria-disabled=true' : '' }}><i class="{{ VC::TI_DWN }}">&nbsp;</i>{{ __('PDF') }}</a>
											<a id="{{ $nocDocId }}" href="{{ $nocDocUrl }}" data-url="{{ $nocDocUrl }}" data-guard-msg="{{ $nocDocMsg }}" data-sv-localized="true" class="btn-icon dropdown-item" data-bs-toggle="tooltip" data-bs-placement="top" target="_blank" {{ $nocDocUrl === '#' ? 'aria-disabled=true' : '' }}><i class="{{ VC::TI_DWN }}">&nbsp;</i>{{ __('DOC') }}</a>
									</div>
							</li>
					</ul>
			</div>
			@push(ST::ADM_SCR_PG)
					<script defer src="{{ asset('assets/js/routes/employees/templates/joinings/downloads/pdf.js') }}"></script>
					<script defer src="{{ asset('assets/js/routes/employees/templates/joinings/downloads/doc.js') }}"></script>
					<script defer src="{{ asset('assets/js/routes/employees/templates/experiences/downloads/pdf.js') }}"></script>
					<script defer src="{{ asset('assets/js/routes/employees/templates/experiences/downloads/doc.js') }}"></script>
					<script defer src="{{ asset('assets/js/routes/employees/templates/nocs/downloads/pdf.js') }}"></script>
					<script defer src="{{ asset('assets/js/routes/employees/templates/nocs/downloads/doc.js') }}"></script>
			@endpush
		</div>
    @else
        <div class="{{ VC::CD }}"><div class="card-body text-center">{{ __('No employee data available') }}</div></div>
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
											<span>{{ isset($employeesId) && $employeesId !== '' ? $employeesId : __('No employee ID available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }} font-style">
											<strong class="font-bold">{{ __('Name') }} :</strong>
											<span>{{ isset($employee->name) && $employee->name !== '' ? $employee->name : __('No name available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }} font-style">
											<strong class="font-bold">{{ __('Email') }} :</strong>
											<span>{{ isset($employee->email) && $employee->email !== '' ? $employee->email : __('No email available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }}">
											<strong class="font-bold">{{ __('Date of Birth') }} :</strong>
											<span>{{ !empty($employee->dob) ? (string)($user?->dateFormat($employee->dob) ?? __('No date of birth available')) : __('No date of birth available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }}">
											<strong class="font-bold">{{ __('Phone') }} :</strong>
											<span>{{ isset($employee->phone) && $employee->phone !== '' ? $employee->phone : __('No phone available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }}">
											<strong class="font-bold">{{ __('Address') }} :</strong>
											<span>{{ isset($employee->address) && $employee->address !== '' ? $employee->address : __('No address available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }}">
											<strong class="font-bold">{{ __('Salary Type') }} :</strong>
											<span>{{ data_get($employee,'salary_type.name') ?: __('No salary type available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }}">
											<strong class="font-bold">{{ __('Basic Salary') }} :</strong>
											<span>{{ isset($employee->salary) && $employee->salary !== '' ? (string)$employee->salary : __('No basic salary available') }}</span>
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
											<span>{{ data_get($employee,'branch.name') ?: __('No branch available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }} font-style">
											<strong class="font-bold">{{ __('Department') }} :</strong>
											<span>{{ data_get($employee,'department.name') ?: __('No department available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }}">
											<strong class="font-bold">{{ __('Designation') }} :</strong>
											<span>{{ data_get($employee,'designation.name') ?: __('No designation available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }}">
											<strong class="font-bold">{{ __('Date Of Joining') }} :</strong>
											<span>{{ !empty($employee->company_doj) ? (string)($user?->dateFormat($employee->company_doj) ?? __('No joining date available')) : __('No joining date available') }}</span>
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
										$employeedoc ??= [];
										try {
											$employeedoc = !empty($employee) && method_exists($employee,'documents') ? ($employee->documents()->pluck('document_value','document_id') ?? []) : [];
										} catch (InvalidArgumentException $e) {
											Log::error('employee_documents_invalid_argument', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
											$employeedoc = [];
										} catch (RuntimeException $e) {
											Log::error('employee_documents_runtime_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
											$employeedoc = [];
										} catch (TypeError $e) {
											Log::error('employee_documents_type_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
											$employeedoc = [];
										} catch (\Error $e) {
											Log::error('employee_documents_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
											$employeedoc = [];
										} catch (\Exception $e) {
											Log::error('employee_documents_exception', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
											$employeedoc = [];
										} catch (\Throwable $e) {
											Log::error('employee_documents_throwable', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
											$employeedoc = [];
										}
										$docs = (is_array($documents ?? null) || ($documents ?? null) instanceof Collection) ? $documents : [];
										$hasDocs = (is_array($docs) && count($docs) > 0) || ($docs instanceof Collection && $docs->isNotEmpty());
									@endphp
									@if($hasDocs)
										@foreach($docs as $key => $document)
											<div class="{{ VC::CM6 }}">
												<div class="info {{ VC::TXSM }}">
													<strong class="font-bold">{{ isset($document->name) && $document->name !== '' ? $document->name : __('No document name available') }} : </strong>
													<span>
														@php
															$docId = isset($document->id) ? $document->id : null;
															$fileName = ($docId !== null && isset($employeedoc[$docId]) && $employeedoc[$docId] !== '') ? $employeedoc[$docId] : null;
														@endphp
														<a href="{{ $fileName ? asset(Storage::url('uploads/document')).'/'.$fileName : '#' }}" target="_blank"{{ $fileName ? '' : ' aria-disabled=true' }}>
															{{ $fileName ? $fileName : __('No document available') }}
														</a>
													</span>
												</div>
											</div>
										@endforeach
									@else
										<div class="text-center">
											{{ __('No document types available') }}
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
											<span>{{ isset($employee->account_holder_name) && $employee->account_holder_name !== '' ? $employee->account_holder_name : __('No account holder name available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }} font-style">
											<strong class="font-bold">{{ __('Account Number') }} :</strong>
											<span>{{ isset($employee->account_number) && $employee->account_number !== '' ? $employee->account_number : __('No account number available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }}">
											<strong class="font-bold">{{ __('Bank Name') }} :</strong>
											<span>{{ isset($employee->bank_name) && $employee->bank_name !== '' ? $employee->bank_name : __('No bank name available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }}">
											<strong class="font-bold">{{ __('Bank Identifier Code') }} :</strong>
											<span>{{ isset($employee->bank_identifier_code) && $employee->bank_identifier_code !== '' ? $employee->bank_identifier_code : __('No bank identifier code available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }}">
											<strong class="font-bold">{{ __('Branch Location') }} :</strong>
											<span>{{ isset($employee->branch_location) && $employee->branch_location !== '' ? $employee->branch_location : __('No branch location available') }}</span>
										</div>
									</div>
									<div class="{{ VC::CM6 }}">
										<div class="info {{ VC::TXSM }}">
											<strong class="font-bold">{{ __('Tax Payer Id') }} :</strong>
											<span>{{ isset($employee->tax_payer_id) && $employee->tax_payer_id !== '' ? $employee->tax_payer_id : __('No taxpayer ID available') }}</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	@else
		<div class="{{ VC::CD }}"><div class="card-body text-center">{{ __('No employee data available') }}</div></div>
	@endif
@endsection


