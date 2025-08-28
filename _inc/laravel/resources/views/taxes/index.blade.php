@php
	use App\Config\Constants\{
		ExtendingLayoutsConstants,
		StacksConstants,
		ViewClassNamesConstants as VC,
		ViewsConstants as VW,
		YieldingConstants
	};
	use App\Models\Utility;
	use Illuminate\Support\{Facades\Log, Facades\Route, Str};
	use InvalidArgumentException;

	$lang = Utility::fetchUserLang();

	$view ??= null;

	$dashboardResolved = null;
	$dashboardUrl = '#';
	$dashboardGuardMsg = Utility::fetchLinkMessage($lang, VW::TX, 'dashboard_route_unavailable') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';

	$taxCreateResolved = null;
	$taxCreateUrl = '#';
	$taxCreateGuardMsg = Utility::fetchLinkMessage($lang, VW::TX, 'create_tax_route_unavailable') ?? 'Create tax route is unavailable. Please contact technical support or your domain administrator.';

	try {
		$dashboardResolved = Route::has('dashboard') ? 'dashboard' : (Route::has(Str::kebab('dashboard')) ? Str::kebab('dashboard') : null);
	} catch (\Error $e) {
		Log::error('Blade taxes/index: route name resolution error for dashboard (Error): ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade taxes/index: invalid argument while resolving dashboard route: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade taxes/index: general exception while resolving dashboard route: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade taxes/index: throwable while resolving dashboard route: ' . $e->getMessage());
	}

	try {
		$dashboardUrl = $dashboardResolved ? route($dashboardResolved) : '#';
	} catch (\Throwable $e) {
		Log::error('Blade taxes/index: dashboard URL generation error: ' . $e->getMessage());
		$dashboardUrl = '#';
	}

	try {
		$taxCreateResolved = Route::has(VW::TX . '.create') ? VW::TX . '.create' : (Route::has(Str::kebab(VW::TX . '.create')) ? Str::kebab(VW::TX . '.create') : null);
	} catch (\Error $e) {
		Log::error('Blade taxes/index: route name resolution error for taxes.create (Error): ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade taxes/index: invalid argument while resolving taxes.create: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade taxes/index: general exception while resolving taxes.create: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade taxes/index: throwable while resolving taxes.create: ' . $e->getMessage());
	}

	try {
		$taxCreateUrl = $taxCreateResolved ? route($taxCreateResolved) : '#';
	} catch (\Throwable $e) {
		Log::error('Blade taxes/index: taxes.create URL generation error: ' . $e->getMessage());
		$taxCreateUrl = '#';
	}
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Manage Tax Rate') }}
@endsection

