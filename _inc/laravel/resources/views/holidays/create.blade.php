@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Illuminate\Support\Collection;

    $lang = Utility::fetchUserLang();

    $formId     = 'hld-store-form';
    $storeBase  = VW::HLD . '.store';
    $storeKebab = Str::kebab($storeBase);
    $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeRes ? route($storeRes) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, VW::HLD, 'store_route_unavailable')
                 ?? __('Holiday store route is unavailable. Please contact technical support or your domain administrator.');

    $plan        = Utility::getChatGPTSettings();
    $aiEnabled   = (int) data_get($plan, PlansConstants::COL_GPT, 0) === 1;
    $user        = auth()->user();
    $aiContextId = (string) ($user?->creatorId() ?? $user?->id ?? '0');
    $genBase     = 'generate';
    $genKebab    = Str::kebab($genBase);
    $genRes      = Route::has($genBase) ? $genBase : (Route::has($genKebab) ? $genKebab : null);
    $genUrl      = $genRes ? route($genRes, [$aiContextId]) : '#';
    $genGuard    = Utility::fetchLinkMessage($lang, VW::HLD, 'generate_ai_route_unavailable')
                 ?? __('Generate content route is unavailable. Please contact technical support or your domain administrator.');

    $occErr   = $errors->has('occasion');
    $occAttrs = [
        'id'               => 'occasion',
        'class'            => trim(VC::FM_CT . ' ' . ($occErr ? 'is-invalid' : '')),
        'placeholder'      => __('Enter occasion'),
        'aria-invalid'     => $occErr ? 'true' : 'false',
        'aria-describedby' => $occErr ? 'occasion-error' : null,
        'autocomplete'     => 'off',
    ];

    $startErr = $errors->has('date');
    $endErr   = $errors->has('end_date');

    $gcalEnabled = (bool) (is_array($settings ?? null) && data_get($settings, 'google_calendar_enable') === 'on');
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
                    class="ai-btn {{ VC::BT_SM_PM }}">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('occasion', __('Occasion'), ['class' => VC::FM_LB]) }}
                {{ Form::text('occasion', null, $occAttrs) }}
                @error('occasion')
                    <span id="occasion-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('date', __('Start Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('date', null, [
                    'id'               => 'date',
                    'class'            => trim(VC::FM_CT . ' ' . ($startErr ? 'is-invalid' : '')),
                    'aria-invalid'     => $startErr ? 'true' : 'false',
                    'aria-describedby' => $startErr ? 'date-error' : null,
                ]) }}
                @error('date')
                    <span id="date-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('end_date', null, [
                    'id'               => 'end_date',
                    'class'            => trim(VC::FM_CT . ' ' . ($endErr ? 'is-invalid' : '')),
                    'aria-invalid'     => $endErr ? 'true' : 'false',
                    'aria-describedby' => $endErr ? 'end_date-error' : null,
                ]) }}
                @error('end_date')
                    <span id="end_date-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>

        @if($gcalEnabled)
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('synchronize_type', __('Synchronize in Google Calendar ?'), ['class' => VC::FM_LB]) }}
                <div class="form-switch">
                    <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calendar">
                    <label class="form-check-label" for="switch-shadow"></label>
                </div>
            </div>
        @endif
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/holidays/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/holidays/generateStore.js') }}"></script>
{{ Form::close() }}
