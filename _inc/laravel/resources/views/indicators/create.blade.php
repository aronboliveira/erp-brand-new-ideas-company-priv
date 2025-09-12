@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Illuminate\Support\Collection;

    $lang = Utility::fetchUserLang();

    $formId     = 'ind-store-form';
    $storeBase  = VW::IND . '.store';
    $storeKebab = Str::kebab($storeBase);
    $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeRes ? route($storeRes) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, VW::IND, 'store_route_unavailable') ?? __('Indicators store route is unavailable. Please contact technical support or your domain administrator.');

    $branchesIsList = (is_array($brances ?? null) && count($brances ?? []) > 0) || (($brances ?? null) instanceof Collection && $brances->isNotEmpty());
    $branchOptions  = $branchesIsList ? (is_array($brances) ? $brances : $brances->toArray()) : ['' => __('No branches available')];
    $branchErr      = $errors->has('branch');
    $branchAttrs    = [
        'id'               => 'branch',
        'class'            => trim(VC::FM_CT_SL . ' ' . ($branchErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $branchErr ? 'true' : 'false',
        'aria-describedby' => $branchErr ? 'branch-error' : null,
    ];
    if (!$branchesIsList) { $branchAttrs['disabled'] = 'disabled'; }

    $deptsIsList = (is_array($departments ?? null) && count($departments ?? []) > 0) || (($departments ?? null) instanceof Collection && $departments->isNotEmpty());
    $deptOptions = $deptsIsList ? (is_array($departments) ? $departments : $departments->toArray()) : ['' => __('No departments available')];
    $deptErr     = $errors->has('department');
    $deptAttrs   = [
        'id'               => 'department',
        'class'            => trim(VC::FM_CT_SL . ' ' . ($deptErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $deptErr ? 'true' : 'false',
        'aria-describedby' => $deptErr ? 'department-error' : null,
    ];
    if (!$deptsIsList) { $deptAttrs['disabled'] = 'disabled'; }

    $perfIsList = (is_array($performance ?? null) && count($performance ?? []) > 0) || (($performance ?? null) instanceof Collection && $performance->isNotEmpty());

    $ratingTitles = [
        5 => __('Excellent - 5 stars'),
        4 => __('Very good - 4 stars'),
        3 => __('Satisfactory - 3 stars'),
        2 => __('Needs improvement - 2 stars'),
        1 => __('Unsatisfactory - 1 star'),
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
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('branch', __('Branch'), ['class' => VC::FM_LB]) }}
                {{ Form::select('branch', $branchOptions, null, $branchAttrs) }}
                @error('branch')
                    <span id="branch-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('department', __('Department'), ['class' => VC::FM_LB]) }}
                {{ Form::select('department', $deptOptions, null, $deptAttrs) }}
                @error('department')
                    <span id="department-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('designation', __('Designation'), ['class' => VC::FM_LB]) }}
                <select class="select {{ VC::FM_CT_SL }} select2-multiple" id="designation_id" name="designation" data-toggle="select2" data-placeholder="{{ __('Select Designation ...') }}" required></select>
            </div>
        </div>

        @if($perfIsList)
            @foreach($performance as $perf)
                @php
                    $perfName = (string) (data_get($perf, 'name') ?: __('Indicator group name unavailable'));
                    $typesRaw = data_get($perf, 'types');
                    $typesIsList = (is_array($typesRaw) && count($typesRaw) > 0) || ($typesRaw instanceof Collection && $typesRaw->isNotEmpty());
                @endphp
                <div class="{{ VC::RW }}">
                    <div class="{{ VC::C12 }} {{ VC::MT3 }}">
                        <h6 class="{{ VC::H6 }}">{{ $perfName }}</h6>
                        <hr class="{{ VC::MB0 }}">
                    </div>

                    @if($typesIsList)
                        @foreach($typesRaw as $type)
                            @php
                                $typeName = (string) (data_get($type, 'name') ?: __('Indicator type name unavailable'));
                                $typeId   = (string) (data_get($type, 'id') ?: ('x' . $loop->index));
                            @endphp
                            <div class="{{ VC::CM6 }}">{{ $typeName }}</div>
                            <div class="{{ VC::CM6 }}">
                                <fieldset class="rating">
                                    @foreach($ratingTitles as $val => $title)
                                        <input class="stars" type="radio" id="rating-{{ $val }}-{{ $typeId }}" name="rating[{{ $typeId }}]" value="{{ $val }}">
                                        <label class="full" for="rating-{{ $val }}-{{ $typeId }}" title="{{ $title }}"></label>
                                    @endforeach
                                </fieldset>
                            </div>
                        @endforeach
                    @else
                        <div class="{{ VC::C12 }}">
                            <div class="alert alert-warning {{ VC::MB0 }}" role="alert">{{ __('No indicator types available.') }}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="{{ VC::C12 }}">
                <div class="alert alert-warning {{ VC::MB0 }}" role="alert">{{ __('No indicators available.') }}</div>
            </div>
        @endif
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/indicators/store.js') }}"></script>
{{ Form::close() }}
