<?php

namespace App\Http\Controllers\Planning;

use App\Config\Constants\{DatabaseConstants as DC, PermissionsConstants as PMC, UsersConstants as UC, ViewsConstants as VW};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\{Employee, InterviewSchedule, JobApplication, JobStage, User, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions, ConsoleOutputs};
use Illuminate\Database\QueryException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\{DB, Log, Validator, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\defaultUndefinedException;
class InterviewScheduleController extends Controller
{
    use ChecksLogin, ChecksPermissions, ConsoleOutputs;

    private const ENTITY = VW::ITV_SCD;

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::ITV_SCD . '.' . $action;
        $routeShow ??= __CLASS__ . '::show';

        return $this->measureProfile($action, function () use ($request, $action, $method, $viewPath, $routeShow) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $arrSchedule ??= '[]';
            $transDate ??= now()->format('Y-m-d');
            try {
                $t = microtime(true);
                $schedules = InterviewSchedule::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get();
                $arrSchedule = $schedules->map(fn($schedule) => [
                    'id' => $schedule->id,
                    'title' => $schedule->applications->jobs->title ?? '',
                    'start' => $schedule->date,
                    'className' => 'event-primary',
                    'url' => route($routeShow, $schedule->id),
                ])->toJson();
                $this->logExecutionTime($t, $action . '::fetchSchedules', 'completed');

                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) {
                    $this->consoleOutput($method . ' view missing: ' . $viewPath, 'error');
                    Log::error($method . ' view not found', [
                        'error' => 'view_missing',
                        'error_class' => \RuntimeException::class,
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'action' => $action,
                        'view' => $viewPath,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact('arrSchedule', DC::TABLE_SCHEDULES, 'transDate'));
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function create(Request $request, string|int $candidate = ''): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::ITV_SCD . '.' . $action;

        return $this->measureProfile($action, function () use ($request, $candidate, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $employees ??= collect();
            $candidates ??= collect();
            $settings ??= [];
            try {
                if (($redirect = self::guard($request, PMC::CR_ITV_SCHD, self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;

                $t = microtime(true);
                $employees = User::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                    ->where(UC::COL_TP, class_basename(strtolower(Employee::class)))
                    ->orWhere('id', $user?->creatorId())
                    ->pluck(UC::COL_NM, 'id')
                    ->prepend('--', '') ?? $employees;
                $candidates = JobApplication::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                    ->pluck('name', 'id')
                    ->prepend('--', '') ?? $candidates;
                $settings = Utility::settings() ?? $settings;
                $this->logExecutionTime($t, $action . '::fetchFormData', 'completed');

                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) {
                    $this->consoleOutput($method . ' view missing: ' . $viewPath, 'error');
                    Log::error($method . ' view not found', [
                        'error' => 'view_missing',
                        'error_class' => \RuntimeException::class,
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'action' => $action,
                        'view' => $viewPath,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact(DC::TABLE_EMPLOYEES, 'candidates', 'candidate', DC::TABLE_SETTINGS));
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function store(Request $request): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $data ??= [];
            $inTransaction ??= false;
            try {
                if (($redirect = self::guard($request, PMC::CR_ITV_SCHD, self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;

                $t = microtime(true);
                $data = Validator::make($request->all(), [
                    'candidate' => 'required',
                    class_basename(strtolower(Employee::class)) => 'required',
                    'date' => 'required',
                    'time' => 'required',
                ])->validate();
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $createData = [];
                foreach (['candidate', class_basename(strtolower(Employee::class)), 'date', 'time'] as $field)
                    $createData[$field] = $data[$field] ?? null;
                $createData['comment'] = $request->comment ?? '';
                $createData[DC::COL_TABLE_CREATOR] = $user?->creatorId();

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                $schedule = InterviewSchedule::create($createData);
                DB::commit();
                $inTransaction = false;

                if ($request->input('synchronizeType') === 'googleCalendar')
                    Utility::addCalendarData($schedule, 'interview_schedule');

                return Redirect::back()->with('success', __('Interview schedule successfully created.'));
            } catch (ValidationException $e) {
                Log::warning($method . ' validation failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'errors' => $e->errors() ?? [],
                ]);
                $this->consoleOutput($method . ' validation failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ENTITY . '.create'));
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function show(Request $request, InterviewSchedule $interviewSchedule): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::ITV_SCD . '.' . $action;

        return $this->measureProfile($action, function () use ($request, $interviewSchedule, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $stages ??= collect();
            try {
                if (($redirect = self::guard($request, 'view interview schedule', self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;

                $t = microtime(true);
                $stages = JobStage::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get() ?? $stages;
                $this->logExecutionTime($t, $action . '::fetchStages', 'completed');

                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) {
                    $this->consoleOutput($method . ' view missing: ' . $viewPath, 'error');
                    Log::error($method . ' view not found', [
                        'error' => 'view_missing',
                        'error_class' => \RuntimeException::class,
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'action' => $action,
                        'view' => $viewPath,
                        'interview_schedule_id' => $interviewSchedule?->id,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact('interviewSchedule', 'stages'));
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function edit(Request $request, InterviewSchedule $interviewSchedule): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::ITV_SCD . '.' . $action;

        return $this->measureProfile($action, function () use ($request, $interviewSchedule, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $employees ??= collect();
            $candidates ??= collect();
            try {
                if (($redirect = self::guard($request, 'edit interview schedule', self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;

                $t = microtime(true);
                $employees = User::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                    ->where(UC::COL_TP, class_basename(strtolower(Employee::class)))
                    ->orWhere('id', $user?->creatorId())
                    ->pluck(UC::COL_NM, 'id')
                    ->prepend('--', '') ?? $employees;
                $candidates = JobApplication::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                    ->pluck('name', 'id')
                    ->prepend('--', '') ?? $candidates;
                $this->logExecutionTime($t, $action . '::fetchFormData', 'completed');

                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) {
                    $this->consoleOutput($method . ' view missing: ' . $viewPath, 'error');
                    Log::error($method . ' view not found', [
                        'error' => 'view_missing',
                        'error_class' => \RuntimeException::class,
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'action' => $action,
                        'view' => $viewPath,
                        'interview_schedule_id' => $interviewSchedule?->id,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact(DC::TABLE_EMPLOYEES, 'candidates', 'interviewSchedule'));
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function update(Request $request, InterviewSchedule $interviewSchedule): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $interviewSchedule, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $data ??= [];
            $inTransaction ??= false;
            try {
                if (($redirect = self::guard($request, 'edit interview schedule', self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;

                $t = microtime(true);
                $data = Validator::make($request->all(), [
                    'candidate' => 'required',
                    class_basename(strtolower(Employee::class)) => 'required',
                    'date' => 'required',
                    'time' => 'required',
                ])->validate();
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $updateData = [];
                foreach (['candidate', class_basename(strtolower(Employee::class)), 'date', 'time'] as $field)
                    $updateData[$field] = $data[$field] ?? null;
                $updateData['comment'] = $request->comment ?? '';

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                $interviewSchedule->update($updateData);
                DB::commit();
                $inTransaction = false;

                return Redirect::back()->with('success', __('Interview schedule successfully updated.'));
            } catch (ValidationException $e) {
                Log::warning($method . ' validation failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                    'user_id' => $user?->id,
                    'errors' => $e->errors() ?? [],
                ]);
                $this->consoleOutput($method . ' validation failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ENTITY . '.edit', $interviewSchedule->id));
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function destroy(Request $request, InterviewSchedule $interviewSchedule): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $interviewSchedule, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $inTransaction ??= false;
            try {
                if (($redirect = self::guard($request, 'delete interview schedule', self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                $interviewSchedule->delete();
                DB::commit();
                $inTransaction = false;

                return Redirect::back()->with('success', __('Interview schedule successfully deleted.'));
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'interview_schedule_id' => $interviewSchedule?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public const GET_ITV_D = 'getInterviewData';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function getInterviewData(Request $request): array|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $routeShow ??= __CLASS__ . '::show';

        return $this->measureProfile($action, function () use ($request, $action, $method, $routeShow) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            try {
                if (($redirect = self::guard($request, 'view interview schedule', self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;

                return $request->input('calendarType') === 'googleCalendar'
                    ? Utility::getCalendarData('interview_schedule')
                    : InterviewSchedule::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                    ->get()
                    ->map(fn($val) => [
                        'id' => $val->id,
                        'title' => $val->comment,
                        'start' => $val->date . ' ' . $val->time,
                        'className' => 'event-primary',
                        'textColor' => '#51459d',
                        'url' => route($routeShow, $val->id),
                        'allDay' => false,
                    ])
                    ->toArray();
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }
}
