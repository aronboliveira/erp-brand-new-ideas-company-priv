<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    PlansConstants,
    ProjectsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Http\Controllers\Controller as AppController;
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

class UserController extends AppController
{
    use ChecksLogin, ChecksPermissions;
    private const SINGULAR = 'user';

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PermissionsConstants::MNG_USER, ViewsConstants::USR . '.index');
                $user = $request->user();
                Log::debug("$cls::$action start", [UsersConstants::COL_USER_ID => $user?->id]);
                $query = User::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->with('current_plan');
                $users = $user[UsersConstants::COL_TP] === PermissionsConstants::SA
                    ? $query->where(UsersConstants::COL_TP, PermissionsConstants::CPN)->get()
                    : $query->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)->get();
                $view = ViewsConstants::USR . '.' . $action;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
                return ViewFacade::make($view, compact(DatabaseConstants::TABLE_USERS));
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
                self::guard($request, PermissionsConstants::CR_USER, ViewsConstants::USR . '.index');
                $customFields = CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->where('module', self::SINGULAR)->get();
                $roles = Role::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->where('name', '!=', PermissionsConstants::CL)->pluck('name', 'id');
                $view = ViewsConstants::USR . '.' . $action;
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
                self::guard($request, PermissionsConstants::CR_USER, ViewsConstants::USR . '.index');
                $rules = [
                    UsersConstants::COL_NM => 'required|max:120',
                    UsersConstants::COL_EM => 'required|email|unique:users',
                    UsersConstants::COL_PW => 'required|min:6'
                ];
                if ($request->user()[UsersConstants::COL_TP] !== PermissionsConstants::SA) $rules['role'] = 'required';
                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());

                $psw = $request->input('password');
                $data = [
                    UsersConstants::COL_NM           => $request->input(UsersConstants::COL_NM),
                    UsersConstants::COL_EM           => $request->input(UsersConstants::COL_EM),
                    UsersConstants::COL_PW           => Hash::make($psw),
                    UsersConstants::COL_TP           => $request->user()[UsersConstants::COL_TP] === PermissionsConstants::SA
                        ? PermissionsConstants::CPN
                        : Role::findById($request->input('role'))->name,
                    UsersConstants::COL_LG           => DB::table(DatabaseConstants::TABLE_SETTINGS)
                        ->where(UsersConstants::COL_NM, SettingsConstants::DEF_LNG)
                        ->where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())
                        ->value('value') ?: DatabaseConstants::DEFAULT_LANG,
                    DatabaseConstants::COL_TABLE_CREATOR => $request->user()->creatorId(),
                    UsersConstants::COL_U_AT         => now(),
                ];

                if ($request->user()[UsersConstants::COL_TP] === PermissionsConstants::SA) {
                    $data['plan'] = Plan::first()->id;
                    $user = User::create($data);
                    $user?->assignRole(Role::findByName(PermissionsConstants::CPN));
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
                    if ($totalUsers >= $plan[PlansConstants::COL_MAX_U] && $plan[PlansConstants::COL_MAX_U] !== -1)
                        return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
                    $user = User::create($data);
                    $role = Role::findById($request->input('role'));
                    $user?->assignRole($role);
                    Utility::employeeDetails($user?->id, $creator);
                }

                if (Utility::settings()['new_user'] ?? false)
                    Utility::sendEmailTemplate('new_user', [$user?->email], ['email' => $user?->email, 'password' => $psw]);

                return redirect()->route(ViewsConstants::USR . '.index')->with('success', __('User successfully created.'));
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
            return redirect()->route(ViewsConstants::USR . '.index');
        });
    }

    public function edit(Request $request, int|string $id)
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PermissionsConstants::ED_USER, ViewsConstants::USR . '.index');
                $userDetail   = User::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->findOrFail($id);
                $customFields = CustomField::getData($userDetail, self::SINGULAR);
                $roles = Role::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->where('name', '!=', PermissionsConstants::CL)->pluck('name', 'id');
                $view = ViewsConstants::USR . '.' . $action;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
                return ViewFacade::make($view, compact('userDetail', DatabaseConstants::TABLE_ROLES, 'customFields'));
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
                self::guard($request, PermissionsConstants::ED_USER, ViewsConstants::USR . '.index');
                $rules = ['name' => 'required|max:120', 'email' => "required|email|unique:users,email,$id"];
                if ($request->user()[UsersConstants::COL_TP] !== PermissionsConstants::SA) $rules['role'] = 'required';
                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());

                $userDetail = User::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->findOrFail($id);
                $input = $request->only(['name', 'email']);
                if ($request->user()[UsersConstants::COL_TP] !== PermissionsConstants::SA) {
                    $role = Role::findById($request->input('role'));
                    $input[UsersConstants::COL_TP] = $role->name;
                } else {
                    $role = Role::findByName(PermissionsConstants::CPN);
                    $input[UsersConstants::COL_TP] = $role->name;
                }
                $userDetail->fill($input)->save();
                CustomField::saveData($userDetail, $request->input('customField', []));
                $userDetail->roles()->sync([$role->id]);

                return redirect()->route(ViewsConstants::USR . '.index')->with('success', __('User successfully updated.'));
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
                self::guard($request, PermissionsConstants::DEL_USER, ViewsConstants::USR . '.index');
                $user = User::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->findOrFail($id);
                if ($request->user()[UsersConstants::COL_TP] === PermissionsConstants::SA) {
                    $user->delete_status = $user?->delete_status ? 0 : 1;
                    $user?->save();
                } elseif ($request->user()[UsersConstants::COL_TP] === PermissionsConstants::CPN) {
                    Employee::where(UsersConstants::COL_USER_ID, $user?->id)->delete();
                    $user?->delete();
                }
                return redirect()->route(ViewsConstants::USR . '.index')->with('success', __('User successfully deleted.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public function profile(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PermissionsConstants::MNG_USER, ViewsConstants::USR . '.index');
                $userDetail = $request->user();
                $userDetail->customField = CustomField::getData($userDetail, self::SINGULAR);
                $customFields = CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $userDetail->creatorId())->where('module', self::SINGULAR)->get();
                $view = ViewsConstants::USR . '.profile';
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
                self::guard($request, PermissionsConstants::ED_USER);
                $userDetail = $request->user();
                $validator = Validator::make($request->all(), [
                    'name'    => 'required|max:120',
                    'email'   => 'required|email|unique:users,email,' . $userDetail->id,
                    'profile' => 'nullable|file|mimes:jpg,png,jpeg,gif|max:' . SettingsConstants::MAX_U_SIZE_DEF,
                ]);
                if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());
                if ($request->hasFile('profile')) {
                    $file      = $request->file('profile');
                    $filename  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $storeName = $filename . '_' . time() . '.' . $extension;
                    $dir       = (Utility::getStorageSetting()[SettingsConstants::STR_STT] ?? 'local') === 'local' ? 'uploads/avatar/' : 'uploads/avatar';
                    $oldPath   = $dir . $userDetail->avatar;
                    File::exists($oldPath) && File::delete($oldPath);
                    $upload = Utility::uploadFile($request, 'profile', $storeName, $dir, []);
                    if (($upload['flag'] ?? 0) !== 1) {
                        // ! ALERT
                        return redirect()->route('profile')->with('error', __($upload['msg'] ?? 'Upload failed'));
                    }
                    $userDetail->avatar = $storeName;
                    Log::debug("$cls::$action avatar", [UsersConstants::COL_USER_ID => $userDetail->id, 'file' => $storeName]);
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
                self::guard($request, PermissionsConstants::ED_USER);
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
                Log::debug("$cls::$action changed", [UsersConstants::COL_USER_ID => $user?->id]);
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
                    UsersConstants::COL_USER_ID => $request->user()->id,
                ]);
                // ! ALERT
                $todo->updateUrl = route('todo.update', [$todo->id]);
                // ! ALERT
                $todo->deleteUrl = route('todo.destroy', [$todo->id]);
                Log::debug("$cls::$action created", ['id' => $todo->id]);
                return response()->json($todo, Response::HTTP_CREATED);
            } catch (\Throwable $e) {
                Log::debug("$cls::$action error", ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public const TD_UPD = 'todoUpdate';
    public function todoUpdate(int $todoId): JsonResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($todoId, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                    return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
                $todo = UserToDo::findOrFail($todoId);
                $todo[ProjectsConstants::COL_IS_CP] = !$todo[ProjectsConstants::COL_IS_CP];
                $todo->save();
                Log::debug("$cls::$action toggled", ['id' => $todo->id, 'complete' => $todo[ProjectsConstants::COL_IS_CP]]);
                return response()->json($todo, Response::HTTP_OK);
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
                self::guard($request, PermissionsConstants::ED_USER);
                $user = $request->user();
                $user->mode = $user->mode == 'light' ? 'dark' : 'light';
                $user->dark_mode = $user->mode == 'dark' ? 1 : 0;
                $user?->save();
                Log::debug("$cls::$action", [UsersConstants::COL_USER_ID => $user?->id, 'mode' => $user?->mode]);
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
                self::guard($request, PermissionsConstants::MNG_USER, ViewsConstants::USR . '.index');
                $user = User::findOrFail($userId);
                $plans = Plan::all();
                $adminPaymentSetting = Utility::getAdminPaymentSetting();
                $view = ViewsConstants::USR . '.plan';
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
                self::guard($request, PermissionsConstants::MNG_USER, ViewsConstants::USR . '.index');
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
                        UsersConstants::COL_USER_ID => $user?->id,
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
                self::guard($request, PermissionsConstants::ED_USER, ViewsConstants::USR . '.index');
                $id   = Crypt::decrypt($id);
                $user = User::findOrFail($id);
                $view = ViewsConstants::USR . '.reset';
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
                self::guard($request, PermissionsConstants::ED_USER, ViewsConstants::USR . '.index');
                $validator = Validator::make($request->all(), ['password' => 'required|confirmed']);
                if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());
                $user = User::findOrFail($id);
                $user->password = Hash::make($request->input('password'));
                $user?->save();
                Log::debug("$cls::$action", [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->route(ViewsConstants::USR . '.index')->with('success', __('User Password successfully updated.'));
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
                self::guard($request, PermissionsConstants::MNG_USER, ViewsConstants::USR . '.index');
                $filterUser = User::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())
                    ->pluck(UsersConstants::COL_NM, 'id')
                    ->prepend(__('Select User'), '');
                $query = DB::table('login_details')
                    ->join(DatabaseConstants::TABLE_USERS, 'login_details.' . UsersConstants::COL_USER_ID, '=', DatabaseConstants::TABLE_USERS . '.id')
                    ->select('login_details.*', DatabaseConstants::TABLE_USERS . '.id as user_id', DatabaseConstants::TABLE_USERS . '.name as user_name')
                    ->where('login_details.' . DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId());
                if ($request->filled('month')) {
                    $query->whereMonth('date', date('m', strtotime($request->month)))
                        ->whereYear('date', date('Y', strtotime($request->month)));
                } else {
                    $query->whereMonth('date', date('m'))->whereYear('date', date('Y'));
                }
                if ($request->filled(DatabaseConstants::TABLE_USERS))
                    $query->where(UsersConstants::COL_USER_ID, $request->users);
                $userDetails      = $query->get();
                $lastLoginDetails = LoginDetail::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->get();
                $view = ViewsConstants::USR . '.userlog';
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
                return ViewFacade::make($view, compact('userDetails', 'lastLoginDetails', 'filterUser'));
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
                self::guard($request, PermissionsConstants::MNG_USER, ViewsConstants::USR . '.index');
                $detail = LoginDetail::findOrFail($id);
                $view = ViewsConstants::USR . '.userlogview';
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
    public function userLogDestroy(Request $request, int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $cls) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                self::guard($request, PermissionsConstants::MNG_USER, ViewsConstants::USR . '.index');
                LoginDetail::where(UsersConstants::COL_USER_ID, $id)->delete();
                Log::debug("$cls::$action cleared", [UsersConstants::COL_USER_ID => $id]);
                return redirect()->back()->with('success', __('Login details deleted.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                Log::debug("$cls::$action error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }
}
