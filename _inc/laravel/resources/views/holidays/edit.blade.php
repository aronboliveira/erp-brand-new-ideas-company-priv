@php
    use App\Config\Constants\{PlansConstants, ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Route};
    use Illuminate\Support\{Collection, Str};
    use Collective\Html\FormFacade as Form;

    $lang        = Utility::fetchUserLang();
    $hasHoliday  = !empty($holiday ?? null) && data_get($holiday, 'id');
    $updateUrl      = '#';
    $updateGuardMsg = Utility::fetchLinkMessage($lang, VW::HLD, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@can('edit holiday')
    @php
        $updateBase     = VW::HLD . '.update';
        $updateKebab    = Str::kebab($updateBase);
        $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
        $updateUrl      = ($updateResolved && $hasHoliday) ? route($updateResolved, $holiday->id) : '#';
    @endphp
@endcan

@if(!$hasHoliday)
    <div class="alert alert-warning mb-0" role="alert">{{ __('The requested holiday was not found or is unavailable.') }}</div>
@else
    {{ Form::model($holiday, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'holiday-edit-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuardMsg,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            @php $plan = Utility::getChatGPTSettings(); @endphp
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                @php
                    $genUrl      = '#';
                    $genGuardMsg = Utility::fetchLinkMessage($lang, VW::HLD, 'generate_ai_route_unavailable') ?? __('AI generation route is unavailable. Please contact technical support or your domain administrator.');
                    if (Route::has('generate')) {
                        $genUrl = route('generate', ['holiday']);
                    }
                @endphp
                <div class="text-end">
                    <a
                        id="holiday-gen-ai"
                        href="#"
                        data-size="md"
                        class="{{ VC::BT_SM_PM }} btn-icon"
                        data-ajax-popup-over="true"
                        data-url="{{ $genUrl }}"
                        data-bs-placement="top"
                        data-title="{{ __('Generate content with AI') }}"
                        data-guard-msg="{{ $genGuardMsg }}"
                        data-sv-localized="true"
                    >
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif

            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('occasion', __('Occasion'), ['class' => 'form-label']) }}
                    {{ Form::text('occasion', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter occasion')]) }}
                </div>
            </div>
            <div class="row">
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('date', __('Start Date'), ['class' => 'form-label']) }}
                    {{ Form::date('date', null, ['class' => VC::FM_CT]) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                    {{ Form::date('end_date', null, ['class' => VC::FM_CT]) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/holidays/edit.js') }}"></script>
        <script async src="{{ asset('assets/js/routes/holidays/generateEdit.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/holidays/editPicker.js') }}"></script>
    {{ Form::close() }}
@endif
