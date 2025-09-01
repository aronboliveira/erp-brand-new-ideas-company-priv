@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Illuminate\Support\{Facades\Route, Str};

    $lang = Utility::fetchUserLang();

    $basicFields = [
        ['name'=>'name','type'=>'text','label'=>__('Name'),'cols'=>6,'required'=>true],
        ['name'=>'contact','type'=>'number','label'=>__('Contact'),'cols'=>6,'required'=>true],
        ['name'=>'tax_number','type'=>'text','label'=>__('Tax Number'),'cols'=>4],
    ];
    $billingFields = [
        ['name'=>'billing_name','type'=>'text','label'=>__('Name'),'cols'=>6],
        ['name'=>'billing_phone','type'=>'text','label'=>__('Phone'),'cols'=>6],
        ['name'=>'billing_address','type'=>'textarea','label'=>__('Address'),'cols'=>12,'rows'=>3],
        ['name'=>'billing_city','type'=>'text','label'=>__('City'),'cols'=>6],
        ['name'=>'billing_state','type'=>'text','label'=>__('State'),'cols'=>6],
        ['name'=>'billing_country','type'=>'text','label'=>__('Country'),'cols'=>6],
        ['name'=>'billing_zip','type'=>'text','label'=>__('Zip Code'),'cols'=>6],
    ];
    $shippingFields = [
        ['name'=>'shipping_name','type'=>'text','label'=>__('Name'),'cols'=>6],
        ['name'=>'shipping_phone','type'=>'text','label'=>__('Phone'),'cols'=>6],
        ['name'=>'shipping_address','type'=>'textarea','label'=>__('Address'),'cols'=>12,'rows'=>3],
        ['name'=>'shipping_city','type'=>'text','label'=>__('City'),'cols'=>6],
        ['name'=>'shipping_state','type'=>'text','label'=>__('State'),'cols'=>6],
        ['name'=>'shipping_country','type'=>'text','label'=>__('Country'),'cols'=>6],
        ['name'=>'shipping_zip','type'=>'text','label'=>__('Zip Code'),'cols'=>6],
    ];
    $showShipping = Utility::getValByName('shipping_display') === 'on';

    $vendorId    = (string) ($vendor->id ?? '');
    $updateBase  = VW::VND . '.update';
    $updateKebab = Str::kebab($updateBase);
    $updateName  = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl   = ($updateName && $vendorId) ? route($updateName, [$vendorId]) : '#';
    $updateGuard = Utility::fetchLinkMessage($lang, VW::VND, 'update_vendor_route_unavailable')
        ?? 'Update vendor route is unavailable. Please contact technical support or your domain administrator.';
    $formId      = 'vendor-update-form';
@endphp

{!! Form::model($vendor, [
    'url'                  => $updateUrl,
    'method'               => 'PUT',
    'id'                   => $formId,
    'data-resolved-action' => $updateUrl,
    'data-guard-msg'       => $updateGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        <h6 class="sub-title">{{ __('Basic Info') }}</h6>
        <div class="row">
            @foreach($basicFields as $f)
                <div class="{{ VC::CLM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label($f['name'], $f['label'], ['class'=>VC::FM_LB]) }}
                        @php $attrs = [ 'class'=>VC::FM_CT ] + (!empty($f['required']) ? ['required'=>'required'] : []); @endphp
                        {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                    </div>
                </div>
            @endforeach

            @if(!$customFields->isEmpty())
                <div class="{{ VC::CLMS4 }}">
                    <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                        @include(VW::CST_FD . '.formBuilder')
                    </div>
                </div>
            @endif
        </div>

        <h6 class="sub-title">{{ __('Billing Address') }}</h6>
        <div class="row">
            @foreach($billingFields as $f)
                <div class="{{ VC::CLM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label($f['name'], $f['label'], ['class'=>VC::FM_LB]) }}
                        @php $attrs = ['class'=>VC::FM_CT] + (isset($f['rows']) ? ['rows'=>$f['rows']] : []); @endphp
                        @if($f['type'] === 'textarea')
                            {{ Form::textarea($f['name'], null, $attrs) }}
                        @else
                            {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($showShipping)
            <div class="col-md-12 text-end mb-3">
                <button type="button" id="billing_data" class="btn btn-primary">{{ __('Shipping Same As Billing') }}</button>
            </div>

            <h6 class="sub-title">{{ __('Shipping Address') }}</h6>
            <div class="row">
                @foreach($shippingFields as $f)
                    <div class="{{ VC::CLM6 }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label($f['name'], $f['label'], ['class'=>VC::FM_LB]) }}
                            @php $attrs = ['class'=>VC::FM_CT] + (isset($f['rows']) ? ['rows'=>$f['rows']] : []); @endphp
                            @if($f['type'] === 'textarea')
                                {{ Form::textarea($f['name'], null, $attrs) }}
                            @else
                                {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
    </div>

    <script defer src="{{ asset('assets/js/routes/vendors/update.js') }}"></script>
{!! Form::close() !!}