@section(YieldingConstants::ADM_BDC)
	<li class="breadcrumb-item">
		<a href="{{ $dashboardUrl }}"
		   {{ $dashboardUrl === '#' ? 'aria-disabled=true' : '' }}
		   class="dashboard-link"
		   data-url="{{ $dashboardUrl }}"
		   data-guard-msg="{{ $dashboardGuardMsg }}"
		   data-sv-localized="true">
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="breadcrumb-item">{{ __('Taxes') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
	<div class="{{ VC::FEND }}">
		@can('create constant tax')
			<a id="tax-create-link"
			   href="{{ $taxCreateUrl }}"
			   data-url="{{ $taxCreateUrl }}"
			   data-ajax-popup="true"
			   data-title="{{ __('Create Tax Rate') }}"
			   data-bs-toggle="tooltip"
			   title="{{ __('Create') }}"
			   data-guard-msg="{{ $taxCreateGuardMsg }}"
			   data-sv-localized="true"
			   class="{{ VC::BT_SM_PM }}">
				<i class="{{ VC::TI_PLS }}"></i>
			</a>
		@endcan
	</div>
@endsection

@section(YieldingConstants::ADM_CTT)
	<div class="row">
		<div class="col-3">
			@include('layouts.account_setup')
		</div>
		<div class="col-9">
			<div class="{{ VC::CD }}">
				<div class="card-body table-border-style">
					<div class="table-responsive">
						<table class="{{ VC::TB }} datatable">
							<thead>
								<tr>
									<th>{{ __('Tax Name') }}</th>
									<th>{{ __('Rate %') }}</th>
									<th width="10%">{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody>
								@foreach(($taxes ?? []) as $taxe)
									@php
										$txId = data_get($taxe, 'id');
                                    @endphp
									<tr class="font-style">
										<td>{{ data_get($taxe, 'name') ?? __('No name available') }}</td>
										<td>{{ data_get($taxe, 'rate') ?? __('No rate available') }}</td>
										<td class="Action">
											<span>
												@can('edit constant tax')
                                                    @php
                                                        $taxEditResolved = null;
                                                        $taxEditUrl = '#';
                                                        $taxEditGuardMsg = Utility::fetchLinkMessage($lang, VW::TX, 'edit_tax_route_unavailable') ?? 'Edit tax route is unavailable. Please contact technical support or your domain administrator.';
                                                        try {
                                                            $taxEditResolved = Route::has(VW::TX . '.edit') ? VW::TX . '.edit' : (Route::has(Str::kebab(VW::TX . '.edit')) ? Str::kebab(VW::TX . '.edit') : null);
                                                        } catch (\Throwable $e) {
                                                            Log::error('Blade taxes/index: route name resolution error for taxes.edit: ' . $e->getMessage());
                                                        }
                                                        try {
                                                            $taxEditUrl = ($taxEditResolved && !empty($txId)) ? route($taxEditResolved, $txId) : '#';
                                                        } catch (\Throwable $e) {
                                                            Log::error('Blade taxes/index: taxes.edit URL generation error: ' . $e->getMessage());
                                                            $taxEditUrl = '#';
                                                        }
                                                    @endphp
													<div class="{{ VC::ACT_BTN_PRIM }}">
														<a href="{{ $taxEditUrl }}"
														   class="{{ VC::BT_SM_CT }} tax-edit-link"
														   data-url="{{ $taxEditUrl }}"
														   data-ajax-popup="true"
														   data-title="{{ __('Edit Tax Rate') }}"
														   data-bs-toggle="tooltip"
														   title="{{ __('Edit') }}"
														   data-guard-msg="{{ $taxEditGuardMsg }}"
														   data-sv-localized="true">
															<i class="{{ VC::TI_PC_WT }}"></i>
														</a>
													</div>
												@endcan
												@can('delete constant tax')
                                                    @php
                                                        $taxDestroyResolved = null;
                                                        $taxDestroyUrl = '#';
                                                        $taxDestroyGuardMsg = Utility::fetchLinkMessage($lang, VW::TX, 'delete_tax_route_unavailable') ?? 'Delete tax route is unavailable. Please contact technical support or your domain administrator.';
                                                        try {
                                                            $taxDestroyResolved = Route::has(VW::TX . '.destroy') ? VW::TX . '.destroy' : (Route::has(Str::kebab(VW::TX . '.destroy')) ? Str::kebab(VW::TX . '.destroy') : null);
                                                        } catch (\Throwable $e) {
                                                            Log::error('Blade taxes/index: route name resolution error for taxes.destroy: ' . $e->getMessage());
                                                        }
                                                        try {
                                                            $taxDestroyUrl = ($taxDestroyResolved && !empty($txId)) ? route($taxDestroyResolved, $txId) : '#';
                                                        } catch (\Throwable $e) {
                                                            Log::error('Blade taxes/index: taxes.destroy URL generation error: ' . $e->getMessage());
                                                            $taxDestroyUrl = '#';
                                                        }
                                                    @endphp
													<div class="{{ VC::ACT_BTN_DNG_2 }}">
														{!! Collective\Html\FormFacade::open([
															'method'               => 'DELETE',
															'url'                  => $taxDestroyUrl,
															'id'                   => 'delete-form-' . $txId,
															'data-resolved-action' => $taxDestroyUrl,
															'data-guard-msg'       => $taxDestroyGuardMsg,
															'data-sv-localized'    => 'true',
														]) !!}
															<a href="#"
															   class="{{ VC::BT_SM_CT_PR }} tax-delete-trigger"
															   data-bs-toggle="tooltip"
															   title="{{ __('Delete') }}"
															   data-original-title="{{ __('Delete') }}"
															   data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
															   data-confirm-yes="document.getElementById('delete-form-{{$txId}}').submit();">
																<i class="{{ VC::TI_TRS_WT }}"></i>
															</a>
														{!! Collective\Html\FormFacade::close() !!}
													</div>
												@endcan
											</span>
										</td>
                                    </tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
	<script defer src="{{ asset('assets/js/routes/taxes/createLink.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/taxes/editLink.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/taxes/destroy.js') }}"></script>
@endpush
