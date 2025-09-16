<div class="modal-body">
    <div class="card ">
        <div class="card-body table-border-style full-card">
            <div class="table-responsive">
                {{--                    @if(!$products->isEmpty())--}}
                <table class="table">
                    <thead>
                    <tr>
                        <th>{{__('Warehouse') }}</th>
                        <th>{{__('Quantity')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($products as $product)
                        @if(!empty($product->warehouse()))
                            <tr>
                                <td>{{ !empty($product->warehouse())?( !empty($product->warehouse()->name) ? $product->warehouse()->name : __('No name available')) :__('No name available') }}</td>
                                <td>{{ !empty($product->quantity) ? $product->quantity : __('No quantity available') }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">{{__(' Product not select in warehouse')}}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
