@php
    $bankAccount ??= [];
    try {
$lang = Utility::fetchUserLang();
        $bankTrfRoute = Route::has(ViewsConstants::BNK_TRF)
            ? route(ViewsConstants::BNK_TRF)
            : (Route::has(Str::kebab(ViewsConstants::BNK_TRF))
                ? route(Str::kebab(ViewsConstants::BNK_TRF))
                : '#');
        $formId     = 'bank-transfer-form';
        $bankTrfMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::BNK_TRF,
            'bank_transfer_index_route_unavailable'
        ) ?? 'Bank transfer route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('bank_transfers/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'            => $bankTrfRoute,
    'id'             => $formId,
    'data-url'       => $bankTrfRoute,
    'data-guard-msg' => $bankTrfMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('from_account', __('From Account'), ['class' => VC::FM_LB]) }}
                {{ Form::select('from_account', $bankAccount, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('to_account', __('To Account'), ['class' => VC::FM_LB]) }}
                {{ Form::select('to_account', $bankAccount, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('amount', __('Amount'), ['class' => VC::FM_LB]) }}
                {{ Form::number('amount', '', ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('date', __('Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('reference', __('Reference'), ['class' => VC::FM_LB]) }}
                {{ Form::text('reference', '', ['class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', '', ['class' => VC::FM_CT, 'rows' => 3]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/bank/transfers/store.js') }}"></script>
{{ Form::close() }}
