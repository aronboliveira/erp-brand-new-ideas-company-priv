@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        YieldingConstants as YW,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Collection, Facades\Auth, Facades\Route, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $indexBase  = VW::VND . '.transaction';
    $indexKebab = Str::kebab($indexBase);
    $indexName  = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
    $indexUrl   = $indexName ? route($indexName) : '#';
    $indexGuard = Utility::fetchLinkMessage($lang, VW::VND, 'vendor_transaction_route_unavailable')
        ?? 'Vendor transaction route is unavailable. Please contact technical support or your domain administrator.';
    $formId     = 'vendor-transaction-filter-form';
    $resetId    = 'vendor-transaction-reset-link';

    $categoryOptions = (($category ?? null) instanceof Collection) ? $category->toArray() : (is_array($category ?? null) ? $category : []);
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Transaction') }}
@endsection

@section(YW::ADM_CTT)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    {!! Form::open([
                        'url'                  => $indexUrl,
                        'method'               => 'GET',
                        'id'                   => $formId,
                        'data-resolved-action' => $indexUrl,
                        'data-guard-msg'       => $indexGuard,
                        'data-sv-localized'    => 'true',
                    ]) !!}
                        <div class="row d-flex justify-content-end mt-2">
                            <div class="{{ VC::CL_XL3 }}">
                                <div class="all-select-box">
                                    <div class="btn-box">
                                        {{ Form::label('date', __('Date'), ['class' => 'text-type']) }}
                                        {{ Form::text('date', request('date'), ['class' => 'form-control datepicker-range']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::CL_XL3 }}">
                                <div class="all-select-box">
                                    <div class="btn-box">
                                        {{ Form::label('category', __('Category'), ['class' => 'text-type']) }}
                                        {{ Form::select('category', ['' => 'All'] + $categoryOptions, request('category'), ['class' => 'form-control select2']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto my-auto">
                                <button type="submit" class="apply-btn" data-bs-toggle="tooltip" title="{{ __('apply') }}">
                                    <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                </button>
                                <a id="{{ $resetId }}"
                                   href="{{ $indexUrl }}"
                                   data-url="{{ $indexUrl }}"
                                   data-guard-msg="{{ $indexGuard }}"
                                   data-sv-localized="true"
                                   class="reset-btn"
                                   data-bs-toggle="tooltip"
                                   title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-restore-alt"></i></span>
                                </a>
                            </div>
                        </div>
                    {!! Form::close() !!}

                    <div class="table-responsive mt-3">
                        <table class="table table-striped mb-0 dataTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Account') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $rows = (($transactions ?? null) instanceof Collection || is_array($transactions ?? null)) ? $transactions : [];
                                @endphp
                                @forelse($rows as $tx)
                                    @php
                                        $dateRaw = data_get($tx, 'date');
                                        $dateStr = $dateRaw
                                            ? ($user && method_exists($user, 'dateFormat') ? ($user->dateFormat($dateRaw) ?? (string) $dateRaw) : (string) $dateRaw)
                                            : __('No date available');

                                        $amtRaw = data_get($tx, 'amount');
                                        $amtStr = (isset($amtRaw) && $amtRaw !== '' && is_numeric($amtRaw))
                                            ? ($user && method_exists($user, 'priceFormat') ? ($user->priceFormat($amtRaw) ?? (string) $amtRaw) : (string) $amtRaw)
                                            : __('No amount available');

                                        $bank   = (is_object($tx) && method_exists($tx, 'bankAccount')) ? $tx->bankAccount() : null;
                                        $bankNm = data_get($bank, 'bank_name');
                                        $holdNm = data_get($bank, 'holder_name');
                                        $acctStr = ($bankNm || $holdNm)
                                            ? trim(($bankNm ?? '') . ' ' . ($holdNm ?? ''))
                                            : __('No account available');

                                        $typeRaw = data_get($tx, 'type');
                                        $typeStr = (isset($typeRaw) && $typeRaw !== '') ? (string) $typeRaw : __('No type available');

                                        $catRaw  = data_get($tx, 'category');
                                        $catStr  = (isset($catRaw) && $catRaw !== '') ? (string) $catRaw : __('No category available');

                                        $descRaw = data_get($tx, 'description');
                                        $descStr = (isset($descRaw) && $descRaw !== '') ? (string) $descRaw : __('No description available');
                                    @endphp
                                    <tr>
                                        <td>{{ $dateStr }}</td>
                                        <td>{{ $amtStr }}</td>
                                        <td>{{ $acctStr }}</td>
                                        <td>{{ $typeStr }}</td>
                                        <td>{{ $catStr }}</td>
                                        <td>{{ $descStr }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            {{ __('No transaction information available') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/vendors/transactions.js') }}"></script>
@endpush
