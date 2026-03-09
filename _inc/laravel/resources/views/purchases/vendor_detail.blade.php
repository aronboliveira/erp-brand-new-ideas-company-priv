@if(!empty($vendor))
    <div class="row">
        <div class="col-md-5">
            <h6>{{__('Bill to')}}</h6>
            <div class="bill-to">
                @if(!empty($vendor['billing_name']))
                    <small>
                        <span>{{$vendor['billing_name']}}</span><br>
                        <span>{{$vendor['billing_phone']}}</span><br>
                        <span>{{$vendor['billing_address']}}</span><br>
                        <span>{{$vendor['billing_zip']}}</span><br>
                        <span>{{$vendor['billing_country'] . ' , '.$vendor['billing_city'].' , '.$vendor['billing_state'].'.'}}</span>
                    </small>
                @else
                    <br> -
                @endif
            </div>
        </div>
        <div class="col-md-5">
            <h6>{{__('Ship to')}}</h6>
            <div class="bill-to">
                @if(!empty($vendor['billing_name']))
                    <small>
                        <span>{{$vendor['shipping_name']}}</span><br>
                        <span>{{$vendor['shipping_phone']}}</span><br>
                        <span>{{$vendor['shipping_address']}}</span><br>
                        <span>{{$vendor['shipping_zip']}}</span><br>
                        <span>{{$vendor['shipping_country'] . ' , '.$vendor['shipping_state'].' , '.$vendor['shipping_city'].'.'}}</span>
                    </small>
                @else
                    <br> -
                @endif
            </div>
        </div>
        <div class="{{ VC::CM2 }}">
            <a href="#" id="remove" class="{{ VC::TXSM }}">{{__(' Remove')}}</a>
        </div>
    </div>
@endif
