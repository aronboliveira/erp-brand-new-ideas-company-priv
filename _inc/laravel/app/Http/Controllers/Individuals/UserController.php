<?php

namespace App\Http\Controllers\Individuals;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    PlansConstants as PLC,
    ProjectsConstants as PJC,
    SettingsConstants as SC,
    UsersConstants as UC,
    ViewsConstants as VW
};
use App\Http\Controllers\Abstracts\Controller as AppController;
use App\Models\{
    CustomField,
    Employee,
    ExperienceCertificate,
    GeneratedOfferLetter,
    JoiningLetter,
    LoginDetail,
    Noc,
    Order,
    Plan,
    Role,
    User,
    UserToDo,
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Crypt,
    DB,
    File,
    Hash,
    Log,
    Validator,
    View as ViewFacade
};
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\DefinesResourceActions;
class UserController extends AppController
{
	use DefinesResourceActions;

    use ChecksLogin, ChecksPermissions;
    private const SINGULAR = 'user';

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::MNG_USER, VW::USR . '.index');
                $user = $request->user();
                Log::debug("$cls::$action start", [UC::COL_USER_ID => $user?->id]);
                $query = User::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->with('current_plan');
                $users = $user[UC::COL_TP] === PMC::SA
                    ? $query->where(UC::COL_TP, PMC::CPN)->get()
                    : $query->where(UC::COL_TP, '!=', PMC::CL)->get();
                // Pre-compute counts in 3 batch queries instead of N×3 (N+1 fix)
                $userIds = $users->pluck('id');
                $userCounts = $userIds->isEmpty() ? collect() : User::whereIn(DC::COL_TABLE_CREATOR, $userIds)
                    ->selectRaw(DC::COL_TABLE_CREATOR . ' as cid, count(*) as cnt')
                    ->groupBy(DC::COL_TABLE_CREATOR)->pluck('cnt', 'cid');
                $customerCounts = $userIds->isEmpty() ? collect() : \App\Models\Customer::whereIn(DC::COL_TABLE_CREATOR, $userIds)
                    ->selectRaw(DC::COL_TABLE_CREATOR . ' as cid, count(*) as cnt')
                    ->groupBy(DC::COL_TABLE_CREATOR)->pluck('cnt', 'cid');
                $vendorCounts = $userIds->isEmpty() ? collect() : \App\Models\Vendor::whereIn(DC::COL_TABLE_CREATOR, $userIds)
                    ->selectRaw(DC::COL_TABLE_CREATOR . ' as cid, count(*) as cnt')
                    ->groupBy(DC::COL_TABLE_CREATOR)->pluck('cnt', 'cid');
                $view = VW::USR . '.' . $action;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
                return ViewFacade::make($view, compact(DC::TABLE_USERS, 'userCounts', 'customerCounts', 'vendorCounts'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public function create(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::CR_USER, VW::USR . '.index');
                $customFields = CustomField::where(DC::COL_TABLE_CREATOR, $request->user()->creatorId())->where('module', self::SINGULAR)->get();
                $roles = Role::where(DC::COL_TABLE_CREATOR, $request->user()->creatorId())->where('name', '!=', PMC::CL)->pluck('name', 'id');
                $view = VW::USR . '.' . $action;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
                return ViewFacade::make($view, compact('roles', 'customFields'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::CR_USER, VW::USR . '.index');
                $rules = [
                    UC::COL_NM => 'required|max:120',
                    UC::COL_EM => 'required|email|unique:users',
                    UC::COL_PW => 'required|min:6'
                ];
                if ($request->user()[UC::COL_TP] !== PMC::SA) $rules['role'] = 'required';
                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());

                $psw = $request->input('password');
                $data = [
                    UC::COL_NM           => $request->input(UC::COL_NM),
                    UC::COL_EM           => $request->input(UC::COL_EM),
                    UC::COL_PW           => Hash::make($psw),
                    UC::COL_TP           => $request->user()[UC::COL_TP] === PMC::SA
                        ? PMC::CPN
                        : Role::findById($request->input('role'))->name,
                    UC::COL_LG           => DB::table(DC::TABLE_SETTINGS)
                        ->where(UC::COL_NM, SC::DEF_LNG)
                        ->where(DC::COL_TABLE_CREATOR, $request->user()->creatorId())
                        ->value('value') ?: DC::DEFAULT_LANG,
                    DC::COL_TABLE_CREATOR => $request->user()->creatorId(),
                    UC::COL_U_AT         => now(),
                ];

                if ($request->user()[UC::COL_TP] === PMC::SA) {
                    $data['plan'] = Plan::first()->id;
                    $user = User::create($data);
                    $user?->assignRole(Role::findByName(PMC::CPN));
                    $initializers = [
                        [$user,                   User::USR_DEF_DT_REG],
                        [$user,                   User::USR_WA_REG],
                        [$user,                   User::USR_DEF_BA],
                        [Utility::class,          Utility::COA_TP_DT],
                        [Utility::class,          Utility::COA_DATA1],
                        [Utility::class,          Utility::PPL_LD_DL_STG],
                        [Utility::class,          Utility::PRJ_TSK_STGS],
                        [Utility::class,          'labels'],
                        [Utility::class,          'sources'],
                        [Utility::class,          Utility::JB_STG],
                        [GeneratedOfferLetter::class, GeneratedOfferLetter::DEF_OFL_REG],
                        [ExperienceCertificate::class, ExperienceCertificate::DEF_EXP_CRT_REG],
                        [JoiningLetter::class,        JoiningLetter::DEF_JG_LT_REG],
                        [Noc::class,                  Noc::DEF_NOC_CRT_REG],
                    ];
                    foreach ($initializers as $initializer) call_user_func_array($initializer, [$user?->id]);
                } else {
                    $creator   = $request->user()->creatorId();
                    $existing  = User::find($creator);
                    $totalUsers = $existing->countUsers();
                    $plan      = Plan::find($existing->plan);
                    if ($totalUsers >= $plan[PLC::COL_MAX_U] && $plan[PLC::COL_MAX_U] !== -1)
                        return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
                    $user = User::create($data);
                    $role = Role::findById($request->input('role'));
                    $user?->assignRole($role);
                    Utility::employeeDetails($user?->id, $creator);
                }

                if (Utility::settings()['new_user'] ?? false)
                    Utility::sendEmailTemplate('new_user', [$user?->email], ['email' => $user?->email, 'password' => $psw]);

                return redirect()->route(VW::USR . '.index')->with('success', __('User successfully created.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public function show(): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () {
            return redirect()->route(VW::USR . '.index');
        });
    }

    public function edit(Request $request, int|string $id)
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::ED_USER, VW::USR . '.index');
                $userDetail   = User::findOrFail($id);
                $customFields = CustomField::getData($userDetail, self::SINGULAR);
                $roles = Role::where(DC::COL_TABLE_CREATOR, $request->user()->creatorId())->where('name', '!=', PMC::CL)->pluck('name', 'id');
                $view = VW::USR . '.' . $action;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
                return ViewFacade::make($view, compact('userDetail', DC::TABLE_ROLES, 'customFields'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::ED_USER, VW::USR . '.index');
                $rules = ['name' => 'required|max:120', 'email' => "required|email|unique:users,email,$id"];
                if ($request->user()[UC::COL_TP] !== PMC::SA) $rules['role'] = 'required';
                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());

                $userDetail = User::findOrFail($id);
                $input = $request->only(['name', 'email']);
                if ($request->user()[UC::COL_TP] !== PMC::SA) {
                    $role = Role::findById($request->input('role'));
                    $input[UC::COL_TP] = $role->name;
                } else {
                    $role = Role::findByName(PMC::CPN);
                    $input[UC::COL_TP] = $role->name;
                }
                $userDetail->fill($input)->save();
                CustomField::saveData($userDetail, $request->input('customField', []));
                $userDetail->roles()->sync([$role->id]);

                return redirect()->route(VW::USR . '.index')->with('success', __('User successfully updated.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public function destroy(Request $request, int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::DEL_USER, VW::USR . '.index');
                $user = User::findOrFail($id);
                if ($request->user()[UC::COL_TP] === PMC::SA) {
                    $user->delete_status = $user?->delete_status ? 0 : 1;
                    $user?->save();
                } elseif ($request->user()[UC::COL_TP] === PMC::CPN) {
                    Employee::where(UC::COL_USER_ID, $user?->id)->delete();
                    $user?->delete();
                }
                return redirect()->route(VW::USR . '.index')->with('success', __('User successfully deleted.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const PRF = 'profile';
    public function profile(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::MNG_USER, VW::USR . '.index');
                $userDetail = $request->user();
                $userDetail->customField = CustomField::getData($userDetail, self::SINGULAR);
                $customFields = CustomField::where(DC::COL_TABLE_CREATOR, $userDetail->creatorId())->where('module', self::SINGULAR)->get();
                $view = VW::USR . '.profile';
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
                return ViewFacade::make($view, compact('userDetail', 'customFields'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const EDT_PRF = 'editProfile';
    public function editProfile(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::ED_USER);
                $userDetail = $request->user();
                $validator = Validator::make($request->all(), [
                    'name'    => 'required|max:120',
                    'email'   => 'required|email|unique:users,email,' . $userDetail->id,
                    'profile' => 'nullable|file|mimes:jpg,png,jpeg,gif|max:' . SC::MAX_U_SIZE_DEF,
                ]);
                if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());
                if ($request->hasFile('profile')) {
                    $file      = $request->file('profile');
                    $filename  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $storeName = $filename . '_' . time() . '.' . $extension;
                    $dir       = (Utility::getStorageSetting()[SC::STR_STT] ?? 'local') === 'local' ? 'uploads/avatar/' : 'uploads/avatar';
                    $oldPath   = $dir . $userDetail->avatar;
                    File::exists($oldPath) && File::delete($oldPath);
                    $upload = Utility::uploadFile($request, 'profile', $storeName, $dir, []);
                    if (($upload['flag'] ?? 0) !== 1) {
                        // ! ALERT
                        return redirect()->route('profile')->with('error', __($upload['msg'] ?? 'Upload failed'));
                    }
                    $userDetail->avatar = $storeName;
                    Log::debug("$cls::$action avatar", [UC::COL_USER_ID => $userDetail->id, 'file' => $storeName]);
                }
                $userDetail->fill($request->only('name', 'email'))->save();
                CustomField::saveData($userDetail, $request->input('customField', []));
                // ! ALERT
                return redirect()->route('dashboard')->with('success', __('Profile successfully updated.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const UPD_PSW = 'updatePassword';
    public function updatePassword(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::ED_USER);
                $validator = Validator::make($request->all(), [
                    'old_password' => 'required',
                    'password'     => 'required|min:6|confirmed',
                ]);
                if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());
                $user = $request->user();
                if (!Hash::check($request->input('old_password'), $user?->password))
                    return redirect()->back()->with('error', __('Please enter correct current password.'));
                $user->password = Hash::make($request->input('password'));
                $user?->save();
                Log::debug("$cls::$action changed", [UC::COL_USER_ID => $user?->id]);
                // ! ALERT
                return redirect()->route('profile')->with('success', __('Password successfully updated.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const TD_STR = 'todoStore';
    public function todoStore(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                    return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
                $validator = Validator::make($request->all(), ['title' => 'required|max:120']);
                if ($validator->fails())
                    return response()->json(['error' => $validator->errors()->first()], Response::HTTP_BAD_REQUEST);
                $todo = UserToDo::create([
                    'title'                 => $request->input('title'),
                    UC::COL_USER_ID => $request->user()->id,
                ]);
                $todo->updateUrl = route(VW::TD . '.update', [$todo->id]);
                $todo->deleteUrl = route(VW::TD . '.destroy', [$todo->id]);
                Log::debug("$cls::$action created", ['id' => $todo->id]);
                return response()->json($todo, Response::HTTP_CREATED);
            } catch (\Throwable $e) {
                Log::debug("$cls::$action error", ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public const TD_UPD = 'todoUpdate';
    public function todoUpdate(string|int $todoId): JsonResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($todoId, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                    return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
                $todo = UserToDo::findOrFail($todoId);
                $todo[PJC::COL_IS_CP] = !$todo[PJC::COL_IS_CP];
                $todo->save();
                Log::debug("$cls::$action toggled", ['id' => $todo->id, 'complete' => $todo[PJC::COL_IS_CP]]);
                return response()->json($todo, Response::HTTP_OK);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::debug("$cls::$action not found", ['id' => $todoId]);
                return response()->json(['error' => 'Todo not found'], Response::HTTP_NOT_FOUND);
            } catch (\Throwable $e) {
                Log::debug("$cls::$action error", ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public const TD_DEL = 'todoDestroy';
    public function todoDestroy(int|string $id): JsonResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($id, $action, $cls) {
            try {
                if ((self::_checkLogin()) instanceof RedirectResponse)
                    return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
                $todo = UserToDo::findOrFail($id);
                $todo->delete();
                Log::debug("$cls::$action deleted", ['id' => $id]);
                return response()->json(['success' => true], Response::HTTP_OK);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::debug("$cls::$action not found", ['id' => $id]);
                return response()->json(['error' => 'Todo not found'], Response::HTTP_NOT_FOUND);
            } catch (\Throwable $e) {
                Log::debug("$cls::$action error", ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public const CHG_MD = 'changeMode';
    public function changeMode(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::ED_USER);
                $user = $request->user();
                $user->mode = $user->mode == 'light' ? 'dark' : 'light';
                $user->dark_mode = $user->mode == 'dark' ? 1 : 0;
                $user?->save();
                Log::debug("$cls::$action", [UC::COL_USER_ID => $user?->id, 'mode' => $user?->mode]);
                return redirect()->back();
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const UPG_PLN = 'upgradePlan';
    public function upgradePlan(Request $request, int|string $userId)
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $userId, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::MNG_USER, VW::USR . '.index');
                $user = User::findOrFail($userId);
                $plans = Plan::all();
                $adminPaymentSetting = Utility::getAdminPaymentSetting();
                $view = VW::USR . '.plan';
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
                return ViewFacade::make($view, compact(self::SINGULAR, 'plans', 'adminPaymentSetting'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const ACT_PLN = 'activePlan';
    public function activePlan(Request $request, int|string $userId, int|string $planId): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $userId, $planId, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $u = $userOrRedirect;
                self::guard($request, PMC::MNG_USER, VW::USR . '.index');
                $user = User::findOrFail($userId);
                $assign = $user?->assignPlan($planId);
                $plan  = Plan::findOrFail($planId);
                if ($assign['is_success'] ?? false) {
                    $orderId = strtoupper(str_replace('.', '', uniqid('', true)));
                    Order::create([
                        'order_id'       => $orderId,
                        'plan_name'      => $plan->name,
                        'plan_id'        => $plan->id,
                        'price'          => $plan->price,
                        'price_currency' => $u->planPrice()['currency'] ?? '',
                        'payment_status' => 'success',
                        UC::COL_USER_ID => $user?->id,
                    ]);
                    return redirect()->back()->with('success', __('Plan successfully upgraded.'));
                }
                return redirect()->back()->with('error', __('Plan failed to upgrade.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const USR_PSW = 'userPassword';
    public function userPassword(Request $request, string $id)
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::ED_USER, VW::USR . '.index');
                $id   = Crypt::decrypt($id);
                $user = User::findOrFail($id);
                $view = VW::USR . '.reset';
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
                return ViewFacade::make($view, compact(self::SINGULAR));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const USR_PSW_RST = 'userPasswordReset';
    public function userPasswordReset(Request $request, int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::ED_USER, VW::USR . '.index');
                $validator = Validator::make($request->all(), ['password' => 'required|confirmed']);
                if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());
                $user = User::findOrFail($id);
                $user->password = Hash::make($request->input('password'));
                $user?->save();
                Log::debug("$cls::$action", [UC::COL_USER_ID => $user?->id]);
                return redirect()->route(VW::USR . '.index')->with('success', __('User Password successfully updated.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const USR_LOG = 'userLog';
    public function userLog(Request $request)
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($g = self::guard($request, PMC::MNG_USER, VW::USR . '.index')) !== true) return $g;
                $filteruser = User::where(DC::COL_TABLE_CREATOR, $request->user()->creatorId())
                    ->pluck(UC::COL_NM, 'id')
                    ->prepend(__('Select User'), '');
                $query = DB::table('login_details')
                    ->join(DC::TABLE_USERS, 'login_details.' . UC::COL_USER_ID, '=', DC::TABLE_USERS . '.id')
                    ->select('login_details.*', DC::TABLE_USERS . '.id as user_id', DC::TABLE_USERS . '.name as user_name')
                    ->where('login_details.' . DC::COL_TABLE_CREATOR, $request->user()->creatorId());
                if ($request->filled('month')) {
                    $query->whereMonth('date', date('m', strtotime($request->month)))
                        ->whereYear('date', date('Y', strtotime($request->month)));
                } else {
                    $query->whereMonth('date', date('m'))->whereYear('date', date('Y'));
                }
                if ($request->filled(DC::TABLE_USERS))
                    $query->where(UC::COL_USER_ID, $request->users);
                $userDetails      = $query->get();
                $lastLoginDetails = LoginDetail::where(DC::COL_TABLE_CREATOR, $request->user()->creatorId())->get();
                $view = VW::USR . '.userlog';
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
                return ViewFacade::make($view, compact('userDetails', 'lastLoginDetails', 'filteruser'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                Log::debug("$cls::$action error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const USR_LOG_VIEW = 'userLogView';
    public function userLogView(Request $request, int|string $id)
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::MNG_USER, VW::USR . '.index');
                $detail = LoginDetail::findOrFail($id);
                $view = VW::USR . '.userlogview';
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
                return ViewFacade::make($view, compact('detail'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                Log::debug("$cls::$action error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const USR_LOG_DSTR = 'userLogDestroy';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function userLogDestroy(Request $request, int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PMC::MNG_USER, VW::USR . '.index');
                LoginDetail::where(UC::COL_USER_ID, $id)->delete();
                Log::debug("$cls::$action cleared", [UC::COL_USER_ID => $id]);
                return redirect()->back()->with('success', __('Login details deleted.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                Log::debug("$cls::$action error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const FLT_USR_VW = 'filterUserView';
    public function filterUserView(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                self::guard($request, PMC::MNG_USER, VW::USR . '.index');
                $users = User::where(DC::COL_TABLE_CREATOR, $user->creatorId())->get();
                return view(VW::USR . '.index', compact('users'));
            } catch (\Throwable $e) {
                Log::debug("$cls::$action error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const CHK_USR_EXT = 'checkUserExists';
    public function checkUserExists(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                    return response()->json(['exists' => false, 'error' => 'Unauthorized'], 403);
                $email = $request->input('email', '');
                $exists = User::where('email', $email)->exists();
                return response()->json(['exists' => $exists]);
            } catch (\Throwable $e) {
                Log::debug("$cls::$action error", ['error' => $e->getMessage()]);
                return response()->json(['exists' => false, 'error' => $e->getMessage()], 500);
            }
        });
    }

    /**
     * Get tasks associated with a specific project for the current user.
     */
    public const GT_PRJ_TSK = 'getProjectTask';
    public function getProjectTask(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                    return response()->json(['error' => 'Unauthorized'], 403);
                $projectId = $request->input('project_id');
                if (empty($projectId))
                    return response()->json(['tasks' => []]);
                $tasks = \App\Models\ProjectTask::where('project_id', $projectId)->pluck('name', 'id');
                return response()->json(['tasks' => $tasks]);
            } catch (\Throwable $e) {
                Log::error("$cls::$action error", ['error' => $e->getMessage()]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
    }

    /**
     * Mark a notification as seen/read.
     */
    public const NTF_SN = 'notificationSeen';
    public function notificationSeen(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                    return response()->json(['error' => 'Unauthorized'], 403);
                $user = $request->user();
                $notification = $user?->notifications()?->where('id', $id)->first();
                if ($notification) {
                    $notification->markAsRead();
                    Log::info("$cls::$action marked", ['notification_id' => $id, UC::COL_USER_ID => $user?->id]);
                }
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                Log::error("$cls::$action error", ['error' => $e->getMessage()]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
    }

    /**
     * Search users by name or email.
     */
    public const SRC = 'search';
    public function search(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                    return response()->json(['error' => 'Unauthorized'], 403);
                $user = $request->user();
                $search = $request->input('q', '');
                $users = User::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                    ->where(function ($q) use ($search) {
                        $q->where(UC::COL_NM, 'LIKE', "%{$search}%")
                            ->orWhere(UC::COL_EM, 'LIKE', "%{$search}%");
                    })
                    ->limit(20)
                    ->get([UC::COL_NM, UC::COL_EM, 'id']);
                return response()->json(['users' => $users]);
            } catch (\Throwable $e) {
                Log::error("$cls::$action error", ['error' => $e->getMessage()]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
    }

    /**
     * Get detailed info for a specific user.
     */
    public const USR_INF = 'userInfo';
    public function userInfo(Request $request, int|string $id): JsonResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                    return response()->json(['error' => 'Unauthorized'], 403);
                $user = $request->user();
                $target = User::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->findOrFail($id);
                return response()->json([
                    'id' => $target->id,
                    UC::COL_NM => $target[UC::COL_NM],
                    UC::COL_EM => $target[UC::COL_EM],
                    UC::COL_TP => $target[UC::COL_TP],
                ]);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json(['error' => 'User not found'], 404);
            } catch (\Throwable $e) {
                Log::error("$cls::$action error", ['error' => $e->getMessage()]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
    }

    /**
     * Update the authenticated user's profile.
     */
    public const UPD_PRF = 'updateProfile';
    public function updateProfile(Request $request): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                    return $userOrRedirect;
                $user = $request->user();
                if (!$user) return redirect()->back()->with('error', __('User not found.'));

                $validator = Validator::make($request->all(), [
                    UC::COL_NM => 'required|string|max:255',
                    UC::COL_EM => 'required|email|max:255|unique:users,' . UC::COL_EM . ',' . $user->id,
                    'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ]);

                if ($validator->fails()) {
                    if ($request->expectsJson())
                        return response()->json(['errors' => $validator->errors()], 422);
                    return redirect()->back()->withErrors($validator)->withInput();
                }

                $user->{UC::COL_NM} = $request->input(UC::COL_NM);
                $user->{UC::COL_EM} = $request->input(UC::COL_EM);

                if ($request->hasFile('avatar')) {
                    $avatar = $request->file('avatar');
                    $path = $avatar->store('avatars', 'public');
                    $user->avatar = $path;
                }

                $user->save();

                Log::info("$cls::$action updated", [UC::COL_USER_ID => $user->id]);

                if ($request->expectsJson())
                    return response()->json(['success' => true, 'message' => __('Profile updated successfully.')]);

                return redirect()->back()->with('success', __('Profile updated successfully.'));
            } catch (\Throwable $e) {
                Log::error("$cls::$action error", ['error' => $e->getMessage()]);
                if ($request->expectsJson())
                    return response()->json(['error' => $e->getMessage()], 500);
                return redirect()->back()->with('error', __('Failed to update profile.'));
            }
        });
    }
}
