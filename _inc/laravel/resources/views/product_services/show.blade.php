@php
	$productService ??= null;
	$product_services ??= null;
	$customFields ??= [];
	$lang ??= 'en';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
	} catch (\Throwable $e) {
		\Log::error('product_services/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Product / Service Detail') }}
@endsection

@section(YieldingConstants::ADM_BDC)
	<li class="{{ VC::BCI }}">
		<a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
		   {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="{{ VC::BCI }}">
		@php
			$prdSvIndexRoute = Route::has(ViewsConstants::PRD_SV . '.index')
				? route(ViewsConstants::PRD_SV . '.index')
				: '#';
		@endphp
		<a href="{{ $prdSvIndexRoute }}">{{ __('Product / Service') }}</a>
	</li>
	<li class="{{ VC::BCI }}">{{ data_get($productService, 'name', __('Detail')) }}</li>
@endsection

@section(YieldingConstants::ADM_CTT)
	<div class="{{ VC::RW }}">
		<div class="{{ VC::C12 }}">
			<div class="{{ VC::CD }}">
				<div class="{{ VC::CD_HD }}">
					<h5>{{ data_get($productService, 'name', __('N/A')) }}</h5>
				</div>
				<div class="{{ VC::CD_MT }}">
					@if($productService)
						<div class="{{ VC::TABLE_RESPONSIVE }}">
							<table class="{{ VC::TB }}">
								<tbody>
									<tr>
										<th>{{ __('Name') }}</th>
										<td>{{ data_get($productService, 'name', __('N/A')) }}</td>
									</tr>
									<tr>
										<th>{{ __('SKU') }}</th>
										<td>{{ data_get($productService, 'sku', __('N/A')) }}</td>
									</tr>
									<tr>
										<th>{{ __('Sale Price') }}</th>
										<td>{{ data_get($productService, 'sale_price', __('N/A')) }}</td>
									</tr>
									<tr>
										<th>{{ __('Purchase Price') }}</th>
										<td>{{ data_get($productService, 'purchase_price', __('N/A')) }}</td>
									</tr>
									<tr>
										<th>{{ __('Category') }}</th>
										<td>{{ data_get($productService, 'category.name', __('N/A')) }}</td>
									</tr>
									<tr>
										<th>{{ __('Unit') }}</th>
										<td>{{ data_get($productService, 'unit.name', __('N/A')) }}</td>
									</tr>
									<tr>
										<th>{{ __('Type') }}</th>
										<td>{{ data_get($productService, 'type', __('N/A')) }}</td>
									</tr>
									<tr>
										<th>{{ __('Description') }}</th>
										<td>{{ data_get($productService, 'description', __('N/A')) }}</td>
									</tr>
								</tbody>
							</table>
						</div>
						@if(!empty($customFields))
							<div class="{{ VC::MT2 ?? 'mt-2' }}">
								<h6>{{ __('Custom Fields') }}</h6>
								<div class="{{ VC::TABLE_RESPONSIVE }}">
									<table class="{{ VC::TB }}">
										<tbody>
											@foreach($customFields as $field)
												<tr>
													<th>{{ data_get($field, 'name', __('Field')) }}</th>
													<td>{{ data_get($field, 'value', __('N/A')) }}</td>
												</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						@endif
					@else
						<div class="{{ VC::P4 }} {{ VC::TXCT }} {{ VC::TXT_MT }}">
							{{ __('Product / Service not found.') }}
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>
@endsection
