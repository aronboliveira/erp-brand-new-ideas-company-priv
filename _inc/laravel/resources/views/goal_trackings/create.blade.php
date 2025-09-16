@php
    use App\Config\Constants\{
        PlansConstants,
        ProjectsConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Illuminate\Support\Collection;

    $lang = Utility::fetchUserLang();

    $storeBase  = ViewsConstants::GL_TRC;
    $storeKebab = Str::kebab($storeBase);
    $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeRes ? route($storeRes) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, ViewsConstants::GL_TRC, 'store_route_unavailable')
                 ?? __('Goal tracking store route is unavailable. Please contact technical support or your domain administrator.');
    $formId     = 'gl-trc-store-form';

    $plan      = Utility::getChatGPTSettings();
    $aiEnabled = (int) data_get($plan, ProjectsConstants::COL_GPT, 0) === 1;

    $genBase   = 'generate';
    $genKebab  = Str::kebab($genBase);
    $genRes    = Route::has($genBase) ? $genBase : (Route::has($genKebab) ? $genKebab : null);
    $genUrl    = $genRes ? route($genRes, ['goal tracking']) : '#';
    $genGuard  = Utility::fetchLinkMessage($lang, ViewsConstants::GL_TRC, 'ai_generate_route_unavailable')
                ?? __('Generate content route for goal trackings is unavailable. Please contact technical support or your domain administrator.');

    $branchesIsList = (is_array($brances ?? null) && count($brances ?? []) > 0)
                   || (($brances ?? null) instanceof Collection && $brances->isNotEmpty());
    $branchOptions  = $branchesIsList ? (is_array($brances) ? $brances : $brances->toArray()) : ['' => __('No branches available')];
    $branchHasErr   = $errors->has('branch');
    $branchAttrs    = [
        'id'               => 'branch',
        'class'            => trim(VC::FM_CT_SL . ' ' . ($branchHasErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $branchHasErr ? 'true' : 'false',
        'aria-describedby' => $branchHasErr ? 'branch-error' : null,
    ];
    if (!$branchesIsList) { $branchAttrs['disabled'] = 'disabled'; }

    $goalTypesIsList = (is_array($goalTypes ?? null) && count($goalTypes ?? []) > 0)
                    || (($goalTypes ?? null) instanceof Collection && $goalTypes->isNotEmpty());
    $goalTypeOptions = $goalTypesIsList ? (is_array($goalTypes) ? $goalTypes : $goalTypes->toArray()) : ['' => __('No goal types available')];
    $goalTypeHasErr  = $errors->has('goal_type');
    $goalTypeAttrs   = [
        'id'               => 'goal_type',
        'class'            => trim(VC::FM_CT_SL . ' ' . ($goalTypeHasErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $goalTypeHasErr ? 'true' : 'false',
        'aria-describedby' => $goalTypeHasErr ? 'goal_type-error' : null,
    ];
    if (!$goalTypesIsList) { $goalTypeAttrs['disabled'] = 'disabled'; }

    $statusIsList  = (is_array($status ?? null) && count($status ?? []) > 0)
                  || (($status ?? null) instanceof Collection && $status->isNotEmpty());
    $statusOptions = $statusIsList ? (is_array($status) ? $status : $status->toArray()) : ['' => __('No status options available')];
    $statusHasErr  = $errors->has('status');
    $statusAttrs   = [
        'id'               => 'status',
        'class'            => trim(VC::FM_CT_SL . ' ' . ($statusHasErr ? 'is-invalid' : '')),
        'aria-invalid'     => $statusHasErr ? 'true' : 'false',
        'aria-describedby' => $statusHasErr ? 'status-error' : null,
    ];
    if (!$statusIsList) { $statusAttrs['disabled'] = 'disabled'; }

    $subjectHasErr = $errors->has('subject');
    $subjectAttrs  = [
        'id'               => 'subject',
        'class'            => trim(VC::FM_CT . ' ' . ($subjectHasErr ? 'is-invalid' : '')),
        'placeholder'      => __('Enter subject'),
        'aria-invalid'     => $subjectHasErr ? 'true' : 'false',
        'aria-describedby' => $subjectHasErr ? 'subject-error' : null,
        'autocomplete'     => 'off',
    ];

    $targetHasErr = $errors->has('target_achievement');
    $targetAttrs  = [
        'id'               => 'target_achievement',
        'class'            => trim(VC::FM_CT . ' ' . ($targetHasErr ? 'is-invalid' : '')),
        'placeholder'      => __('Enter target achievement'),
        'aria-invalid'     => $targetHasErr ? 'true' : 'false',
        'aria-describedby' => $targetHasErr ? 'target_achievement-error' : null,
        'autocomplete'     => 'off',
    ];

    $descHasErr = $errors->has('description');
    $descAttrs  = [
        'id'               => 'description',
        'class'            => trim(VC::FM_CT . ' description ' . ($descHasErr ? 'is-invalid' : '')),
        'placeholder'      => __('Enter description'),
        'aria-invalid'     => $descHasErr ? 'true' : 'false',
        'aria-describedby' => $descHasErr ? 'description-error' : null,
    ];

    $startHasErr = $errors->has('start_date');
    $endHasErr   = $errors->has('end_date');

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
        @if($aiEnabled)
            <div class="text-end">
                <a  href="{{ $genUrl }}"
                    data-size="md"
                    data-ajax-popup-over="true"
                    data-url="{{ $genUrl }}"
                    data-guard-msg="{{ $genGuard }}"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}"
                    class="{{ VC::BT_SM_PM }} ai-btn">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('branch', __('Branch'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('branch', $branchOptions, null, $branchAttrs) }}
                    @error('branch')
                        <span id="branch-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>

            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('goal_type', __('GoalTypes'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('goal_type', $goalTypeOptions, null, $goalTypeAttrs) }}
                    @error('goal_type')
                        <span id="goal_type-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>

            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('start_date', null, [
                        'id'               => 'start_date',
                        'class'            => trim(VC::FM_CT . ' ' . ($startHasErr ? 'is-invalid' : '')),
                        'aria-invalid'     => $startHasErr ? 'true' : 'false',
                        'aria-describedby' => $startHasErr ? 'start_date-error' : null,
                    ]) }}
                    @error('start_date')
                        <span id="start_date-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>

            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('end_date', null, [
                        'id'               => 'end_date',
                        'class'            => trim(VC::FM_CT . ' ' . ($endHasErr ? 'is-invalid' : '')),
                        'aria-invalid'     => $endHasErr ? 'true' : 'false',
                        'aria-describedby' => $endHasErr ? 'end_date-error' : null,
                    ]) }}
                    @error('end_date')
                        <span id="end_date-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>

            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('subject', __('Subject'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('subject', null, $subjectAttrs) }}
                    @error('subject')
                        <span id="subject-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>

            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('target_achievement', __('Target Achievement'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('target_achievement', null, $targetAttrs) }}
                    @error('target_achievement')
                        <span id="target_achievement-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>

            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                    {{ Form::textarea('description', null, $descAttrs) }}
                    @error('description')
                        <span id="description-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>

            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('status', __('Status'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('status', $statusOptions, null, $statusAttrs) }}
                    @error('status')
                        <span id="status-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>

            <div class="{{ VC::C12 }}">
                <fieldset id="demo1" class="rating">
                    @foreach($ratingTitles as $val => $title)
                        <input class="stars" type="radio" id="rating-{{ $val }}" name="rating" value="{{ $val }}">
                        <label class="full" for="rating-{{ $val }}" title="{{ $title }}"></label>
                    @endforeach
                </fieldset>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/goals/trackings/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/goals/trackings/generateStore.js') }}"></script>
{{ Form::close() }}
