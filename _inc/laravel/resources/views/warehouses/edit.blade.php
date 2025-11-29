@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route, Str};

    $lang = Utility::fetchUserLang();

    $updateBase  = VW::WRH . '.update';
    $updateKeb   = Str::kebab($updateBase);
    $updateName  = Route::has($updateBase) ? $updateBase : (Route::has($updateKeb) ? $updateKeb : null);
    $wid         = (string) ($warehouse->id ?? '');
    $updateUrl   = ($updateName && $wid !== '') ? route($updateName, [$wid]) : '#';
    $updateGuard = Utility::fetchLinkMessage($lang, VW::WRH, 'update_warehouse_route_unavailable')
        ?? 'Update warehouse route is unavailable. Please contact technical support or your domain administrator.';
    $formId      = 'edit_warehouse';

    $plan = Utility::getChatGPTSettings();
@endphp

{!! Form::model($warehouse, [
    'url'                  => $updateUrl,
    'method'               => 'PUT',
    'id'                   => $formId,
    'data-resolved-action' => $updateUrl,
    'data-guard-msg'       => $updateGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        @if(($plan?->{PlansConstants::COL_GPT} ?? 0) == 1)
            @php
                $genBase  = 'generate';
                $genName  = Route::has($genBase) ? $genBase : (Route::has(Str::kebab($genBase)) ? Str::kebab($genBase) : null);
                $genUrl   = $genName ? route($genName, ['warehouse']) : '#';
                $genGuard = Utility::fetchLinkMessage($lang, VW::WRH, 'ai_generate_content_unavailable')
                    ?? 'AI content generation for warehouses is unavailable. Please contact technical support or your domain administrator.';
                $genId    = 'warehouse-ai-generate-link';
            @endphp
            <div class="text-end">
                <a id="{{ $genId }}"
                   href="{{ $genUrl }}"
                   data-url="{{ $genUrl }}"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-title="{{ __('Generate content with AI') }}"
                   data-bs-placement="top"
                   data-guard-msg="{{ $genGuard }}"
                   data-sv-localized="true"
                   class="{{ VC::BT_SM_PM }} btn-icon">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
            <script defer src="{{ asset('assets/js/routes/warehouses/generate.js') }}"></script>
        @endif

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => true]) }}
                @error('name')
                    <small class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('address', __('Address'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('address', null, ['class' => VC::FM_CT, 'rows' => 3]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('city', __('City'), ['class' => VC::FM_LB]) }}
                {{ Form::text('city', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('zip', __('Zip Code'), ['class' => VC::FM_LB]) }}
                {{ Form::text('zip', null, ['class' => VC::FM_CT]) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/warehouses/update.js') }}"></script>
{!! Form::close() !!}

