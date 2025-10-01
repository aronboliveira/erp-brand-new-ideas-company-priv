@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route};
    use Illuminate\Support\{Collection, Str};

    $lang                = Utility::fetchUserLang();
    $estStoreBase        = VW::EST;
    $estStoreKebab       = Str::kebab($estStoreBase);
    $estStoreResolved    = Route::has($estStoreBase) ? $estStoreBase : (Route::has($estStoreKebab) ? $estStoreKebab : null);
    $estStoreUrl         = $estStoreResolved ? route($estStoreResolved) : '#';
    $estStoreFormId      = 'estimate-store-form';
    $estStoreGuardMsg    = Utility::fetchLinkMessage($lang, VW::EST, 'store_estimate_route_unavailable') ?? 'Store estimate route is unavailable. Please contact technical support or your domain administrator.';
    $clientsIsList       = (is_array($client ?? null) && count($client ?? []) > 0) || (($client ?? null) instanceof Collection && $client->isNotEmpty());
    $taxesIsList         = (is_array($taxes  ?? null) && count($taxes  ?? []) > 0) || (($taxes  ?? null) instanceof Collection && $taxes->isNotEmpty());
    $clientOptions       = $clientsIsList ? (is_array($client) ? $client : $client->toArray()) : [__('No clients available')];
    $taxOptions          = $taxesIsList   ? (is_array($taxes)  ? $taxes  : $taxes->toArray())  : [__('No taxes available')];
    $taxIndexBase        = VW::TX.'.index';
    $taxIndexKebab       = Str::kebab($taxIndexBase);
    $taxIndexResolved    = Route::has($taxIndexBase) ? $taxIndexBase : (Route::has($taxIndexKebab) ? $taxIndexKebab : null);
    $taxIndexUrl         = $taxIndexResolved ? route($taxIndexResolved) : '#';
    $taxIndexLinkId      = 'tax-index-link';
    $taxIndexGuardMsg    = Utility::fetchLinkMessage($lang, VW::TX, 'tx_index_route_unavailable') ?? 'Tax index route is unavailable. Please contact technical support or your domain administrator.';
@endphp

<div class="{{ VC::CD }} bg-none card-box">
    {{ Form::open([
        'url'               => $estStoreUrl,
        'method'            => 'POST',
        'id'                => $estStoreFormId,
        'data-url'          => $estStoreUrl,
        'data-guard-msg'    => $estStoreGuardMsg,
        'data-sv-localized' => 'true',
    ]) }}
        @csrf
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::CLMS6 }}">
                {{ Form::label('client_id', __('Client'), ['class' => VC::FM_LB]) }}
                {{ Form::select(
                    'client_id',
                    $clientOptions,
                    null,
                    array_merge(['class' => VC::FM_CT.' select2', 'required' => 'required', 'placeholder' => __('Select Client')], $clientsIsList ? [] : ['disabled' => 'disabled'])
                ) }}
                @unless($clientsIsList)
                    <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ __('No clients available.') }}</div>
                @endunless
            </div>

            <div class="{{ VC::FM_G }} {{ VC::CLMS6 }}">
                {{ Form::label('issue_date', __('Issue Date'), ['class' => VC::FM_LB]) }}
                {{ Form::text('issue_date', null, ['class' => VC::FM_CT.' datepicker', 'required' => 'required', 'placeholder' => __('Select Issue Date')]) }}
            </div>

            <div class="{{ VC::FM_G }} {{ VC::CLMS6 }}">
                {{ Form::label('tax_id', __('Tax %'), ['class' => VC::FM_LB]) }}
                {{ Form::select(
                    'tax_id',
                    $taxOptions,
                    null,
                    array_merge(['class' => VC::FM_CT.' select2', 'required' => 'required', 'placeholder' => __('Select Tax')], $taxesIsList ? [] : ['disabled' => 'disabled'])
                ) }}
                @unless($taxesIsList)
                    <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">
                        {{ __('Please create new Tax') }}
                        <a
                            id="{{ $taxIndexLinkId }}"
                            href="{{ $taxIndexUrl }}"
                            data-url="{{ $taxIndexUrl }}"
                            data-guard-msg="{{ $taxIndexGuardMsg }}"
                            data-sv-localized="true"
                        >{{ __('here') }}</a>.
                    </div>
                @endunless
            </div>

            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('terms', __('Terms'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('terms', null, ['class' => VC::FM_CT, 'rows' => 3, 'placeholder' => __('Enter terms...')]) }}
            </div>

            <div class="{{ VC::C12 }} text-end">
                <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
                <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            </div>
        </div>
        <script defer src="{{ asset('assets/js/routes/estimations/store.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/taxes/estimationsIndex.js') }}"></script>
    {{ Form::close() }}
</div>
