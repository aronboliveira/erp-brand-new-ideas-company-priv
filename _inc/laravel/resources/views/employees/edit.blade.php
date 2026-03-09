@php
\tuse App\\Config\\Constants\\{ExtendingLayoutsConstants, StacksConstants, UsersConstants, ViewClassNamesConstants as VC, ViewsConstants as VW, YieldingConstants};\n\tuse App\\Models\\{Branch, Department, Designation, Employee, Utility};\n\tuse Collective\\Html\\FormFacade as Form;\n\tuse Illuminate\\Support\\Facades\\{Auth, Log, Route};\n\tuse Illuminate\\Support\\Str;\n\t$user ??= null;\n\t$lang ??= 'en';\n\ttry {\n\t\t$user = Auth::user();\n\t\t$lang = Utility::fetchUserLang(user: $user) ?? 'en';\n\t} catch (\\Error $e) {\n\t\tLog::error('Error in employees/edit.blade.php main @php block', [\n\t\t\t'exception_class' => get_class($e),\n\t\t\t'message' => $e->getMessage(),\n\t\t\t'file' => $e->getFile(),\n\t\t\t'line' => $e->getLine(),\n\t\t]);\n\t} catch (\\Exception $e) {\n\t\tLog::error('Exception in employees/edit.blade.php main @php block', [\n\t\t\t'exception_class' => get_class($e),\n\t\t\t'message' => $e->getMessage(),\n\t\t\t'file' => $e->getFile(),\n\t\t\t'line' => $e->getLine(),\n\t\t]);\n\t} catch (\\Throwable $e) {\n\t\tLog::error('Throwable in employees/edit.blade.php main @php block', [\n\t\t\t'exception_class' => get_class($e),\n\t\t\t'message' => $e->getMessage(),\n\t\t\t'file' => $e->getFile(),\n\t\t\t'line' => $e->getLine(),\n\t\t]);\n\t}\n
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Edit Employee')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
$empIndexBase ??= '';
		$empIndexKebab ??= '';
		$empIndexName ??= null;
		$empIndexUrl ??= '#';
		$empIndexGuard ??= '';
		try {
			$empIndexBase = VW::EMP . '.index';
			$empIndexKebab = Str::kebab($empIndexBase);
			$empIndexName = Route::has($empIndexBase) ? $empIndexBase : (Route::has($empIndexKebab) ? $empIndexKebab : null);
			$empIndexUrl = $empIndexName ? (route($empIndexName) ?? '#') : '#';
			$empIndexGuard = Utility::fetchLinkMessage($lang, VW::EMP, 'index_employee_route_unavailable') ?? 'Employee index route is unavailable. Please contact technical support or your domain administrator.';
		} catch (\Error $e) {
			BcLog::error('Error in employees/edit.blade.php breadcrumb @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\Exception $e) {
			BcLog::error('Exception in employees/edit.blade.php breadcrumb @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\Throwable $e) {
			BcLog::error('Throwable in employees/edit.blade.php breadcrumb @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}
@endphp
    <li class="{{ VC::BCI }}">
        <a id="bc-employee-index-link"
           href="{{ $empIndexUrl }}"
           data-url="{{ $empIndexUrl }}"
           data-guard-msg="{{ base64_encode($empIndexGuard) }}"
           data-sv-localized="true">
            {{ __('Employee') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{!empty($employeesId) ? $employeesId : (!empty($employee) && isset($employee) ? $employee->id : __('Employee id not found')) }}</li>
@endsection

@if(!empty($employee) && isset($employee))
    @section(YieldingConstants::ADM_CTT)
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                @php
$employeeId ??= '';
					$empUpdateBase ??= '';
					$empUpdateKebab ??= '';
					$empUpdateResolved ??= null;
					$empUpdateUrl ??= '#';
					$empUpdateFormId ??= 'employee-update-form-x';
					$empUpdateGuardMsg ??= '';
					try {
						$employeeId = (string) data_get($employee ?? null, 'id', '');
						$empUpdateBase = VW::EMP . '.update';
						$empUpdateKebab = Str::kebab($empUpdateBase);
						$empUpdateResolved = Route::has($empUpdateBase) ? $empUpdateBase : (Route::has($empUpdateKebab) ? $empUpdateKebab : null);
						$empUpdateUrl = ($empUpdateResolved && $employeeId !== '') ? (route($empUpdateResolved, [$employeeId]) ?? '#') : '#';
						$empUpdateFormId = 'employee-update-form-' . ($employeeId === '' ? 'x' : $employeeId);
						$empUpdateGuardMsg = Utility::fetchLinkMessage($lang, VW::EMP, 'update_employee_route_unavailable') ?? 'Update employee route is unavailable. Please contact technical support or your domain administrator.';
					} catch (\Error $e) {
						UpdateLog::error('Error in employees/edit.blade.php update @php block', [
							'exception_class' => get_class($e),
							'message' => $e->getMessage(),
							'file' => $e->getFile(),
							'line' => $e->getLine(),
						]);
					} catch (\Exception $e) {
						UpdateLog::error('Exception in employees/edit.blade.php update @php block', [
							'exception_class' => get_class($e),
							'message' => $e->getMessage(),
							'file' => $e->getFile(),
							'line' => $e->getLine(),
						]);
					} catch (\Throwable $e) {
						UpdateLog::error('Throwable in employees/edit.blade.php update @php block', [
							'exception_class' => get_class($e),
							'message' => $e->getMessage(),
							'file' => $e->getFile(),
							'line' => $e->getLine(),
						]);
					}
@endphp
                {{ Form::model($employee, [
                    'method'            => 'PUT',
                    'url'               => $empUpdateUrl,
                    'enctype'           => 'multipart/form-data',
                    'id'                => $empUpdateFormId,
                    'data-url'          => $empUpdateUrl,
                    'data-guard-msg'    => $empUpdateGuardMsg,
                    'data-sv-localized' => 'true',
                ]) }}
                    @csrf
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CM6 }}">
                            <div class="{{ VC::CD }} emp_details">
                                <div class="{{ VC::CD_HD }}"><h6 class="{{ VC::MB0 }}">{{ __('Personal Detail') }}</h6></div>
                                <div class="{{ VC::CD_BD }} employee-detail-edit-body">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::FM_GCB6 }}">
                                            {!! Form::label('name', __('Name'), ['class' => VC::FM_LB]) !!}<span class="{{ VC::TX_DNG_PL1 }}">*</span>
                                            {!! Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) !!}
                                        </div>
                                        <div class="{{ VC::FM_GCB6 }}">
                                            {!! Form::label('phone', __('Phone'), ['class' => VC::FM_LB]) !!}<span class="{{ VC::TX_DNG_PL1 }}">*</span>
                                            {!! Form::number('phone', null, ['class' => VC::FM_CT]) !!}
                                        </div>
                                        <div class="{{ VC::FM_GCB6 }}">
                                            {!! Form::label('dob', __('Date of Birth'), ['class' => VC::FM_LB]) !!}<span class="{{ VC::TX_DNG_PL1 }}">*</span>
                                            {!! Form::date('dob', null, ['class' => VC::FM_CT]) !!}
                                        </div>
                                        <div class="{{ VC::FM_GCB6 }}">
                                            {!! Form::label('gender', __('Gender'), ['class' => VC::FM_LB]) !!}<span class="{{ VC::TX_DNG_PL1 }}">*</span>
                                            <div class="{{ VC::DFL }} radio-check mt-2">
                                                <div class="{{ VC::FM_CHK_IL_GP }}">
                                                    <input type="radio" id="g_male" value="Male" name="gender" class="form-check-input" {{ ($employee->gender == 'Male') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="g_male">{{ __('Male') }}</label>
                                                </div>
                                                <div class="{{ VC::FM_CHK_IL_GP }}">
                                                    <input type="radio" id="g_female" value="Female" name="gender" class="form-check-input" {{ ($employee->gender == 'Female') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="g_female">{{ __('Female') }}</label>
                                                </div>
                                                <div class="{{ VC::FM_CHK_IL_GP }}">
                                                    <input type="radio" id="g_nb" value="Non-Binary" name="gender" class="form-check-input" {{ ($employee->gender == 'Non-Binary') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="g_nb">{{ __('Non-Binary') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="{{ VC::FM_G }}">
                                        {!! Form::label('address', __('Address'), ['class' => VC::FM_LB]) !!}<span class="{{ VC::TX_DNG_PL1 }}">*</span>
                                        {!! Form::textarea('address', null, ['class' => VC::FM_CT, 'rows' => 2]) !!}
                                    </div>

                                    @if(strtolower($user?->{UsersConstants::COL_TP}) === 'employee')
                                        {!! Form::submit('Update', ['class' => 'btn-create btn-xs badge-blue radius-10px ' . VC::FEND]) !!}
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(strtolower($user?->{UsersConstants::COL_TP}) !== 'employee')
                            <div class="{{ VC::CM6 }}">
                                <div class="{{ VC::CD }} emp_details">
                                    <div class="{{ VC::CD_HD }}"><h6 class="{{ VC::MB0 }}">{{ __('Company Detail') }}</h6></div>
                                    <div class="{{ VC::CD_BD }} employee-detail-edit-body">
                                        <div class="{{ VC::RW }}">
                                            @csrf
                                            <div class="{{ VC::FM_GCB12 }}">
                                                {!! Form::label('employee_id', __('Employee ID'), ['class' => VC::FM_LB]) !!}
                                                {!! Form::text('employee_id', !empty($employeesId) ? $employeesId : (!empty($employee) && isset($employee) ? $employee->id : __('No id given to employee yet. Please contact your system administrator or technical support.')), ['class' => VC::FM_CT, 'disabled' => 'disabled']) !!}
                                            </div>

                                            <div class="{{ VC::FM_GCB6 }}">
                                                {{ Form::label('branch_id', __('Branch'), ['class' => VC::FM_LB]) }}
                                                {{ Form::select('branch_id', Branch::pluck('name', 'id')->toArray(), null, ['class' => VC::FM_CT_SL, 'required' => 'required', 'id' => 'branch_id']) }}
                                            </div>

                                            <div class="{{ VC::FM_GCB6 }}">
                                                {{ Form::label('department_id', __('Department'), ['class' => VC::FM_LB]) }}
                                                {{ Form::select('department_id', Department::pluck('name', 'id')->toArray(), null, ['class' => VC::FM_CT_SL, 'required' => 'required', 'id' => 'department_id']) }}
                                            </div>

                                            <div class="{{ VC::FM_GCB6 }}">
                                                {{ Form::label('designation_id', __('Designation'), ['class' => VC::FM_LB]) }}
                                                {{ Form::select('designation_id', Designation::pluck('name', 'id')->toArray(), null, ['class' => VC::FM_CT_SL, 'required' => 'required', 'id' => 'designation_id']) }}
                                            </div>

                                            <div class="{{ VC::FM_GCB6 }}">
                                                {!! Form::label('company_doj', 'Company Date Of Joining', ['class' => VC::FM_LB]) !!}
                                                {!! Form::date('company_doj', null, ['class' => VC::FM_CT, 'required' => 'required']) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="{{ VC::CM6 }}">
                                <div class="employee-detail-wrap">
                                    <div class="{{ VC::CD }} emp_details">
                                        <div class="{{ VC::CD_HD }}"><h6 class="{{ VC::MB0 }}">{{ __('Company Detail') }}</h6></div>
                                        <div class="{{ VC::CD_BD }} employee-detail-edit-body">
                                            <div class="{{ VC::RW }}">
                                                <div class="{{ VC::CM6 }}">
                                                    <div class="info">
                                                        <strong>{{ __('Branch') }}</strong>
                                                        <span>{{ !empty($employee->branch?->name) ? $employee->branch->name : __('Failed to fetch branch name') }}</span>
                                                    </div>
                                                </div>
                                                <div class="{{ VC::CM6 }}">
                                                    <div class="info font-style">
                                                        <strong>{{ __('Department') }}</strong>
                                                        <span>{{ !empty($employee->department?->name) ? $employee->department->name : __('Failed to fetch department name') }}</span>
                                                    </div>
                                                </div>
                                                <div class="{{ VC::CM6 }}">
                                                    <div class="info font-style">
                                                        <strong>{{ __('Designation') }}</strong>
                                                        <span>{{ !empty($employee->designation?->name) ? $employee->designation->name : __('Failed to fetch designation name') }}</span>
                                                    </div>
                                                </div>
                                                <div class="{{ VC::CM6 }}">
                                                    <div class="info">
                                                        <strong>{{ __('Date Of Joining') }}</strong>
                                                        <span>{{ method_exists($user, 'dateFormat') ? (!empty($employee->company_doj) ? $user->dateFormat($employee->company_doj) : __('Failed to format date.')) : __('Failed to get Date of Joining') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @if(strtolower($user?->{UsersConstants::COL_TP}) !== 'employee')
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CM6 }}">
                                <div class="{{ VC::CD }} emp_details">
                                    <div class="{{ VC::CD_HD }}"><h6 class="{{ VC::MB0 }}">{{ __('Document') }}</h6></div>
                                    <div class="{{ VC::CD_BD }} employee-detail-edit-body">
                                        @php
                                            $docsRaw = method_exists($employee, 'documents') ? $employee->documents() : [];
                                            $employeedoc = $docsRaw->isNotEmpty() ? $docsRaw->pluck('document_value', 'document_id') : [];
@endphp
                                        @if (Utility::isFilled($documents) ?? [])
                                            @foreach($documents as $key => $document)
                                                <div class="{{ VC::RW }}">
                                                    <div class="{{ VC::FM_GCB12 }}">
                                                        <div class="float-left col-4">
                                                            <label for="document" class="float-left pt-1 {{ VC::FM_LB }}">{{ $document->name }} @if($document->is_required == 1) <span class="{{ VC::TX_DNG }}">*</span> @endif</label>
                                                        </div>
                                                        <div class="float-right col-4">
                                                            <input type="hidden" name="emp_doc_id[{{ $document->id }}]" value="{{ $document->id }}">
                                                            <div class="choose-file {{ VC::FM_G }}">
                                                                <label for="document[{{ $document->id }}]">
                                                                    <input
                                                                        class="{{ VC::FM_CT }} @if(!empty($employeedoc[$document->id])) float-left @endif @error('document') is-invalid @enderror border-0"
                                                                        @if($document->is_required == 1 && empty($employeedoc[$document->id])) required @endif
                                                                        name="document[{{ $document->id }}]"
                                                                        onchange="document.getElementById('{{ 'blah'.$key }}').src = window.URL.createObjectURL(this.files[0])"
                                                                        type="file"
                                                                        data-filename="{{ $document->id . '_filename' }}">
                                                                </label>
                                                                <p class="{{ $document->id . '_filename' }}"></p>

                                                                @php
                                                                    $logo = Utility::getFile('uploads/document/');
@endphp
                                                                <img id="{{ 'blah'.$key }}" src="{{ (isset($employeedoc[$document->id]) && !empty($employeedoc[$document->id]) ? $logo.'/'.$employeedoc[$document->id] : '') }}" width="25%" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="{{ VC::TXCT }}">
                                                {{ __('No documents available.') }}
                                            </div>
                                        @endif
                                    </>
                                </div>
                            </div>

                            <div class="{{ VC::CM6 }}">
                                <div class="{{ VC::CD }} emp_details">
                                    <div class="{{ VC::CD_HD }}"><h6 class="{{ VC::MB0 }}">{{ __('Bank Account Detail') }}</h6></div>
                                    <div class="{{ VC::CD_BD }} employee-detail-edit-body">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::FM_GCB6 }}">
                                                {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => VC::FM_LB]) !!}
                                                {!! Form::text('account_holder_name', null, ['class' => VC::FM_CT]) !!}
                                            </div>
                                            <div class="{{ VC::FM_GCB6 }}">
                                                {!! Form::label('account_number', __('Account Number'), ['class' => VC::FM_LB]) !!}
                                                {!! Form::number('account_number', null, ['class' => VC::FM_CT]) !!}
                                            </div>
                                            <div class="{{ VC::FM_GCB6 }}">
                                                {!! Form::label('bank_name', __('Bank Name'), ['class' => VC::FM_LB]) !!}
                                                {!! Form::text('bank_name', null, ['class' => VC::FM_CT]) !!}
                                            </div>
                                            <div class="{{ VC::FM_GCB6 }}">
                                                {!! Form::label('bank_identifier_code', __('Bank Identifier Code'), ['class' => VC::FM_LB]) !!}
                                                {!! Form::text('bank_identifier_code', null, ['class' => VC::FM_CT]) !!}
                                            </div>
                                            <div class="{{ VC::FM_GCB6 }}">
                                                {!! Form::label('branch_location', __('Branch Location'), ['class' => VC::FM_LB]) !!}
                                                {!! Form::text('branch_location', null, ['class' => VC::FM_CT]) !!}
                                            </div>
                                            <div class="{{ VC::FM_GCB6 }}">
                                                {!! Form::label('tax_payer_id', __('Tax Payer Id'), ['class' => VC::FM_LB]) !!}
                                                {!! Form::text('tax_payer_id', null, ['class' => VC::FM_CT]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CM6 }}">
                                <div class="employee-detail-wrap">
                                    <div class="{{ VC::CD }} emp_details">
                                        <div class="{{ VC::CD_HD }}"><h6 class="{{ VC::MB0 }}">{{ __('Document Detail') }}</h6></div>
                                        <div class="{{ VC::CD_BD }} employee-detail-edit-body">
                                            <div class="{{ VC::RW }}">
                                                @php
                                                    $employeedoc = $employee->documents()->pluck('document_value', __('document_id'));
@endphp
                                                @foreach($documents as $key => $document)
                                                    <div class="{{ VC::CM12 }}">
                                                        <div class="info">
                                                            <strong>{{ $document->name }}</strong>
                                                            <span>
                                                                <a href="{{ (!empty($employeedoc[$document->id]) ? asset(Storage::url('uploads/document')).'/'.$employeedoc[$document->id] : '') }}" target="_blank">
                                                                    {{ (!empty($employeedoc[$document->id]) ? $employeedoc[$document->id] : '') }}
                                                                </a>
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="{{ VC::CM6 }}">
                                <div class="employee-detail-wrap">
                                    <div class="{{ VC::CD }} emp_details">
                                        <div class="{{ VC::CD_HD }}"><h6 class="{{ VC::MB0 }}">{{ __('Bank Account Detail') }}</h6></div>
                                        <div class="{{ VC::CD_BD }} employee-detail-edit-body">
                                            <div class="{{ VC::RW }}">
                                                <div class="{{ VC::CM6 }}">
                                                    <div class="info">
                                                        <strong>{{ __('Account Holder Name') }}</strong>
                                                        <span>{{ $employee->account_holder_name }}</span>
                                                    </div>
                                                </div>
                                                <div class="{{ VC::CM6 }}">
                                                    <div class="info font-style">
                                                        <strong>{{ __('Account Number') }}</strong>
                                                        <span>{{ $employee->account_number }}</span>
                                                    </div>
                                                </div>
                                                <div class="{{ VC::CM6 }}">
                                                    <div class="info font-style">
                                                        <strong>{{ __('Bank Name') }}</strong>
                                                        <span>{{ $employee->bank_name }}</span>
                                                    </div>
                                                </div>
                                                <div class="{{ VC::CM6 }}">
                                                    <div class="info">
                                                        <strong>{{ __('Bank Identifier Code') }}</strong>
                                                        <span>{{ $employee->bank_identifier_code }}</span>
                                                    </div>
                                                </div>
                                                <div class="{{ VC::CM6 }}">
                                                    <div class="info">
                                                        <strong>{{ __('Branch Location') }}</strong>
                                                        <span>{{ $employee->branch_location }}</span>
                                                    </div>
                                                </div>
                                                <div class="{{ VC::CM6 }}">
                                                    <div class="info">
                                                        <strong>{{ __('Tax Payer Id') }}</strong>
                                                        <span>{{ $employee->tax_payer_id }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(strtolower($user?->{UsersConstants::COL_TP}) !== 'employee')
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::C12 }}">
                                <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }} {{ VC::FEND }}">
                            </div>
                        </div>
                    @endif
                {!! Form::close() !!}
            </div>
        </div>
    @endsection
    @push(StacksConstants::ADM_SCR_PG)
        <script async src="{{ asset('assets/js/routes/employees/lang/edit.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/employees/update.js') }}"></script>
        <script defer>
            (() => {
                const errFb = '# ERROR';
                const dataClientLocalized = 'data-client-localized';
                const dataGuardMsg = 'data-guard-msg';
                const langKey = 'erp-np-lang';
                const csrfToken = '{{ csrf_token() }}';

                const getMsg = (key, el) => {
                    let msg = errFb;
                    if (el.getAttribute('data-sv-localized') === 'true'
                    || el.getAttribute(dataClientLocalized) === 'true') {
                        msg = el.getAttribute(dataGuardMsg) || errFb;
                    } else {
                        let lang = (sessionStorage.getItem(langKey)
                        || document.documentElement.lang
                        || 'en')
                        .toLowerCase()
                        .replace(/_/g, '-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                    msg = window.translations?.[lang]?.[key]
                        || el.getAttribute(dataGuardMsg)
                        || window.translations?.en?.[key]
                        || errFb;
                    if (msg !== errFb) {
                        el.setAttribute(dataGuardMsg, msg);
                        el.setAttribute(dataClientLocalized, 'true');
                    }
                    }
                    return msg;
                };

                const showToast = message => (window.RouteGuard?.showToast || (m => alert(m)))(message);

                let queuedError = '';
                const flushError = () => {
                    if (queuedError) {
                    showToast(queuedError);
                    queuedError = '';
                    }
                };
                document.addEventListener('click', flushError);
                new MutationObserver((records, obs) => {
                    for (const r of records) {
                        for (const n of r.removedNodes) {
                        if (n === document.documentElement) {
                        document.removeEventListener('click', flushError);
                        obs.disconnect();
                        }
                    }
                    }
                }).observe(document.body, { childList: true, subtree: true });

                document.querySelectorAll('input[type="file"][data-filename]')
                    .forEach(input => {
                    if (input.dataset.listenerAttached === 'true') return;
                    input.dataset.listenerAttached = 'true';

                    const onChange = e => {
                        try {
                            const name = e.target.files?.[0]?.name;
                            if (!name) return;
                        const target = document.querySelector('.' + input.dataset.filename);
                        if (target && !target.textContent.includes(name)) {
                            target.textContent += name;
                        }
                        } catch {
                            queuedError = getMsg('file_name_append_failed', input);
                        }
                    };
                    input.addEventListener('change', onChange);

                    new MutationObserver((recs, obs) => {
                        for (const r of recs) {
                        for (const n of r.removedNodes) {
                            if (n === input) {
                                input.removeEventListener('change', onChange);
                            obs.disconnect();
                            }
                        }
                        }
                    }).observe(document.body, { childList: true, subtree: true });
                    });

                const getDepartment = branchId => {
                    try {
                    $.ajax({
                        url: '{{ route(VW::EMP.".getdepartment") }}',
                        type: 'POST',
                        data: { branch_id: branchId ?? '', _token: csrfToken },
                        success: data => {
                        try {
                            const sel = document.getElementById('department_id');
                            if (!sel) return;
                            sel.innerHTML = '<option value="" disabled>{{ __("Select any Department") }}</option>';
                            for (const [k, v] of Object.entries(data || {})) {
                                sel.insertAdjacentHTML('beforeend', `<option value="${k}">${v}</option>`);
                            }
                            sel.value = '';
                        } catch {
                            queuedError = getMsg('department_fetch_failed', document.getElementById('branch_id'));
                        }
                        },
                        error: () => {
                        queuedError = getMsg('department_fetch_failed', document.getElementById('branch_id'));
                        }
                    });
                    } catch {
                    queuedError = getMsg('department_fetch_failed', document.getElementById('branch_id'));
                    }
                };

                document.addEventListener('change', e => {
                    if (e.target && e.target.matches('#branch_id')) {
                    getDepartment(e.target.value);
                    }
                });

                const getDesignation = deptId => {
                    try {
                    $.ajax({
                        url: '{{ route(VW::EMP.".json") }}',
                        type: 'POST',
                        data: { department_id: deptId ?? '', _token: csrfToken },
                        success: data => {
                        try {
                            const wrap = document.querySelector('.designation_div');
                            if (!wrap) return;
                            wrap.innerHTML = `
                            <select class="{{ VC::FM_CT }} designation_id" name="designation_id" id="choices-designation">
                                <option value="">{{ __("Select any Designation") }}</option>
                            </select>`;
                            for (const [k, v] of Object.entries(data || {})) {
                            document.getElementById('choices-designation')
                                    .insertAdjacentHTML('beforeend',
                                        `<option value="${k}"${k==='{{ $employee->designation_id }}'?' selected':''}>${v}</option>`);
                            }
                            new Choices('#choices-designation', { removeItemButton: true });
                        } catch {
                            queuedError = getMsg('designation_fetch_failed', document.getElementById('department_id'));
                        }
                        },
                        error: () => {
                        queuedError = getMsg('designation_fetch_failed', document.getElementById('department_id'));
                        }
                    });
                    } catch {
                    queuedError = getMsg('designation_fetch_failed', document.getElementById('department_id'));
                    }
                };

                document.addEventListener('DOMContentLoaded', () => {
                    const dep = document.getElementById('department_id');
                    if (dep) getDesignation(dep.value);
                });

                document.addEventListener('change', e => {
                    if (e.target && e.target.matches('select[name=department_id]')) {
                        getDesignation(e.target.value);
                    }
                });
            })();
        </script>
    @endpush
@else
    <div class="{{ VC::ALT_DNG }} {{ VC::TXCT }}">
        {{ __('Employee record not found.') }}
    </div>
@endif

                    {{--                                            <img id="{{'blah'.$key}}" src=""  width="25%" />--}}

                    {{--                                        @if(!empty($employeedoc[$document->id]))--}}
                    {{--                                            <br> <span class="{{ VC::TXS }}"><a href="{{ (!empty($employeedoc[$document->id])?asset(Storage::url('uploads/document')).'/'.$employeedoc[$document->id]:'') }}" target="_blank">{{ (!empty($employeedoc[$document->id])?$employeedoc[$document->id]:'') }}</a>--}}
                    {{--                                                    </span>--}}
                    {{--                                        @endif--}}
