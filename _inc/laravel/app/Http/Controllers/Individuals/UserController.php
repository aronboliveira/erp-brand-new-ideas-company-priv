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
    Validator
};
use Symfony\Component\HttpFoundation\Response;

class UserController extends AppController
{
    use ChecksLogin, ChecksPermissions;
    private const SINGULAR = 'user';
    /**
     * @return \Illuminate\View\View|RedirectResponse
     */
    public function index(Request $request)
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::MNG_USER);
            $user = $request->user();
            $query = User::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->with('current_plan');
            $users = $user[UsersConstants::COL_TP] === PermissionsConstants::SA
                ? $query->where(UsersConstants::COL_TP, PermissionsConstants::CPN)->get()
                : $query->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)->get();
            return view(ViewsConstants::USR . '.' . __FUNCTION__, compact(DatabaseConstants::TABLE_USERS));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\View\View|RedirectResponse
     */
    public function create(Request $request)
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::CR_USER);
            $customFields = CustomField::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->where('module', self::SINGULAR)->get();
            $roles = Role::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->where('name', '!=', PermissionsConstants::CL)->pluck('name', 'id');
            return view(ViewsConstants::USR . '.create', compact('roles', 'customFields'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::CR_USER);
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
                UsersConstants::COL_NM              => $request->input(UsersConstants::COL_NM),
                UsersConstants::COL_EM             => $request->input(UsersConstants::COL_EM),
                UsersConstants::COL_PW          => Hash::make($psw),
                UsersConstants::COL_TP              => $request->user()[UsersConstants::COL_TP] === PermissionsConstants::SA
                    ? PermissionsConstants::CPN
                    : Role::findById($request->input('role'))->name,
                UsersConstants::COL_LG              => DB::table(DatabaseConstants::TABLE_SETTINGS)
                    ->where(UsersConstants::COL_NM, SettingsConstants::DEF_LNG)
                    ->where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                    ->value('value') ?: DatabaseConstants::DEFAULT_LANG,
                DatabaseConstants::TABLE_CREATOR        => $request->user()->creatorId(),
                UsersConstants::COL_U_AT => now(),
            ];
            if ($request->user()[UsersConstants::COL_TP] === PermissionsConstants::SA) {
                $data['plan'] = Plan::first()->id;
                $user        = User::create($data);
                $user?->assignRole(Role::findByName(PermissionsConstants::CPN));
                if ($request->user()[UsersConstants::COL_TP] === PermissionsConstants::SA) {
                    $data['plan'] = Plan::first()->id;
                    $user        = User::create($data);
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
                    foreach ($initializers as $initializer)
                        call_user_func_array($initializer, [$user?->id]);
                }
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
                Utility::employeeDetails($user?->id, $request->user()->creatorId());
            }
            Log::info('User created', ['id' => $user?->id]);
            if (Utility::settings()['new_user'] ?? false)
                Utility::sendEmailTemplate('new_user', [$user?->email], ['email' => $user?->email, 'password' => $psw]);
            return redirect()->route(ViewsConstants::USR . '.index')->with('success', __('User successfully created.'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * @return RedirectResponse
     */
    public function show(): RedirectResponse
    {
        return redirect()->route(ViewsConstants::USR . '.index');
    }

    /**
     * @param  Request  $request
     * @param  int      $id
     * @return \Illuminate\View\View|RedirectResponse
     */
    public function edit(Request $request, int $id)
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::ED_USER);

            $userDetail  = User::findOrFail($id);
            $customFields = CustomField::getData($userDetail, self::SINGULAR);
            $roles       = Role::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->where('name', '!=', PermissionsConstants::CL)->pluck('name', 'id');
            return view(ViewsConstants::USR . '.' . __FUNCTION__, compact(
                'userDetail',
                DatabaseConstants::TABLE_ROLES,
                'customFields'
            ));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }


    /**
     * @param  Request  $request
     * @param  int      $id
     * @return RedirectResponse
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::ED_USER);
            $rules = ['name' => 'required|max:120', 'email' => "required|email|unique:users,email,$id"];
            if ($request->user()[UsersConstants::COL_TP] !== PermissionsConstants::SA) $rules['role'] = 'required';
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());
            $userDetail = User::findOrFail($id);
            $input     = $request->only(['name', 'email']);
            if ($request->user()[UsersConstants::COL_TP] !== PermissionsConstants::SA) {
                $role      = Role::findById($request->input('role'));
                $input[UsersConstants::COL_TP] = $role->name;
            } else {
                $role = Role::findByName(PermissionsConstants::CPN);
                $input[UsersConstants::COL_TP] = $role->name;
            }
            $userDetail->fill($input)->save();
            CustomField::saveData($userDetail, $request->input('customField', []));
            $userDetail->roles()->sync([$role->id]);
            Log::info('User updated', ['id' => $userDetail->id]);
            return redirect()->route(ViewsConstants::USR . '.index')->with('success', __('User successfully updated.'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * @param  Request  $request
     * @param  int      $id
     * @return RedirectResponse
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::DEL_USER);
            $user = User::findOrFail($id);
            if ($request->user()[UsersConstants::COL_TP] === PermissionsConstants::SA) {
                $user->delete_status = $user?->delete_status ? 0 : 1;
                $user?->save();
            } elseif ($request->user()[UsersConstants::COL_TP] === PermissionsConstants::CPN) {
                Employee::where(UsersConstants::COL_USER_ID, $user?->id)->delete();
                $user?->delete();
            }
            Log::info('User toggled/deleted', ['id' => $user?->id]);
            return redirect()->route(ViewsConstants::USR . '.index')->with('success', __('User successfully deleted.'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Show current user profile.
     */
    public function profile(Request $request): RedirectResponse|\Illuminate\View\View
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::MNG_USER);
            $userDetail = $request->user();
            $userDetail->customField = CustomField::getData($userDetail, self::SINGULAR);
            $customFields = CustomField::where(DatabaseConstants::TABLE_CREATOR, $userDetail->creatorId())
                ->where('module', self::SINGULAR)->get();
            return view(ViewsConstants::USR . '.profile', compact('userDetail', 'customFields'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Handle profile edit form submission.
     */
    public const EDT_PRF = 'editProfile';
    public function editProfile(Request $request): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::ED_USER);
            $userDetail = $request->user();
            $validator = Validator::make($request->all(), [
                'name'  => 'required|max:120',
                'email' => 'required|email|unique:users,email,' . $userDetail->id,
                'profile' => 'nullable|file|mimes:jpg,png,jpeg,gif|max:' . SettingsConstants::MAX_U_SIZE_DEF,
            ]);
            if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());

            // file‐upload block
            if ($request->hasFile('profile')) {
                $file      = $request->file('profile');
                $filename  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $storeName = $filename . '_' . time() . '.' . $extension;
                $dir       = (Utility::getStorageSetting()[SettingsConstants::STR_STT] ?? 'local') === 'local'
                    ? 'uploads/avatar/' : 'uploads/avatar';
                $oldPath   = $dir . $userDetail->avatar;
                File::exists($oldPath) && File::delete($oldPath);
                $upload = Utility::uploadFile($request, 'profile', $storeName, $dir, []);
                if ($upload['flag'] !== 1)
                    return redirect()->route('profile')->with('error', __($upload['msg']));
                $userDetail->avatar = $storeName;
                Log::info('Avatar updated', [UsersConstants::COL_USER_ID => $userDetail->id, 'file' => $storeName]);
            }
            $userDetail->fill($request->only('name', 'email'))->save();
            CustomField::saveData($userDetail, $request->input('customField', []));
            return redirect()->route('dashboard')->with('success', __('Profile successfully updated.'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Update current user password.
     */
    public const UPD_PSW = 'updatePassword';
    public function updatePassword(Request $request): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::ED_USER);
            $validator = Validator::make($request->all(), [
                'old_password'          => 'required',
                'password'              => 'required|min:6|confirmed',
            ]);
            if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());
            $user = $request->user();
            if (!Hash::check($request->input('old_password'), $user?->password))
                return redirect()->back()->with('error', __('Please enter correct current password.'));
            $user->password = Hash::make($request->input('password'));
            $user?->save();
            Log::info('Password changed', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->route('profile')->with('success', __('Password successfully updated.'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Store a new todo item.
     */
    public const TD_STR = 'todoStore';
    public function todoStore(Request $request): JsonResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            $validator = Validator::make($request->all(), ['title' => 'required|max:120']);
            if ($validator->fails())
                return response()->json(['error' => $validator->errors()->first()], Response::HTTP_BAD_REQUEST);
            $todo = UserToDo::create([
                'title'   => $request->input('title'),
                UsersConstants::COL_USER_ID => $request->user()->id,
            ]);
            $todo->updateUrl = route('todo.update', [$todo->id]);
            $todo->deleteUrl = route('todo.destroy', [$todo->id]);
            Log::info('Todo created', ['id' => $todo->id]);
            return response()->json($todo, Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Toggle completion of a todo item.
     */
    public const TD_UPD = 'todoUpdate';
    public function todoUpdate(int $todoId): JsonResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            $todo = UserToDo::findOrFail($todoId);
            $todo[ProjectsConstants::COL_IS_CP] = !$todo[ProjectsConstants::COL_IS_CP];
            $todo->save();
            Log::info('Todo toggled', ['id' => $todo->id, 'complete' => $todo[ProjectsConstants::COL_IS_CP]]);
            return response()->json($todo, Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a todo item.
     */
    public const TD_DEL = 'todoDestroy';
    public function todoDestroy(int $id): JsonResponse
    {
        try {
            if ((self::_checkLogin()) instanceof RedirectResponse)
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            $todo = UserToDo::findOrFail($id);
            $todo->delete();
            Log::info('Todo deleted', ['id' => $id]);
            return response()->json(['success' => true], Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Toggle user dark/light mode.
     */
    public const CHG_MD = 'changeMode';
    public function changeMode(Request $request): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::ED_USER);
            $user = $request->user();
            $user->mode     = $user->mode == 'light' ? 'dark' : 'light';
            $user->dark_mode = $user->mode == 'dark' ? 1 : 0;
            $user?->save();
            Log::info('Mode changed', [UsersConstants::COL_USER_ID => $user?->id, 'mode' => $user?->mode]);
            return redirect()->back();
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Show plan upgrade form for a user.
     */
    public const UPG_PLN = 'upgradePlan';
    public function upgradePlan(Request $request, int $userId)
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::MNG_USER);

            $user = User::findOrFail($userId);
            $plans = Plan::all();
            $adminPaymentSetting = Utility::getAdminPaymentSetting();

            return view(ViewsConstants::USR . '.plan', compact(self::SINGULAR, 'plans', 'adminPaymentSetting'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Activate a new plan for a user.
     */
    public const ACT_PLN = 'activePlan';
    public function activePlan(Request $request, int $userId, int $planId): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $u = $userOrRedirect;
            self::guard($request, PermissionsConstants::MNG_USER);

            $user = User::findOrFail($userId);
            $assign = $user?->assignPlan($planId);
            $plan  = Plan::findOrFail($planId);
            if ($assign['is_success']) {
                $orderId = strtoupper(str_replace('.', '', uniqid('', true)));
                Order::create([
                    'order_id'        => $orderId,
                    'plan_name'       => $plan->name,
                    'plan_id'         => $plan->id,
                    'price'           => $plan->price,
                    'price_currency'  => $u->planPrice()['currency'] ?? '',
                    'payment_status'  => 'success',
                    UsersConstants::COL_USER_ID         => $user?->id,
                ]);
                Log::info('Plan upgraded', [UsersConstants::COL_USER_ID => $user?->id, 'plan_id' => $plan->id]);
                return redirect()->back()->with('success', __('Plan successfully upgraded.'));
            }
            return redirect()->back()->with('error', __('Plan failed to upgrade.'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Show the password reset form for a user.
     */
    public const USR_PSW = 'userPassword';
    public function userPassword(Request $request, string $encryptedId)
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::ED_USER);
            $id  = Crypt::decrypt($encryptedId);
            $user = User::findOrFail($id);
            return view(ViewsConstants::USR . '.reset', compact(self::SINGULAR));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Reset a user's password.
     */
    public const USR_PSW_RST = 'userPasswordReset';
    public function userPasswordReset(Request $request, int $id): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::ED_USER);
            $validator = Validator::make($request->all(), ['password' => 'required|confirmed']);
            if ($validator->fails())
                return redirect()->back()->with('error', $validator->errors()->first());
            $user = User::findOrFail($id);
            $user->password = Hash::make($request->input('password'));
            $user?->save();
            Log::info('Password reset', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->route(ViewsConstants::USR . '.index')->with('success', __('User Password successfully updated.'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Display user login details.
     */
    public const USR_LOG = 'userLog';
    public function userLog(Request $request)
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::MNG_USER);

            $filterUser = User::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->pluck(UsersConstants::COL_NM, 'id')->prepend(__('Select User'), '');
            $query = DB::table('login_details')
                ->join(DatabaseConstants::TABLE_USERS, 'login_details.' . UsersConstants::COL_USER_ID, '=', DatabaseConstants::TABLE_USERS . '.id')
                ->select('login_details.*', DatabaseConstants::TABLE_USERS . '.id as user_id', DatabaseConstants::TABLE_USERS . '.name as user_name')
                ->where('login_details.' . DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId());
            if ($request->filled('month')) {
                $query->whereMonth('date', date('m', strtotime($request->month)))
                    ->whereYear('date', date('Y', strtotime($request->month)));
            } else
                $query->whereMonth('date', date('m'))->whereYear('date', date('Y'));
            if ($request->filled(DatabaseConstants::TABLE_USERS))
                $query->where(UsersConstants::COL_USER_ID, $request->users);
            $userDetails     = $query->get();
            $lastLoginDetails = LoginDetail::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())->get();
            return view(ViewsConstants::USR . '.userlog', compact('userDetails', 'lastLoginDetails', 'filterUser'));
        } catch (AuthorizationException $e) {
            return redirect()->back()->with('error', __('Permission denied.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', __('An unexpected error occurred.'));
        }
    }

    /**
     * View a single login detail.
     */
    public const USR_LOG_VIEW = 'userLogView';
    public function userLogView(Request $request, int $id)
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::MNG_USER);

            $detail = LoginDetail::findOrFail($id);
            return view(ViewsConstants::USR . '.userlogview', compact('detail'));
        } catch (AuthorizationException $e) {
            return redirect()->back()->with('error', __('Permission denied.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', __('An unexpected error occurred.'));
        }
    }

    /**
     * Delete all login details for a user.
     */
    public const USR_LOG_DSTR = 'userLogDestroy';
    public function userLogDestroy(Request $request, int $id): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            self::guard($request, PermissionsConstants::MNG_USER);

            LoginDetail::where(UsersConstants::COL_USER_ID, $id)->delete();
            Log::info('Login details cleared', [UsersConstants::COL_USER_ID => $id]);

            return redirect()->back()->with('success', __('Login details deleted.'));
        } catch (AuthorizationException $e) {
            return redirect()->back()->with('error', __('Permission denied.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', __('An unexpected error occurred.'));
        }
    }
}
