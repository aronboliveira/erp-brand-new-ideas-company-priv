@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};

    $lang = Utility::fetchUserLang();

    $formId     = 'ovt-store-form';
    $storeBase  = VW::OVT;
    $storeKebab = Str::kebab($storeBase);
    $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeRes ? route($storeRes) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, VW::OVT, 'overtime_store_route_unavailable') ?? __('Overtime store route is unavailable. Please contact technical support or your domain administrator.');

    $employeeId = (string) data_get($employee ?? null, 'id', '');

    $titleErr = $errors->has('title');
    $titleAttrs = [
        'id'               => 'title',
        'class'            => trim(VC::FM_CT . ' ' . ($titleErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $titleErr ? 'true' : 'false',
        'aria-describedby' => $titleErr ? 'title-error' : null,
        'autocomplete'     => 'off',
    ];

    $daysErr = $errors->has('number_of_days');
    $daysAttrs = [
        'id'               => 'number_of_days',
        'class'            => trim(VC::FM_CT . ' ' . ($daysErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $daysErr ? 'true' : 'false',
        'aria-describedby' => $daysErr ? 'number_of_days-error' : null,
        'step'             => '0.01',
        'min'              => '0',
        'inputmode'        => 'decimal',
    ];

    $hoursErr = $errors->has('hours');
    $hoursAttrs = [
        'id'               => 'hours',
        'class'            => trim(VC::FM_CT . ' ' . ($hoursErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $hoursErr ? 'true' : 'false',
        'aria-describedby' => $hoursErr ? 'hours-error' : null,
        'step'             => '0.01',
        'min'              => '0',
        'inputmode'        => 'decimal',
    ];

    $rateErr = $errors->has('rate');
    $rateAttrs = [
        'id'               => 'rate',
        'class'            => trim(VC::FM_CT . ' ' . ($rateErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $rateErr ? 'true' : 'false',
        'aria-describedby' => $rateErr ? 'rate-error' : null,
        'step'             => '0.01',
        'min'              => '0',
        'inputmode'        => 'decimal',
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
        {{ Form::hidden('employee_id', $employeeId, ['id' => 'employee_id']) }}

        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('title', __('Overtime Title'), ['class' => VC::FM_LB]) }} <span class="text-danger">*</span>
                {{ Form::text('title', null, $titleAttrs) }}
                @error('title')
                    <span id="title-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('number_of_days', __('Number of days'), ['class' => VC::FM_LB]) }}
                {{ Form::number('number_of_days', null, $daysAttrs) }}
                @error('number_of_days')
                    <span id="number_of_days-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('hours', __('Hours'), ['class' => VC::FM_LB]) }}
                {{ Form::number('hours', null, $hoursAttrs) }}
                @error('hours')
                    <span id="hours-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('rate', __('Rate'), ['class' => VC::FM_LB]) }}
                {{ Form::number('rate', null, $rateAttrs) }}
                @error('rate')
                    <span id="rate-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/overtimes/store.js') }}"></script>
{{ Form::close() }}
