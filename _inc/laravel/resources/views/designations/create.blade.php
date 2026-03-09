@php
    try {
$lang = Utility::fetchUserLang();
        $departmentsIsList  = (is_array($departments ?? null) && count($departments ?? [])) || (($departments ?? null) instanceof Collection && $departments->isNotEmpty());
        $departmentsOptions = $departmentsIsList ? $departments : ['' => __('No departments available')];
        $designationStoreBase  = VW::DSG;
        $designationStoreKebab = Str::kebab($designationStoreBase);
        $designationStoreName  = Route::has($designationStoreBase) ? $designationStoreBase : (Route::has($designationStoreKebab) ? $designationStoreKebab : null);
        $designationStoreUrl   = $designationStoreName ? route($designationStoreName) : '#';
        $designationFormId     = 'designation-store-form';
        $designationGuardMsg   = Utility::fetchLinkMessage($lang, VW::DSG, 'designation_store_route_unavailable') ?? 'Store designation route is unavailable. Please contact technical support or your domain administrator.';

        $deptHasError = $errors->has('department_id');
        $deptAttrs    = [
            'id'               => 'department_id',
            'class'            => trim(VC::FM_CT_SL.' '.($deptHasError ? 'is-invalid' : '')),
            'placeholder'      => __('Select Department'),
            'required'         => 'required',
            'aria-invalid'     => $deptHasError ? 'true' : 'false',
            'aria-describedby' => $deptHasError ? 'department_id-error' : null,
        ];
        if (!$departmentsIsList) { $deptAttrs['disabled'] = 'disabled'; }

        $nameHasError = $errors->has('name');
        $nameAttrs    = [
            'id'               => 'name',
            'class'            => trim(VC::FM_CT.' '.($nameHasError ? 'is-invalid' : '')),
            'placeholder'      => __('Enter Designation Name'),
            'required'         => 'required',
            'aria-invalid'     => $nameHasError ? 'true' : 'false',
            'aria-describedby' => $nameHasError ? 'name-error' : null,
            'autocomplete'     => 'off',
        ];
    } catch (\Throwable $e) {
        \Log::error('designations/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'            => $designationStoreUrl,
    'method'         => 'POST',
    'id'             => $designationFormId,
    'data-url'       => $designationStoreUrl,
    'data-guard-msg' => $designationGuardMsg,
    'data-sv-localized' => 'true'
]) }}
    @csrf
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('department_id', __('Department'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('department_id', $departmentsOptions, null, $deptAttrs) }}
                    @error('department_id')
                        <span id="department_id-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                    @enderror
                </div>
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('name', null, $nameAttrs) }}
                    @error('name')
                        <span id="name-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/designations/store.js') }}"></script>
{{ Form::close() }}
