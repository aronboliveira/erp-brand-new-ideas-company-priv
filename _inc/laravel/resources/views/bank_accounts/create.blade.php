@php
    try {
$lang = Utility::fetchUserLang();
        $bankAccountStoreRoute = Route::has(ViewsConstants::BNK_ACC)
            ? route(ViewsConstants::BNK_ACC)
            : (Route::has(Str::kebab(ViewsConstants::BNK_ACC))
                ? route(Str::kebab(ViewsConstants::BNK_ACC))
                : '#');

        $formId = 'bank-account-store-form';
        $storeMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::BNK_ACC,
            'bank_account_store_route_unavailable'
        ) ?? 'Bank Account store route is unavailable. Please contact technical support or your domain administrator.';
        $fields = [
            [
                'name'    => 'chart_account_id',
                'type'    => 'select',
                'label'   => __('Account'),
                'options' => $chart_accounts,
                'cols'    => 6,
            ],
            [
                'name'    => 'holder_name',
                'type'    => 'text',
                'label'   => __('Bank Holder Name'),
                'cols'    => 6,
            ],
            [
                'name'    => 'bank_name',
                'type'    => 'text',
                'label'   => __('Bank Name'),
                'cols'    => 6,
            ],
            [
                'name'    => 'account_number',
                'type'    => 'text',
                'label'   => __('Account Number'),
                'cols'    => 6,
            ],
            [
                'name'    => 'opening_balance',
                'type'    => 'number',
                'label'   => __('Opening Balance'),
                'attrs'   => ['step' => '0.01'],
                'cols'    => 6,
            ],
            [
                'name'    => 'contact_number',
                'type'    => 'text',
                'label'   => __('Contact Number'),
                'cols'    => 6,
            ],
            [
                'name'    => 'bank_address',
                'type'    => 'textarea',
                'label'   => __('Bank Address'),
                'attrs'   => ['rows' => 3],
                'cols'    => 12,
            ],
        ];
    } catch (\Throwable $e) {
        \Log::error('bank_accounts/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'            => $bankAccountStoreRoute,
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $bankAccountStoreRoute,
    'data-guard-msg' => $storeMsg,
]) }}
    <div class="modal-body">
        <div class="row">
            @foreach($fields as $f)
                <div class="{{ VC::FM_G }} col-md-{{ $f['cols'] }}">
                    {{ Form::label($f['name'], $f['label'], ['class' => 'form-label']) }}
                    @php
                        try {
                            $common = ['class' => 'form-control', 'required' => 'required'];
                            if (!empty($f['attrs'])) {
                                $common = array_merge($common, $f['attrs']);
                            }
                        } catch (\Throwable $e) {
                            \Log::error('bank_accounts/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    @if($f['type'] === 'select')
                        {{ Form::select($f['name'], $f['options'], null, $common + ['placeholder' => '']) }}
                    @elseif($f['type'] === 'textarea')
                        {{ Form::textarea($f['name'], null, $common) }}
                    @else
                        {{ Form::{$f['type']}($f['name'], null, $common) }}
                    @endif
                </div>
            @endforeach

            @if(isset($customFields) && !$customFields->isEmpty())
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::TAB_FD_SH }}" id="tab-2" role="tabpanel">
                        @include(ViewsConstants::CST_FD . '.formBuilder')
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
    <script defer src="{{ asset('assets/js/routes/bank/accounts/store.js') }}"></script>
{{ Form::close() }}
