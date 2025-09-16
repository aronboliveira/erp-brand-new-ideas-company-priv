@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC, PlansConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route};
    use Illuminate\Support\{Collection, Str};

    $lang = Utility::fetchUserLang();

    $formId     = 'mt-store-form';
    $storeBase  = VW::MT;
    $storeKebab = Str::kebab($storeBase);
    $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeRes ? route($storeRes) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, VW::MT, 'store_route_unavailable') ?? __('Meeting store route is unavailable. Please contact technical support or your domain administrator.');
    $plan       = Utility::getChatGPTSettings();
    $aiEnabled  = (int) data_get($plan, PlansConstants::COL_GPT, 0) === 1;
    $aiCtxId    = (string) (auth()->user()?->creatorId() ?? auth()->id() ?? '0');
    $genBase    = 'generate';
    $genKebab   = Str::kebab($genBase);
    $genRes     = Route::has($genBase) ? $genBase : (Route::has($genKebab) ? $genKebab : null);
    $genUrl     = $genRes ? route($genRes, [$aiCtxId]) : '#';
    $genGuard   = Utility::fetchLinkMessage($lang, VW::MT, 'generate_route_unavailable') ?? __('Generate content route for meetings is unavailable. Please contact technical support or your domain administrator.');
    $branchesList  = $branch ?? [];
    $branchesIsList = (is_array($branchesList) && count($branchesList) > 0) || ($branchesList instanceof Collection && $branchesList->isNotEmpty());
    $branchOptions = ['' => __('Select Branch'), '0' => __('All Branch')];
    if ($branchesIsList) {
        if ($branchesList instanceof Collection) {
            $branchOptions += $branchesList->mapWithKeys(fn($b) => [data_get($b, 'id', '') => (string) data_get($b, 'name', __('Unnamed'))])->toArray();
        } elseif (isset($branchesList[0]) && is_object($branchesList[0] ?? null)) {
            foreach ($branchesList as $b) { $branchOptions[data_get($b, 'id', '')] = (string) data_get($b, 'name', __('Unnamed')); }
        } elseif (is_array($branchesList)) {
            $branchOptions += $branchesList;
        }
    }
    $branchErr = $errors->has('branch_id');
    $branchAttrs = [
        'id'               => 'branch_id',
        'class'            => trim(VC::FM_CT_SL . ' select ' . ($branchErr ? 'is-invalid' : '')),
        'placeholder'      => __('Select Branch'),
        'required'         => 'required',
        'aria-invalid'     => $branchErr ? 'true' : 'false',
        'aria-describedby' => $branchErr ? 'branch_id-error' : null,
    ];

    $deptErr = $errors->has('department_id');
    $deptAttrs = [
        'id'               => 'department_id',
        'class'            => VC::FM_CT_SL . ' select',
        'placeholder'      => __('Select Department'),
        'multiple'         => 'multiple',
        'aria-invalid'     => $deptErr ? 'true' : 'false',
        'aria-describedby' => $deptErr ? 'department_id-error' : null,
    ];

    $empErr = $errors->has('employee_id');
    $empAttrs = [
        'id'               => 'employee_id',
        'class'            => VC::FM_CT_SL . ' select',
        'placeholder'      => __('Select Employee'),
        'multiple'         => 'multiple',
        'aria-invalid'     => $empErr ? 'true' : 'false',
        'aria-describedby' => $empErr ? 'employee_id-error' : null,
    ];

    $titleErr = $errors->has('title');
    $titleAttrs = [
        'id'               => 'title',
        'class'            => trim(VC::FM_CT . ' ' . ($titleErr ? 'is-invalid' : '')),
        'placeholder'      => __('Enter Meeting Title'),
        'required'         => 'required',
        'aria-invalid'     => $titleErr ? 'true' : 'false',
        'aria-describedby' => $titleErr ? 'title-error' : null,
        'autocomplete'     => 'off',
    ];

    $dateErr = $errors->has('date');
    $dateAttrs = [
        'id'               => 'date',
        'class'            => trim(VC::FM_CT . ' ' . ($dateErr ? 'is-invalid' : '')),
        'aria-invalid'     => $dateErr ? 'true' : 'false',
        'aria-describedby' => $dateErr ? 'date-error' : null,
    ];

    $timeErr = $errors->has('time');
    $timeAttrs = [
        'id'               => 'time',
        'class'            => trim(VC::FM_CT . ' timepicker ' . ($timeErr ? 'is-invalid' : '')),
        'aria-invalid'     => $timeErr ? 'true' : 'false',
        'aria-describedby' => $timeErr ? 'time-error' : null,
    ];

    $noteErr = $errors->has('note');
    $noteAttrs = [
        'id'               => 'note',
        'class'            => trim(VC::FM_CT . ' ' . ($noteErr ? 'is-invalid' : '')),
        'placeholder'      => __('Enter Meeting Note'),
        'rows'             => 3,
        'aria-invalid'     => $noteErr ? 'true' : 'false',
        'aria-describedby' => $noteErr ? 'note-error' : null,
    ];
@endphp

{{ Form::open([
    'url'               => $storeUrl,
    'method'            => 'POST',
    'id'                => $formId,
    'data-url'          => $storeUrl,
    'data-guard-msg'    => $storeGuard,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        @if($aiEnabled)
            <div class="text-end">
                <a href="{{ $genUrl }}"
                   class="ai-btn btn btn-primary btn-icon btn-sm"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-url="{{ $genUrl }}"
                   data-guard-msg="{{ $genGuard }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('branch_id', __('Branch'), ['class' => VC::FM_LB]) }}
                {{ Form::select('branch_id', $branchOptions, null, $branchAttrs) }}
                @error('branch_id')
                    <span id="branch_id-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB12 }}" id="department_div">
                {{ Form::label('department_id', __('Department'), ['class' => VC::FM_LB]) }}
                {{ Form::select('department_id[]', [], null, $deptAttrs) }}
                @error('department_id')
                    <span id="department_id-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB12 }}" id="employee_div">
                {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                {{ Form::select('employee_id[]', [], null, $empAttrs) }}
                @error('employee_id')
                    <span id="employee_id-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('title', __('Meeting Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, $titleAttrs) }}
                @error('title')
                    <span id="title-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('date', __('Meeting Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('date', null, $dateAttrs) }}
                @error('date')
                    <span id="date-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('time', __('Meeting Time'), ['class' => VC::FM_LB]) }}
                {{ Form::time('time', null, $timeAttrs) }}
                @error('time')
                    <span id="time-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('note', __('Meeting Note'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('note', null, $noteAttrs) }}
                @error('note')
                    <span id="note-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            @if (!empty($settings) && isset($settings['google_calendar_enable']) && $settings['google_calendar_enable'] == 'on')
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('synchronize_type', __('Synchronize in Google Calendar ?'), ['class' => VC::FM_LB]) }}
                    <div class="form-switch">
                        <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calendar">
                        <label class="form-check-label" for="switch-shadow"></label>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/meetings/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/meetings/generateStore.js') }}"></script>
{{ Form::close() }}
