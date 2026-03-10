<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    BillsConstants,
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Exports\ProposalExport;
use App\Models\{
    Customer,
    CustomField,
    Invoice,
    ProductService,
    ProductServiceCategory,
    Proposal,
    ProposalProduct,
    User,
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Auth,
    Crypt,
    DB,
    Log,
    View as ViewFacade
};
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class ProposalController extends Controller
{
    public function __construct()
    {
        Log::debug('Constructing ' . self::class . '...');
    }

    use ChecksLogin, ChecksPermissions;

    protected const INDEX_ROUTE = ViewsConstants::PPS . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";

        return $this->measureProfile($action, function () use ($request, $action) {
            Log::info("$action start", ['user' => Auth::id()]);
            $stepStart = microtime(true);

            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;

                if (($c = $this->guard($request, PermissionsConstants::MNG_PPS, self::INDEX_ROUTE)) !== true) {
                    Log::warning("$action denied", ['user' => Auth::id()]);
                    return $c;
                }

                $creatorId = $user?->creatorId();
                $customers = Customer::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->pluck(UsersConstants::COL_NM, 'id')->prepend('All', '');
                $status = Proposal::$statuses;

                $query = Proposal::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId);
                if ($request->filled('customer')) $query->where('customer_id', $request->customer);
                if ($request->filled('issue_date')) {
                    $range = explode(' to ', $request->issue_date);
                    $query->whereBetween('issue_date', $range);
                }
                if ($request->filled('status')) $query->where('status', $request->status);

                $proposals = $query->get();
                Log::info("$action fetched", ['count' => $proposals->count()]);
                $this->logExecutionTime($stepStart, 'fetch proposals', 'completed');

                $view = ViewsConstants::PPS . '.index';
                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException($request, new \RuntimeException('View not found: ' . $view), $action);
                }

                return view($view, compact(
                    DatabaseConstants::TABLE_PROPOSALS,
                    DatabaseConstants::TABLE_CUSTOMERS,
                    'status'
                ));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all()]);
                Log::error("$action error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function create(string|int $customer_id): View|JsonResponse|RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";

        return $this->measureProfile($action, function () use ($customer_id, $action) {
            Log::info("$action start", ['user' => Auth::id(), 'customer_id' => $customer_id]);
            $stepStart = microtime(true);

            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;

                if (($c = $this->guard(request(), 'create proposal', self::INDEX_ROUTE)) !== true) {
                    Log::warning("$action denied", ['user' => Auth::id()]);
                    return $c;
                }

                $creatorId = $user?->creatorId();
                $customFields = CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->where('module', 'proposal')->get();
                $proposalNumber = $user?->proposalNumberFormat($this->proposalNumber());
                $customers = Customer::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->pluck(UsersConstants::COL_NM, 'id')->prepend('Select Customer', '');
                $category = ProductServiceCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->where('type', 'income')->pluck('name', 'id')->prepend('Select Category', '');
                $productServices = ProductService::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->pluck('name', 'id')->prepend('--', '');

                $this->logExecutionTime($stepStart, 'fetch proposal data', 'completed');

                $view = ViewsConstants::PPS . '.create';
                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException(request(), new \RuntimeException('View not found: ' . $view), $action);
                }

                return view($view, compact(
                    DatabaseConstants::TABLE_CUSTOMERS,
                    'proposalNumber',
                    'productServices',
                    'category',
                    'customFields',
                    'customer_id'
                ));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'customer_id' => $customer_id]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'customer_id' => $customer_id]);
                return defaultUndefinedException(request(), $e, $action);
            }
        });
    }

    public function customer(Request $request): View|RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;

        return $this->measureProfile($action, function () use ($action, $request) {
            Log::info("[$action] start", ['id' => $request->id]);

            try {
                $findStart = microtime(true);
                $customer = Customer::findOrFail($request->id);
                $this->logExecutionTime($findStart, $action . '::findOrFail', 'completed');
                Log::info("[$action] customer found", ['id' => $request->id]);

                $view = ViewsConstants::PPS . '.customer_detail';
                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException($request, new \RuntimeException('View not found: ' . $view), $action);
                }

                return view($view, compact('customer'));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage(), 'id' => $request->id]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['id' => $request->id]);
    }

    public function product(Request $request): JsonResponse
    {
        $method = __METHOD__;

        return $this->measureProfile($method, function () use ($request, $method) {
            Log::info($method . ' start', ['product_id' => $request->product_id]);

            $stepStart = microtime(true);
            $product = ProductService::findOrFail($request->product_id);
            $this->logExecutionTime($stepStart, 'findProduct', 'completed');

            $stepStart = microtime(true);
            $unit = $product->unit?->name ?? ''; /** @phpstan-ignore property.nonObject */
            $this->logExecutionTime($stepStart, 'fetchUnit', 'completed');

            $stepStart = microtime(true);
            $taxRate = $product->tax_id ? $product->taxRate($product->tax_id) : 0;
            $this->logExecutionTime($stepStart, 'fetchTaxRate', 'completed');

            $stepStart = microtime(true);
            $taxes = $product->tax_id ? $product->tax($product->tax_id) : 0;
            $this->logExecutionTime($stepStart, 'fetchTaxes', 'completed');

            $stepStart = microtime(true);
            $sale = $product->sale_price;
            $total = $sale;
            $this->logExecutionTime($stepStart, 'calculateTotal', 'completed');

            return response()->json(compact('product', 'unit', 'taxRate', 'taxes', 'total'));
        }, ['product_id' => $request->product_id]);
    }

    public function store(Request $request): RedirectResponse
    {
        $function = __FUNCTION__;

        return $this->measureProfile($function, function () use ($request, $function) {
            $method = static::class . '::' . $function;
            $startAction = microtime(true);

            Log::info($method . ' start', ['user' => Auth::id()]);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startAction, $function . '::login', 'failed');
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $this->logExecutionTime($startAction, $function . '::login', 'completed');

            if (($c = $this->guard($request, 'create proposal', static::INDEX_ROUTE)) !== true) {
                Log::warning($method . ' denied', ['user' => Auth::id()]);
                return $c;
            }

            $startValidation = microtime(true);
            $data = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'issue_date'  => 'required|date',
                'category_id' => 'required|exists:product_service_categories,id',
                'items'       => 'required|array',
            ]);
            $this->logExecutionTime($startValidation, $function . '::validation', 'completed');

            DB::beginTransaction();
            try {
                $startCreate = microtime(true);
                $proposal = Proposal::create([
                    'proposal_id'                      => $this->proposalNumber(),
                    'customer_id'                      => $data['customer_id'],
                    'status'                           => 0,
                    'issue_date'                       => $data['issue_date'],
                    'category_id'                      => $data['category_id'],
                    DatabaseConstants::COL_TABLE_CREATOR   => $user?->creatorId(),
                ]);
                $this->logExecutionTime($startCreate, $function . '::createProposal', 'completed');

                CustomField::saveData($proposal, $request->customField);

                foreach ($data['items'] as $item) {
                    ProposalProduct::create([
                        'proposal_id' => $proposal->id,
                        'product_id'  => $item['item'],
                        'quantity'    => $item['quantity'],
                        'tax'         => $item['tax'],
                        'discount'    => $item['discount'],
                        'price'       => $item['price'],
                        'description' => $item['description'],
                    ]);
                }

                $settings = Utility::settingsById($user?->creatorId());
                $customer = Customer::find($proposal->customer_id);
                $notif = [
                    'proposal_number'     => $user?->proposalNumberFormat($proposal->proposal_id),
                    'user_name'           => $user?->name,
                    'customer_name'       => $customer->name,
                    'proposal_issue_date' => $proposal->issue_date,
                ];
                if (!empty($settings['twilio_proposal_notification'] ?? null)) {
                    Utility::sendTwilioMsg($request->contact, 'new_proposal', $notif);
                }

                DB::commit();
                $this->logExecutionTime($startCreate, $function . '::commit', 'completed');
                Log::info($method . ' success', ['proposal' => $proposal->id]);

                // Index route typically doesn’t take an ID
                return redirect()->route(static::INDEX_ROUTE)
                    ->with('success', __('Proposal successfully created.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                $timeError = microtime(true);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' error', ['err' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }

    public function edit(string $encId): View|RedirectResponse
    {
        $class = static::class;
        $function = __FUNCTION__;
        $action = "{$class}::{$function}";

        return $this->measureProfile($action, function () use ($encId, $action, $function) {
            Log::info("$action start", ['user' => Auth::id(), 'encId' => $encId]);
            $stepStart = microtime(true);

            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;

                if (($c = $this->guard(request(), 'edit proposal', self::INDEX_ROUTE)) !== true) {
                    Log::warning("$action denied", ['user' => Auth::id()]);
                    return $c;
                }

                $id = \Illuminate\Support\Facades\Crypt::decrypt($encId);
                $proposal = Proposal::findOrFail($id);
                if ($proposal->created_by !== $user?->creatorId())
                    return defaultPermissionDenial(request(), new \Exception('ownership'), $action);

                $proposalNumber = $user?->proposalNumberFormat($proposal->proposal_id);
                $creatorId = $user?->creatorId();

                $customers = Customer::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->pluck(UsersConstants::COL_NM, 'id');

                $category = ProductServiceCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->where('type', 'income')->pluck('name', 'id')->prepend('Select Category', '');

                $productServices = ProductService::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->pluck('name', 'id');

                $customFields = CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->where('module', 'proposal')->get();

                $items = $proposal->items->map(function ($it) {
                    $it->itemAmount = $it->quantity * $it->price;
                    $it->taxes = Utility::tax($it->tax);
                    return $it;
                });

                $this->logExecutionTime($stepStart, 'fetch proposal edit data', 'completed');

                $view = ViewsConstants::PPS . '.' . $function;
                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException(request(), new \RuntimeException('View not found: ' . $view), $action);
                }

                return view($view, compact(
                    DatabaseConstants::TABLE_CUSTOMERS,
                    'productServices',
                    'proposal',
                    'proposalNumber',
                    'category',
                    'customFields',
                    'items'
                ));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'encId' => $encId]);
                Log::error("$action error", ['err' => $e->getMessage()]);
                return defaultUndefinedException(request(), $e, $action);
            }
        });
    }

    // todo

    public function update(Request $request, Proposal $proposal): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $proposal) {
            Log::info("[$action] start", ['user' => Auth::id(), 'proposal' => $proposal->id]);

            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            $guardStart = microtime(true);
            if (($c = $this->guard($request, 'edit proposal', self::INDEX_ROUTE)) !== true) {
                Log::warning("[$action] update denied", ['user' => Auth::id()]);
                Log::debug("[$action] permission denied", ['user' => Auth::id()]);
                return $c;
            }
            $this->logExecutionTime($guardStart, $action . '::guard', 'completed');

            if ($proposal->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('ownership'), $action);

            $data = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'issue_date' => 'required|date',
                'category_id' => 'required|exists:product_service_categories,id',
                'items' => 'required|array',
            ]);

            $txStart = microtime(true);
            DB::beginTransaction();
            try {
                $proposalStart = microtime(true);
                $proposal->update([
                    'customer_id' => $data['customer_id'],
                    'issue_date' => $data['issue_date'],
                    'category_id' => $data['category_id'],
                ]);
                $this->logExecutionTime($proposalStart, $action . '::updateProposal', 'completed');

                CustomField::saveData($proposal, $request->customField);

                $itemsStart = microtime(true);
                foreach ($data['items'] as $item) {
                    $pp = ProposalProduct::find($item['id']) ?? new ProposalProduct(['proposal_id' => $proposal->id]);
                    $pp->fill([
                        'product_id' => $item['item'] ?? $pp->product_id,
                        'quantity' => $item['quantity'],
                        'tax' => $item['tax'],
                        'discount' => $item['discount'],
                        'price' => $item['price'],
                        'description' => $item['description'],
                    ])->save();
                }
                $this->logExecutionTime($itemsStart, $action . '::updateItems', 'completed');

                DB::commit();
                Log::info("[$action] success", ['proposal' => $proposal->id]);
                return redirect()->route(self::INDEX_ROUTE, $proposal->id)
                    ->with('success', __('Proposal successfully updated.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("[$action] error", ['err' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
            $this->logExecutionTime($txStart, $action . '::transaction', 'completed');
        }, ['proposal_id' => $proposal->id]);
    }

    public function show(string $encId): View|RedirectResponse
    {
        $function = __FUNCTION__;
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user' => Auth::id(), 'encId' => $encId]);
        return $this->measureProfile($method, function () use ($encId, $method) {
            $stepStart = microtime(true);
            if (($user = self::_checkLogin()) instanceof RedirectResponse)
                return $user;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (($c = $this->guard(request(), 'show proposal', self::INDEX_ROUTE)) !== true) {
                Log::warning('show denied', ['user' => Auth::id()]);
                return $c;
            }
            $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
            try {
                $stepStart = microtime(true);
                $id = Crypt::decrypt($encId);
                $proposal = Proposal::findOrFail($id);
                $this->logExecutionTime($stepStart, 'decryptAndFindProposal', 'completed');
                $stepStart = microtime(true);
                if ($proposal->created_by !== $user?->creatorId())
                    return defaultPermissionDenial(request(), new \Exception('ownership'), $method);
                $this->logExecutionTime($stepStart, 'checkOwnership', 'completed');
                $proposal->customField = CustomField::getData($proposal, 'proposal');
                $customFields = CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->where('module', 'proposal')->get();
                $this->logExecutionTime($stepStart, 'fetchCustomFields', 'completed');
                if (!ViewFacade::exists(ViewsConstants::PPS . '.view')) return defaultUndefinedException(request(), new \RuntimeException('View not found: ' . ViewsConstants::PPS . '.view'), $method);
                return view(ViewsConstants::PPS . '.view', [
                    'proposal' => $proposal,
                    'customer' => $proposal->customer,
                    'items' => $proposal->items,
                    'status' => Proposal::$statuses,
                    'customFields' => $customFields,
                ]);
            } catch (\Throwable $e) {
                Log::error($method . ' error', ['err' => $e->getMessage()]);
                return defaultUndefinedException(request(), $e, $method);
            }
        }, ['encId' => $encId]);
    }

    public function destroy(Proposal $proposal): RedirectResponse
    {
        $function = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($function, function () use ($proposal, $function, $method) {
            $method = static::class . '::' . $function;
            $startAction = microtime(true);
            Log::info($method . ' start', ['user' => Auth::id(), 'proposal' => $proposal->id]);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startAction, $function . '::login', 'failed');
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $this->logExecutionTime($startAction, $function . '::login', 'completed');
            if (($c = $this->guard(request(), 'delete proposal', static::INDEX_ROUTE)) !== true) {
                Log::warning($method . ' denied', ['user' => Auth::id()]);
                return $c;
            }
            if ($proposal->created_by !== $user?->creatorId()) {
                Log::warning($method . ' ownership denied', ['user' => Auth::id(), 'proposal' => $proposal->id]);
                return defaultPermissionDenial(request(), new \Exception('ownership'), $method);
            }
            DB::transaction(function () use ($proposal, $function, $method) {
                $startDelete = microtime(true);
                $proposal->delete();
                ProposalProduct::where('proposal_id', $proposal->id)->delete();
                $this->logExecutionTime($startDelete, $function . '::delete', 'completed');
                Log::info($method . ' deleted', ['proposal' => $proposal->id]);
            });
            return redirect()->route(static::INDEX_ROUTE)
                ->with('success', __('Proposal successfully deleted.'));
        }, func_get_args());
    }

    public const PRD_DST = 'productDestroy';
    public function productDestroy(Request $request): RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $action) {
            Log::info("$action start", ['user' => Auth::id(), 'id' => $request->id]);
            $stepStart = microtime(true);
            try {
                if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
                if (($c = $this->guard($request, 'delete proposal product', self::INDEX_ROUTE)) !== true) {
                    Log::warning("$action denied", ['user' => Auth::id()]);
                    return $c;
                }
                ProposalProduct::where('id', $request->id)->delete();
                $this->logExecutionTime($stepStart, 'delete proposal product', 'completed');
                Log::info("$action deleted", ['product' => $request->id]);
                return redirect()->back()->with('success', __('Proposal product successfully deleted.'));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all()]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'product' => $request->id]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public const CTM_PPS = 'customerProposal';
    public function customerProposal(Request $request): View|RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            Log::info("[$action] start", ['user' => Auth::id()]);

            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            $guardStart = microtime(true);
            $c = $this->guard($request, 'manage customer proposal', self::INDEX_ROUTE);
            $this->logExecutionTime($guardStart, $action . '::guard', 'completed');
            if ($c) {
                Log::warning("[$action] customerProposal denied", ['user' => Auth::id()]);
                Log::debug("[$action] permission denied", ['user' => Auth::id()]);
                return $c;
            }

            try {
                $queryStart = microtime(true);
                $query = Proposal::where('customer_id', Auth::id())
                    ->where('status', '!=', 0)
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId());
                $this->logExecutionTime($queryStart, $action . '::query', 'completed');

                if ($request->filled('issue_date')) {
                    $range = explode(' - ', $request->issue_date);
                    $query->whereBetween('issue_date', $range);
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }

                $proposalsStart = microtime(true);
                $proposals = $query->get();
                $this->logExecutionTime($proposalsStart, $action . '::getProposals', 'completed');
                if (!ViewFacade::exists(ViewsConstants::PPS . '.index')) {
                    return defaultUndefinedException($request, new \RuntimeException('View not found: ' . ViewsConstants::PPS . '.index'), $action);
                }
                return view(ViewsConstants::PPS . '.index', [
                    DatabaseConstants::TABLE_PROPOSALS => $proposals,
                    'status' => Proposal::$statuses,
                ]);
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['user_id' => Auth::id()]);
    }

    public const CTM_PPS_SHW = 'customerProposalShow';
    public function customerProposalShow(string $encId): View|RedirectResponse
    {
        $function = __FUNCTION__;
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user' => Auth::id(), 'encId' => $encId]);
        return $this->measureProfile($method, function () use ($encId, $method) {
            $stepStart = microtime(true);
            if (($user = self::_checkLogin()) instanceof RedirectResponse)
                return $user;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (($c = $this->guard(request(), 'show proposal', self::INDEX_ROUTE)) !== true) {
                Log::warning('customerShow denied', ['user' => Auth::id()]);
                return $c;
            }
            $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
            try {
                $stepStart = microtime(true);
                $id = Crypt::decrypt($encId);
                $proposal = Proposal::findOrFail($id);
                $this->logExecutionTime($stepStart, 'decryptAndFindProposal', 'completed');
                $stepStart = microtime(true);
                if ($proposal->created_by !== $user?->creatorId())
                    return defaultPermissionDenial(request(), new \Exception('ownership'), $method);
                $this->logExecutionTime($stepStart, 'checkOwnership', 'completed');
                if (!ViewFacade::exists(ViewsConstants::PPS . '.view')) return defaultUndefinedException(request(), new \RuntimeException('View not found: ' . ViewsConstants::PPS . '.view'), $method);
                return view(ViewsConstants::PPS . '.view', [
                    'proposal' => $proposal,
                    'customer' => $proposal->customer,
                    'items' => $proposal->items,
                ]);
            } catch (\Throwable $e) {
                Log::error($method . ' error', ['err' => $e->getMessage()]);
                return defaultUndefinedException(request(), $e, $method);
            }
        }, ['encId' => $encId]);
    }

    public function sent(string $encId): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($encId, $function) {
            $method = static::class . '::' . $function;
            $startAction = microtime(true);
            Log::info($method . ' start', ['user' => Auth::id(), 'encId' => $encId]);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startAction, $function . '::login', 'failed');
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $this->logExecutionTime($startAction, $function . '::login', 'completed');
            if (($c = $this->guard(request(), 'send proposal', static::INDEX_ROUTE)) !== true) {
                Log::warning($method . ' denied', ['user' => Auth::id()]);
                return $c;
            }
            DB::beginTransaction();
            try {
                $startProcessing = microtime(true);
                $id = (int)$encId;
                $proposal = Proposal::findOrFail($id);
                $proposal->update(['send_date' => now()->toDateString(), 'status' => 1]);
                $customer = Customer::find($proposal->customer_id);
                $url = route(ViewsConstants::PPS . '.pdf', Crypt::encrypt($proposal->id));
                $payload = [
                    'proposal_name' => $customer->name ?? '',
                    'proposal_number' => $user?->proposalNumberFormat($proposal->proposal_id),
                    'proposal_url' => $url,
                ];
                $settings = Utility::settingsById($user?->creatorId());
                $msg = __('Proposal successfully sent.');
                if ($settings['proposal_sent'] ?? false) {
                    $startEmail = microtime(true);
                    $resp = Utility::sendEmailTemplate('proposal_sent', [$customer->id => $customer->email], $payload);
                    $this->logExecutionTime($startEmail, $function . '::sendEmail', 'completed');
                    if ($resp['is_success'] === false && $resp['error'] ?? false)
                        $msg .= "<br><span class='text-danger'>{$resp['error']}</span>";
                }
                DB::commit();
                $this->logExecutionTime($startProcessing, $function . '::processing', 'completed');
                Log::info($method . ' success', ['proposal' => $proposal->id]);
                return redirect()->back()->with('success', $msg);
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                DB::rollBack();
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' error', ['err' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException(request(), $e, $method);
            }
        }, func_get_args());
    }

    public function resent(string $encId): RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($encId, $action) {
            Log::info("$action start", ['user' => Auth::id(), 'encId' => $encId]);
            $stepStart = microtime(true);
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if (($c = $this->guard(request(), 'send proposal', self::INDEX_ROUTE)) !== true) {
                    Log::warning("$action denied", ['user' => Auth::id()]);
                    return $c;
                }
                $id = (int)$encId;
                $proposal = Proposal::findOrFail($id);
                $customer = Customer::find($proposal->customer_id);
                $url = route(ViewsConstants::PPS . '.pdf', \Illuminate\Support\Facades\Crypt::encrypt($proposal->id));
                $payload = [
                    'proposal_name' => $customer->name ?? '',
                    'proposal_number' => $user?->proposalNumberFormat($proposal->proposal_id),
                    'proposal_url' => $url,
                ];
                $settings = Utility::settingsById($user?->creatorId());
                if ($settings['proposal_sent'] ?? false)
                    Utility::sendEmailTemplate('proposal_sent', [$customer->id => $customer->email], $payload);
                $this->logExecutionTime($stepStart, 'send proposal', 'completed');
                Log::info("$action success", ['proposal' => $proposal->id]);
                return redirect()->back()->with('success', __('Proposal successfully sent.'));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'encId' => $encId]);
                Log::error("$action error", ['err' => $e->getMessage()]);
                return defaultUndefinedException(request(), $e, $action);
            }
        });
    }

    public const SHP_DSP = 'shippingDisplay';
    public function shippingDisplay(Request $request, string|int $id): RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $id, $action) {
            Log::info("$action called", ['user' => Auth::id(), 'proposal' => $id, 'display' => $request->is_display]);
            $stepStart = microtime(true);
            try {
                if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
                $proposal = Proposal::findOrFail($id);
                $proposal->shipping_display = $request->is_display === 'true' ? '1' : '0';
                $proposal->save();
                $this->logExecutionTime($stepStart, 'update shipping display', 'completed');
                Log::info("$action updated", ['proposal' => $id, 'shipping_display' => $proposal->shipping_display]);
                return redirect()->back()->with('success', __('Shipping address status successfully changed.'));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'proposal' => $id]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'proposal' => $id]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function duplicate(string|int $id): RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($id, $action) {
            Log::info("$action called", ['user' => Auth::id(), 'proposal' => $id]);
            $stepStart = microtime(true);
            try {
                if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
                if (($c = $this->guard(request(), 'duplicate proposal', self::INDEX_ROUTE)) !== true) return $c;
                $result = DB::transaction(function () use ($id, $action) {
                    $orig = Proposal::findOrFail($id);
                    $dup = $orig->replicate(['proposal_id', 'issue_date', 'send_date', 'status']);
                    $dup->proposal_id = $this->proposalNumber();
                    $dup->issue_date = now()->toDateString();
                    $dup->send_date = null;
                    $dup->status = 0;
                    $dup->save();
                    foreach ($orig->items as $item)
                        ProposalProduct::create([
                            'proposal_id' => $dup->id,
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'tax' => $item->tax,
                            'discount' => $item->discount,
                            'price' => $item->price,
                        ]);
                    Log::info("$action duplicated", ['orig' => $id, 'dup' => $dup->id]);
                    return redirect()->back()->with('success', __('Proposal duplicated successfully.'));
                });
                $this->logExecutionTime($stepStart, 'duplicate proposal', 'completed');
                return $result;
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => request()->all(), 'proposal' => $id]);
                Log::error("$action error", ['err' => $e->getMessage(), 'proposal' => $id]);
                return defaultUndefinedException(request(), $e, $action);
            }
        });
    }

    public function convert(string|int $id): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $id) {
            Log::info("[$action] called", ['user' => Auth::id(), 'proposal' => $id]);

            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof \Illuminate\Http\RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            $guardStart = microtime(true);
            $c = $this->guard(request(), 'convert invoice', self::INDEX_ROUTE);
            $this->logExecutionTime($guardStart, $action . '::guard', 'completed');
            if ($c) return $c;

            try {
                $txStart = microtime(true);
                return DB::transaction(function () use ($id, $user, $action) {
                    $findStart = microtime(true);
                    $prop = Proposal::findOrFail($id);
                    $this->logExecutionTime($findStart, $action . '::findProposal', 'completed');

                    $updateStart = microtime(true);
                    $prop->update(['is_convert' => 1]);
                    $this->logExecutionTime($updateStart, $action . '::updateProposal', 'completed');

                    $invStart = microtime(true);
                    $inv = Invoice::create([
                        'invoice_id' => $this->invoiceNumber(),
                        'customer_id' => $prop->customer_id,
                        'issue_date' => now()->toDateString(),
                        'due_date' => now()->toDateString(),
                        'send_date' => null,
                        'category_id' => $prop->category_id,
                        'status' => 0,
                        DatabaseConstants::COL_TABLE_CREATOR => $prop->created_by,
                    ]);
                    $this->logExecutionTime($invStart, $action . '::createInvoice', 'completed');

                    $prop->update(['converted_invoice_id' => $inv->id]);

                    foreach ($prop->items as $item) {
                        $invProdStart = microtime(true);
                        $invProd = $item->replicate(['id', 'proposal_id']);
                        $invProd->invoice_id = $inv->id;
                        $invProd->save();
                        $this->logExecutionTime($invProdStart, $action . '::replicateItem', 'completed');

                        Utility::totalQuantity('minus', $invProd->quantity, $invProd->product_id);
                        Utility::addProductStock(
                            $invProd->product_id,
                            $invProd->quantity,
                            'invoice',
                            $invProd->quantity . ' quantity sold in ' . $user?->proposalNumberFormat($prop->proposal_id)
                                . ' Proposal => ' . $user?->invoiceNumberFormat($inv->invoice_id),
                            $inv->id
                        );
                    }

                    Log::info("[$action] converted", ['proposal' => $id, 'invoice' => $inv->id]);
                    return redirect()->back()->with('success', __('Proposal converted to invoice successfully.'));
                });
            } catch (\Throwable $e) {
                Log::error("[$action] error", ['err' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException(request(), $e, $action);
            }

            $this->logExecutionTime($txStart, $action . '::transaction', 'completed');
        }, ['id' => $id]);
    }

    // todo

    public const STT_CHG = 'statusChange';
    public function statusChange(Request $request, string|int $id): RedirectResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user' => Auth::id(), 'proposal' => $id, 'status' => $request->status]);
        return $this->measureProfile(function () use ($request, $id) {
            $stepStart = microtime(true);
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            $proposal = Proposal::findOrFail($id);
            $proposal->status = $request->status;
            $proposal->save();
            $this->logExecutionTime($stepStart, 'updateProposalStatus', 'completed');
            return redirect()->back()->with('success', __('Proposal status changed successfully.'));
        }, ['user' => Auth::id(), 'proposal' => $id, 'status' => $request->status]);
    }

    public const PV_PPS = 'previewProposal';
    public function previewProposal(string $template, string $color): View
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($template, $color, $function) {
            $method = static::class . '::' . $function;
            $startAction = microtime(true);
            Log::info($method . ' called', ['user' => Auth::id(), 'template' => $template, 'color' => $color]);
            if (($userOrRedirect = static::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) {
                $this->logExecutionTime($startAction, $function . '::login', 'failed');
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $this->logExecutionTime($startAction, $function . '::login', 'completed');
            $settings = Utility::settingsById($user?->creatorId());
            $preview = true;
            $proposal = new Proposal();
            $proposal->proposal_id = 1;
            $proposal->issue_date = now()->toDateTimeString();
            $proposal->due_date = now()->toDateTimeString();
            $totalTaxPrice = 0;
            $taxesData = [];
            $proposal->itemData = collect(range(1, 3))->map(function ($i) use (&$totalTaxPrice, &$taxesData) {
                $item = (object)[
                    'name' => "Item $i",
                    'quantity' => 1,
                    'tax' => 5,
                    'discount' => 50,
                    'price' => 100,
                    'unit' => 1
                ];
                $item->itemTax = collect(['Tax 1', 'Tax 2'])->map(function ($tax, $k) use (&$totalTaxPrice, &$taxesData) {
                    $price = 10;
                    $totalTaxPrice += $price;
                    $taxesData["Tax $k"] = ($taxesData["Tax $k"] ?? 0) + $price;
                    return ['name' => "Tax $k", 'rate' => '10 %', 'price' => '$10', 'tax_price' => 10];
                })->toArray();
                return $item;
            })->toArray();
            $proposal->totalTaxPrice = $totalTaxPrice;
            $proposal->totalQuantity = 3;
            $proposal->totalRate = 300;
            $proposal->totalDiscount = 10;
            $proposal->taxesData = $taxesData;
            $proposal->created_by = $user?->creatorId();
            $proposal->customField = [];
            $customFields = [];
            $color = "#$color";
            $fontColor = Utility::getFontColor($color);
            $logoPath = Utility::getFile('proposal_logo/') . ($settings['proposal_logo'] ?? '');
            $img = $logoPath ?: asset('uploads/logo/' . ($settings[SettingsConstants::CPN_LG_DK] ?? SettingsConstants::CPN_LG_DK_DEF));
            $this->logExecutionTime($startAction, $function . '::proposalData', 'completed');
            if (!ViewFacade::exists(ViewsConstants::PPS . ".templates.$template")) {
                return defaultUndefinedException(request(), new \RuntimeException('View not found: ' . ViewsConstants::PPS . ".templates.$template"), $method);
            }
            return view(ViewsConstants::PPS . ".templates.$template", compact(
                'proposal',
                'preview',
                'color',
                'img',
                'settings',
                'fontColor',
                'customFields'
            ));
        }, func_get_args());
    }

    public const PPS_LNK = 'proposalLink';
    public function proposalLink(string $encId): View|RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($encId, $action) {
            Log::info("$action called", ['user' => Auth::id(), 'encId' => $encId]);
            $stepStart = microtime(true);
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                $id = \Illuminate\Support\Facades\Crypt::decrypt($encId);
                $proposal = Proposal::findOrFail($id);
                if ($proposal->created_by !== $user?->creatorId())
                    return defaultPermissionDenial(request(), new \Exception('ownership'), $action);
                $settings = Utility::settingsById($proposal->created_by);
                $logoPath = Utility::getFile('proposal_logo/') . ($settings['proposal_logo'] ?? '');
                $img = $logoPath ?: asset('uploads/logo/' . ($settings[SettingsConstants::CPN_LG_DK] ?? SettingsConstants::CPN_LG_DK_DEF));
                $this->logExecutionTime($stepStart, 'view proposal link', 'completed');
                if (!ViewFacade::exists(ViewsConstants::PPS . '.customer_proposal')) return defaultUndefinedException(request(), new \RuntimeException('View not found: ' . ViewsConstants::PPS . '.customer_proposal'), $action);
                return view(ViewsConstants::PPS . '.customer_proposal', [
                    'proposal' => $proposal,
                    'customer' => $proposal->customer,
                    'items' => $proposal->items, /** @phpstan-ignore property.protected */
                    'customFields' => CustomField::where('module', 'proposal')->get(),
                    'status' => Proposal::$statuses,
                    'user' => User::find($proposal->created_by),
                    'img' => $img,
                ]);
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'encId' => $encId]);
                Log::error("$action error", ['err' => $e->getMessage(), 'encId' => $encId]);
                return defaultUndefinedException(request(), $e, $action);
            }
        });
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($action) {
            Log::info("$action called", ['user' => Auth::id()]);
            $stepStart = microtime(true);
            try {
                $file = 'proposal_' . now()->format('Y-m-d_H_i_s') . '.xlsx';
                $resp = Excel::download(new ProposalExport(), $file);
                ob_end_clean();
                $this->logExecutionTime($stepStart, 'export proposal', 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e]);
                Log::error("$action error", ['err' => $e->getMessage()]);
                return defaultUndefinedException(request(), $e, $action);
            }
        });
    }

    public function items(Request $request): JsonResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $action) {
            Log::info("$action called", ['user' => Auth::id(), 'payload' => $request->all()]);
            $stepStart = microtime(true);
            try {
                $item = ProposalProduct::where('proposal_id', $request->proposal_id)
                    ->where('product_id', $request->product_id)->first();
                $this->logExecutionTime($stepStart, 'fetch proposal items', 'completed');
                return response()->json($item);
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all()]);
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function proposal(Request $request, string $encId): View|RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $encId) {
            Log::info("[$action] start", ['encId' => $encId, UsersConstants::COL_USER_ID => Auth::id()]);

            $checkStart = microtime(true);
            $user = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($user instanceof RedirectResponse) return $user;

            $guardStart = microtime(true);
            $redirect = $this->guard($request, 'show proposal', self::INDEX_ROUTE);
            $this->logExecutionTime($guardStart, $action . '::guard', 'completed');
            if ($redirect) {
                Log::warning("[$action] permission denied", ['user' => Auth::id()]);
                return $redirect;
            }

            try {
                $decryptStart = microtime(true);
                $id = Crypt::decrypt($encId);
                $this->logExecutionTime($decryptStart, $action . '::decrypt', 'completed');

                $proposalStart = microtime(true);
                $proposal = Proposal::with('items.product', 'customer')
                    ->where('id', $id)
                    ->firstOrFail();
                $this->logExecutionTime($proposalStart, $action . '::findProposal', 'completed');

                if ($proposal->created_by !== $user?->creatorId()) {
                    Log::warning("[$action] ownership denied", ['user_id' => $user?->id, 'proposal_id' => $id]);
                    return defaultPermissionDenial($request, null, $action);
                }

                $settingsStart = microtime(true);
                $settings = Utility::settingsById($proposal->created_by);
                $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');

                $itemsStart = microtime(true);
                $items = [];
                $totals = ['tax' => 0, 'quantity' => 0, 'rate' => 0, 'discount' => 0];
                $taxesData = [];
                foreach ($proposal->items as $it) {
                    $prod = $it->product;
                    $name = $prod->name ?? '';
                    $unit = $prod->unit_id ?? '';
                    $taxes = Utility::tax($it->tax);
                    $itemTaxes = [];
                    foreach ($taxes as $t) {
                        $price = Utility::taxRate($t->rate, $it->price, $it->quantity, $it->discount);
                        $totals['tax'] += $price;
                        $taxesData[$t->name] = ($taxesData[$t->name] ?? 0) + $price;
                        $itemTaxes[] = [
                            'name' => $t->name,
                            'rate' => "{$t->rate}%",
                            'price' => Utility::priceFormat($settings, $price),
                            'tax_price' => $price,
                        ];
                    }
                    $items[] = (object)[
                        'name' => $name,
                        'quantity' => $it->quantity,
                        'unit' => $unit,
                        'price' => $it->price,
                        'discount' => $it->discount,
                        'description' => $it->description,
                        'itemTax' => $itemTaxes,
                    ];
                    $totals['quantity'] += $it->quantity;
                    $totals['rate'] += $it->price;
                    $totals['discount'] += $it->discount;
                }
                $this->logExecutionTime($itemsStart, $action . '::processItems', 'completed');

                $settingsData = Utility::settingsById($proposal->created_by);
                $logoFile = $settingsData['proposal_logo'] ?? null;
                $img = $logoFile
                    ? Utility::getFile('proposal_logo/') . $logoFile
                    : asset('uploads/logo/' . ($settings[SettingsConstants::CPN_LG_DK] ?? SettingsConstants::CPN_LG_DK_DEF));
                $color = "#{$settings['proposal_color']}";
                $fontColor = Utility::getFontColor($color);

                Log::info("[$action] rendering proposal view", ['proposal_id' => $id]);
                if (!ViewFacade::exists(ViewsConstants::PPS . ".templates.{$settings[BillsConstants::COL_PPS_TMP]}")) {
                    return defaultUndefinedException($request, new \RuntimeException('View not found: ' . ViewsConstants::PPS . ".templates.{$settings[BillsConstants::COL_PPS_TMP]}"), $action);
                }
                return view(
                    ViewsConstants::PPS . ".templates.{$settings[BillsConstants::COL_PPS_TMP]}",
                    compact('proposal', 'items', 'totals', 'taxesData', 'settings', 'img', 'color', 'fontColor')
                );
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['err' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }

            $this->logExecutionTime($proposalStart, $action . '::total', 'completed');
        }, ['encId' => $encId]);
    }

    public const SV_PPS_TMP = 'saveProposalTemplateSettings';
    public function saveProposalTemplateSettings(Request $request): RedirectResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['input' => $request->except('proposal_logo')]);
        return $this->measureProfile($method, function () use ($request, $method) {
            $stepStart = microtime(true);
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (!($redirect = $this->guard($request, PermissionsConstants::MNG_PPS, self::INDEX_ROUTE)) !== true) {
                Log::warning($method . ' permission denied', ['user_id' => $user?->id]);
                return $redirect;
            }
            $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
            $data = $request->except('_token');
            $data['proposal_color'] = $data[BillsConstants::COL_PPS_TMP] && empty($data['proposal_color'])
                ? 'ffffff'
                : ($data['proposal_color'] ?? 'ffffff');
            $stepStart = microtime(true);
            if ($file = $request->file('proposal_logo')) {
                $name = "{$user?->id}_proposal_logo.png";
                $dir = 'proposal_logo/';
                $upload = Utility::uploadFile($request, 'proposal_logo', $name, $dir, ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF]);
                $this->logExecutionTime($stepStart, 'uploadLogo', 'completed');
                if ($upload['flag'] === 0) {
                    Log::warning($method . ' upload failed', ['msg' => $upload['msg']]);
                    return redirect()->back()->with('error', __($upload['msg']));
                }
                $data['proposal_logo'] = $name;
                Log::info($method . ' logo uploaded', ['file' => $name]);
            }
            $stepStart = microtime(true);
            try {
                DB::transaction(function () use ($data, $user) {
                    foreach ($data as $key => $val) {
                        DB::insert(
                            'INSERT INTO ' . DatabaseConstants::TABLE_SETTINGS . ' (`value`,`name`,`' . DatabaseConstants::COL_TABLE_CREATOR . '`)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                            [$val, $key, $user?->creatorId()]
                        );
                        Log::info('setting saved', ['name' => $key]);
                    }
                });
                $this->logExecutionTime($stepStart, 'saveSettings', 'completed');
                Log::info($method . ' completed');
                return redirect()->back()->with('success', __('Proposal settings updated successfully'));
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['input' => $request->except('proposal_logo')]);
    }

    public const IV_LK = 'invoiceLink';
    public function invoiceLink(Request $request, string $encId): View|RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $encId, $function) {
            $method = static::class . '::' . $function;
            $startAction = microtime(true);
            Log::info($method . ' start', ['encId' => $encId, UsersConstants::COL_USER_ID => Auth::id()]);
            if (($user = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startAction, $function . '::login', 'failed');
                return $user;
            }
            if (($redirect = $this->guard($request, 'show proposal', static::INDEX_ROUTE)) !== true) {
                Log::warning($method . ' permission denied', [UsersConstants::COL_USER_ID => $user?->id]);
                $this->logExecutionTime($startAction, $function . '::guard', 'failed');
                return $redirect;
            }
            $this->logExecutionTime($startAction, $function . '::guard', 'completed');
            try {
                $startDecrypt = microtime(true);
                $id = Crypt::decrypt($encId);
                $this->logExecutionTime($startDecrypt, $function . '::decrypt', 'completed');
                $startFetch = microtime(true);
                $proposal = Proposal::with('items', 'customer')->findOrFail($id);
                $this->logExecutionTime($startFetch, $function . '::fetchProposal', 'completed');
                if ($proposal->created_by !== $user?->creatorId()) {
                    Log::warning($method . ' ownership denied', [
                        UsersConstants::COL_USER_ID => $user?->id,
                        'proposal_id' => $id
                    ]);
                    return defaultPermissionDenial($request, null, $method);
                }
                $settings = Utility::settingsById($user?->creatorId());
                $img = Utility::getFile('proposal_logo/') . ($settings['proposal_logo'] ?? '');
                $status = Proposal::$statuses;
                $customFields = CustomField::where('module', 'proposal')->get();
                $this->logExecutionTime($startAction, $function . '::view', 'completed');
                return view(ViewsConstants::PPS . '.customer_proposal', compact('proposal', 'status', 'customFields', 'settings', 'img'));
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' failed', ['err' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }

    private function proposalNumber(): int|string|RedirectResponse
    {
        Log::debug(__METHOD__ . ' called', ['user' => Auth::id()]);
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $latest = Proposal::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
            ->latest()->first();
        return $latest ? (is_numeric($latest->proposal_id) ? $latest->proposal_id + 1 : $latest->proposal_id) : 0;
    }

    private function invoiceNumber(): int|string|RedirectResponse
    {
        Log::debug(__METHOD__ . ' called', ['user' => Auth::id()]);
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $latest = Invoice::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
            ->latest()->first();
        return $latest ? (is_numeric($latest->invoice_id) ? $latest->invoice_id + 1 : $latest->invoice_id) : 1;
    }
}
