<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
	ActivitiesConstants,
	CompaniesConstants,
	DatabaseConstants,
	PermissionsConstants,
	PlansConstants,
	SettingsConstants,
	UsersConstants,
	ViewsConstants
};
use App\Models\{
	Branch,
	CustomQuestion,
	Department,
	Designation,
	Document,
	Employee,
	EmployeeDocument,
	GeneratedOfferLetter,
	InterviewSchedule,
	Job,
	JobApplication,
	JobApplicationNote,
	JobOnBoard,
	JobStage,
	PayslipType,
	Plan,
	User,
	Utility
};
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{
	JsonResponse,
	RedirectResponse,
	Request
};
use Illuminate\Support\Facades\{
	Crypt,
	DB,
	Hash,
	Log,
	Route,
	View as ViewFacade
};
use Symfony\Component\HttpFoundation\Response;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class JobApplicationController extends Controller
{
	use ChecksLogin, ChecksPermissions;

	public function index(Request $request): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		return $this->measureProfile(function () use ($request, $action, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, PermissionsConstants::MNG_JB_APL, ViewsConstants::JB_APL . '.index'))) return $c;
			Log::debug($method . ' filters', $request->only(['start_date', 'end_date', 'job']));
			$stages = JobStage::whereCreatedBy($user?->creatorId())->orderBy('order')->get();
			$jobs = Job::whereCreatedBy($user?->creatorId())->pluck('title', 'id')->prepend('All', '');
			$filter = [
				'start_date' => $request->start_date ?? now()->subMonth()->toDateString(),
				'end_date'   => $request->end_date ?? now()->addHour()->toDateTimeString(),
				'job'        => $request->job ?? '',
			];
			$view = ViewsConstants::JB_APL . '.index';
			if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method);
			return ViewFacade::make($view, compact('stages', 'jobs', 'filter'));
		}, ['route' => Route::getCurrentRoute()?->getName()]);
	}

	public function create(): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		$request = request();
		return $this->measureProfile(function () use ($request, $action, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, 'create job application', ViewsConstants::JB_APL . '.index'))) return $c;
			$jobs = Job::whereCreatedBy($user?->creatorId())->pluck('title', 'id')->prepend('-', '');
			$questions = CustomQuestion::whereCreatedBy($user?->creatorId())->get();
			$view = ViewsConstants::JB_APL . '.create';
			if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method);
			return ViewFacade::make($view, compact('jobs', 'questions'));
		}, ['route' => Route::getCurrentRoute()?->getName()]);
	}

	public function store(Request $request): RedirectResponse
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		return $this->measureProfile($action, function () use ($request, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, 'create job application', ViewsConstants::JB_APL . '.index'))) return $c;
			$validated = $request->validate([
				'job'    => 'required',
				'name'   => 'required',
				'email'  => 'required|email',
				'phone'  => 'required',
				'profile' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:' . SettingsConstants::MAX_U_SIZE_DEF,
				'resume'  => 'nullable|mimes:jpeg,png,jpg,gif,svg,pdf,doc,zip|max:' . SettingsConstants::MAX_U_SIZE_DEF,
			]);
			Log::debug($method . ' validated', $validated);
			$profile = self::storeUpload($request, 'profile', 'uploads/job/profile');
			$resume  = self::storeUpload($request, 'resume', 'uploads/job/resume');
			$stageId = JobStage::whereCreatedBy($user?->creatorId())->value('id') ?? 1;
			JobApplication::create([
				'job'             => $validated['job'],
				'name'            => $validated['name'],
				'email'           => $validated['email'],
				'phone'           => $validated['phone'],
				'profile'         => $profile,
				'resume'          => $resume,
				'cover_letter'    => $request->cover_letter,
				'dob'             => $request->dob,
				'gender'          => $request->gender,
				'country'         => $request->country,
				'state'           => $request->state,
				'city'            => $request->city,
				'stage'           => $stageId,
				'custom_question' => json_encode($request->question ?? []),
				DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
			]);
			return redirect()->route(ViewsConstants::JB_APL . '.index')->with('success', __('Job application successfully created.'));
		}, ['route' => Route::getCurrentRoute()?->getName()]);
	}

	public function show(string $encId): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		$request = request();
		return $this->measureProfile(function () use ($encId, $request, $action, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, 'show job application', ViewsConstants::JB_APL . '.index'))) return $c;
			try {
				$id = Crypt::decrypt($encId);
			} catch (\Throwable $e) {
				return redirect()->back()->with('error', __('Job application not found.'));
			}
			$jobApplication = JobApplication::findOrFail($id);
			if (($jobApplication[DatabaseConstants::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId()) return defaultPermissionDenial($request, new \Exception('owner'), $method);
			$notes = JobApplicationNote::whereApplicationId($id)->get();
			$stages = JobStage::whereCreatedBy($user?->creatorId())->get();
			$view = ViewsConstants::JB_APL . '.show';
			if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method);
			return ViewFacade::make($view, compact('jobApplication', 'notes', 'stages'));
		}, ['route' => Route::getCurrentRoute()?->getName(), 'encId' => $encId]);
	}

	public function destroy(Request $request, JobApplication $jobApplication): RedirectResponse
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		return $this->measureProfile($action, function () use ($request, $jobApplication, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, 'delete job application', ViewsConstants::JB_APL . '.index'))) return $c;
			if (($jobApplication[DatabaseConstants::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId()) return defaultPermissionDenial($request, new \Exception('owner'), $method, route(ViewsConstants::JB_APL . '.index'));
			$jobApplication->delete();
			collect([
				$jobApplication->profile ? 'uploads/job/profile/' . $jobApplication->profile : '',
				$jobApplication->resume ? 'uploads/job/resume/' . $jobApplication->resume : '',
			])->filter()->each(fn($path) => Utility::changeStorageLimit($user?->creatorId(), $path));
			return redirect()->route(ViewsConstants::JB_APL . '.index')->with('success', __('Job application successfully deleted.'));
		}, ['route' => Route::getCurrentRoute()?->getName()]);
	}

	public function order(Request $request): RedirectResponse
	{
		$action = __FUNCTION__;
		return $this->measureProfile($action, function () use ($request) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			if (($c = self::guard($request, 'move job application', ViewsConstants::JB_APL . '.index'))) return $c;
			collect($request->input('order', []))->each(function ($item, $key) use ($request) {
				JobApplication::whereKey($item)->update(['order' => $key, 'stage' => $request->stage_id]);
			});
			return back()->with('success', __('Order updated.'));
		}, ['route' => Route::getCurrentRoute()?->getName()]);
	}

	public const ADD_SK = 'addSkill';
	public function addSkill(Request $request, string|int $id): RedirectResponse
	{
		$action = __FUNCTION__;
		return $this->measureProfile($action, function () use ($request, $id) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			if (($c = self::guard($request, 'add job application skill', ViewsConstants::JB_APL . '.index'))) return $c;
			$request->validate(['skill' => 'required']);
			JobApplication::whereKey($id)->update(['skill' => $request->skill]);
			return back()->with('success', __('Skill added.'));
		}, ['route' => Route::getCurrentRoute()?->getName(), 'id' => $id]);
	}

	public const ADD_NT = 'addNote';
	public function addNote(Request $request, string|int $id): RedirectResponse
	{
		$action = __FUNCTION__;
		return $this->measureProfile($action, function () use ($request, $id) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, 'add job application note', ViewsConstants::JB_APL . '.index'))) return $c;
			$request->validate(['note' => 'required']);
			JobApplicationNote::create([
				'application_id' => $id,
				'note'           => $request->note,
				'note_created'   => auth()->id(),
				DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
			]);
			return back()->with('success', __('Note added.'));
		}, ['route' => Route::getCurrentRoute()?->getName(), 'id' => $id]);
	}

	public const DST_NT = 'destroyNote';
	public function destroyNote(string|int $id): RedirectResponse
	{
		$action = __FUNCTION__;
		$request = request();
		return $this->measureProfile($action, function () use ($id, $request) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			if (($c = self::guard($request, 'delete job application note', ViewsConstants::JB_APL . '.index'))) return $c;
			JobApplicationNote::whereKey($id)->delete();
			return back()->with('success', __('Note deleted.'));
		}, ['route' => Route::getCurrentRoute()?->getName(), 'id' => $id]);
	}

	public function rating(Request $request, string|int $id): JsonResponse
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		return $this->measureProfile($action, function () use ($request, $id, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return response()->json(['error' => __('Authentication required.')], Response::HTTP_UNAUTHORIZED);
			if (($c = self::guard($request, 'edit job application', ViewsConstants::JB_APL . '.index')) !== true) return $c;
			JobApplication::whereKey($id)->update(['rating' => $request->rating]);
			Log::debug($method . ' updated', ['id' => $id, 'rating' => $request->rating]);
			return response()->json(['success' => true], Response::HTTP_OK);
		}, ['id' => $id, 'rating' => $request->rating]);
	}

	public function archive(string|int $id): RedirectResponse
	{
		$action = __FUNCTION__;
		$request = request();
		return $this->measureProfile($action, function () use ($id, $request) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			if (($c = self::guard($request, 'archive job application', ViewsConstants::JB_APL . '.index')) !== true) return $c;
			$app = JobApplication::findOrFail($id);
			$app->update(['is_archive' => !$app->is_archive]);
			$route = $app->is_archive ? ViewsConstants::JB_APL . '.candidate' : ViewsConstants::JB_APL . '.index';
			$msg = $app->is_archive ? __('Added to archive.') : __('Removed from archive.');
			return redirect()->route($route)->with('success', $msg);
		}, ['id' => $id]);
	}

	public function candidate(): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		$request = request();
		return $this->measureProfile($action, function () use ($request, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, 'manage job onBoard', ViewsConstants::JB_APL . '.index')) !== true) return $c;
			$archived = JobApplication::whereCreatedBy($user?->creatorId())->whereIsArchive(1)->get();
			$view = ViewsConstants::JB_APL . '.candidate';
			if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method);
			return ViewFacade::make($view, compact('archived'));
		}, []);
	}

	public const JBB_CRT = 'jobBoardCreate';
	public function jobBoardCreate(string|int $id): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		$request = request();
		return $this->measureProfile($action, function () use ($id, $request, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, 'manage job onBoard', ViewsConstants::JB_APL . '.index')) !== true) return $c;
			$view = ViewsConstants::JB_APL . '.onboardCreate';
			if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method);
			return ViewFacade::make($view, [
				'id'               => $id,
				'status'           => JobOnBoard::$status,
				'job_type'         => JobOnBoard::$job_type,
				'salary_duration'  => JobOnBoard::$salary_duration,
				'salary_type'      => PayslipType::whereCreatedBy($user?->creatorId())->pluck('name', 'id'),
				'applications'     => InterviewSchedule::select('interview_schedules.*', 'job_applications.name')
					->join('job_applications', 'interview_schedules.candidate', '=', 'job_applications.id')
					->where('interview_schedules.created_by', $user?->creatorId())
					->pluck('name', 'candidate')
					->prepend('-', '')
			]);
		}, ['id' => $id]);
	}

	public const JB_OB = 'jobOnBoard';
	public function jobOnBoard(): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		$request = request();
		return $this->measureProfile($action, function () use ($request, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, 'manage job onBoard', ViewsConstants::JB_APL . '.index')) !== true) return $c;
			$boards = JobOnBoard::whereCreatedBy($user?->creatorId())->with('applications')->get();
			$view = ViewsConstants::JB_APL . '.onboard';
			if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method);
			return ViewFacade::make($view, compact('boards'));
		}, []);
	}

	public const JBB_ST = 'jobBoardStore';
	public function jobBoardStore(Request $request, string|int $id): RedirectResponse
	{
		$action = __FUNCTION__;
		return $this->measureProfile($action, function () use ($request, $id) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, 'manage job onBoard', ViewsConstants::JB_APL . '.index')) !== true) return $c;
			$validated = $request->validate([
				'joining_date'    => 'required|date',
				'job_type'        => 'required',
				'days_of_week'    => 'required|gt:0',
				'salary'          => 'required|gt:0',
				'salary_type'     => 'required',
				'salary_duration' => 'required',
				'status'          => 'required',
			]);
			$application = $id === '0' ? $request->application : $id;
			JobOnBoard::create(array_merge($validated, [
				'application' => $application,
				DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
			]));
			InterviewSchedule::whereCandidate($application)->delete();
			return redirect()->route(ViewsConstants::JB_APL . '.onboard')->with('success', __('Candidate added to board.'));
		}, ['id' => $id]);
	}

	public const JBB_UPD = 'jobBoardUpdate';
	public function jobBoardUpdate(Request $request, string|int $id): RedirectResponse
	{
		$action = __FUNCTION__;
		return $this->measureProfile($action, function () use ($request, $id) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			if (($c = self::guard($request, 'manage job onBoard', ViewsConstants::JB_APL . '.index')) !== true) return $c;
			$request->validate([
				'joining_date'    => 'required|date',
				'job_type'        => 'required',
				'days_of_week'    => 'required',
				'salary'          => 'required',
				'salary_type'     => 'required',
				'salary_duration' => 'required',
				'status'          => 'required',
			]);
			JobOnBoard::whereKey($id)->update($request->only([
				'joining_date',
				'job_type',
				'days_of_week',
				'salary',
				'salary_type',
				'salary_duration',
				'status',
			]));
			return redirect()->route(ViewsConstants::JB_APL . '.onboard')->with('success', __('Board candidate updated.'));
		}, ['id' => $id]);
	}

	public const JBB_ED = 'jobBoardEdit';
	public function jobBoardEdit(string|int $id): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		$request = request();
		return $this->measureProfile($action, function () use ($id, $request, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, 'manage job onBoard', ViewsConstants::JB_APL . '.index')) !== true) return $c;
			$view = ViewsConstants::JB_APL . '.onboardEdit';
			if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method);
			return ViewFacade::make($view, [
				'jobOnBoard'      => JobOnBoard::findOrFail($id),
				'status'          => JobOnBoard::$status,
				'job_type'        => JobOnBoard::$job_type,
				'salary_duration' => JobOnBoard::$salary_duration,
				'salary_type'     => PayslipType::whereCreatedBy($user?->creatorId())->pluck('name', 'id'),
			]);
		}, ['id' => $id]);
	}

	public const JBB_DEL = 'jobBoardDelete';
	public function jobBoardDelete(string|int $id): RedirectResponse
	{
		$action = __FUNCTION__;
		$request = request();
		return $this->measureProfile($action, function () use ($id, $request) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			if (($c = self::guard($request, 'manage job onBoard', ViewsConstants::JB_APL . '.index')) !== true) return $c;
			JobOnBoard::whereKey($id)->delete();
			return back()->with('success', __('On-board deleted.'));
		}, ['id' => $id]);
	}

	public const JBB_CV = 'jobBoardConvert';
	public function jobBoardConvert(string|int $id): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		$request = request();
		return $this->measureProfile($action, function () use ($id, $request, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, 'manage job onBoard', ViewsConstants::JB_APL . '.index')) !== true) return $c;
			$onBoard = JobOnBoard::findOrFail($id);
			$view = ViewsConstants::JB_APL . '.convert';
			if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method);
			return ViewFacade::make($view, [
				'jobOnBoard'       => $onBoard,
				'employees'        => User::whereCreatedBy($user?->creatorId())->get(),
				'employeesId'      => $user?->employeeIdFormat($this->employeeNumber()),
				'departments'      => Department::whereCreatedBy($user?->creatorId())->pluck(CompaniesConstants::COL_DEP_NM, 'id'),
				'designations'     => Designation::whereCreatedBy($user?->creatorId())->pluck('name', 'id'),
				'documents'        => Document::whereCreatedBy($user?->creatorId())->get(),
				'branches'         => Branch::whereCreatedBy($user?->creatorId())->pluck(CompaniesConstants::COL_BRC_NM, 'id'),
				'company_settings' => Utility::settings(),
			]);
		}, ['id' => $id]);
	}

	public const JBB_CV_DT = 'jobBoardConvertData';
	public function jobBoardConvertData(Request $request, string|int $id): RedirectResponse
	{
		$action = __FUNCTION__;
		return $this->measureProfile($action, function () use ($request, $id) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$authUser = $userOrRedirect;
			if (($c = self::guard($request, 'manage job onBoard', ViewsConstants::JB_APL . '.index')) !== true) return $c;
			$request->validate([
				'name'           => 'required',
				'dob'            => 'required|date',
				'gender'         => 'required',
				'phone'          => 'required',
				'address'        => 'required',
				'email'          => 'required|email|unique:users',
				'password'       => 'required',
				'department_id'  => 'required',
				'designation_id' => 'required',
				'document.*'     => 'nullable|mimes:jpeg,png,jpg,gif,svg,pdf,doc,zip|max:' . SettingsConstants::MAX_U_SIZE_DEF,
			]);
			return DB::transaction(function () use ($request, $id, $authUser) {
				$creator = $authUser?->creatorId();
				$plan = Plan::find($authUser?->plan);
				$limit = $plan[PlansConstants::COL_MAX_U] ?? -1;
				$current = User::whereType('employee')->whereCreatedBy($creator)->count();
				if ($limit !== -1 && $current >= $limit) return back()->with('error', __('Employee limit reached.'));

				$userData = array_merge(
					$request->only(['name', 'email']),
					[
						'password'                   => Hash::make($request->password),
						'type'                       => 'employee',
						UsersConstants::COL_LG       => DatabaseConstants::DEFAULT_LANG,
						DatabaseConstants::COL_TABLE_CREATOR => $creator,
					]
				);
				$newUser = tap(User::create($userData), fn($u) => $u->assignRole('Employee'));

				$employeeData = collect($request->only([
					'name',
					'dob',
					'gender',
					'phone',
					'address',
					'email',
					'branch_id',
					'department_id',
					'designation_id',
					'company_doj',
					'account_holder_name',
					'account_number',
					'bank_name',
					'bank_identifier_code',
					'branch_location',
					'tax_payer_id',
				]))->merge([
					UsersConstants::COL_USER_ID     => $newUser?->id,
					'password'                      => Hash::make($request->password),
					UsersConstants::COL_EMP_ID      => $this->employeeNumber(),
					'documents'                     => $request->hasFile('document') ? implode(',', array_keys($request->file('document'))) : null,
					DatabaseConstants::COL_TABLE_CREATOR => $creator,
				])->all();
				$employee = Employee::create($employeeData);

				JobOnBoard::whereKey($id)->update(['convert_to_employee' => $employee->id]);

				foreach ($request->file('document', []) as $docId => $file) {
					$storedName = Utility::uploadFile(
						$request,
						"document.$docId",
						pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_' . time() . '.' . $file->getClientOriginalExtension(),
						'uploads/document',
						[]
					)['url'] ?? '';
					EmployeeDocument::create([
						UsersConstants::COL_EMP_ID         => $employee->employee_id,
						'document_id'                      => $docId,
						'document_value'                   => $storedName,
						DatabaseConstants::COL_TABLE_CREATOR   => $creator,
					]);
				}

				$resp = null;
				$settings = Utility::settings();
				if (($settings['new_user'] ?? false)) {
					$resp = Utility::sendEmailTemplate(
						'new_user',
						[$newUser->id => $newUser?->email],
						['email' => $newUser?->email, 'password' => $request->password]
					);
				}

				$msg = __('Application converted.') . (!empty($resp) && !$resp['is_success'] && ($resp['error'] ?? null) ? '<br><span class="text-danger">' . $resp['error'] . '</span>' : '');
				return back()->with('success', $msg);
			});
		}, ['id' => $id]);
	}

	public const GET_BY_JB = 'getByJob';
	public function getByJob(Request $request): JsonResponse
	{
		$action = __FUNCTION__;
		return $this->measureProfile($action, function () use ($request) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return response()->json(['error' => __('Authentication required.')], Response::HTTP_UNAUTHORIZED);
			if (($c = self::guard($request, PermissionsConstants::MNG_JB_APL, ViewsConstants::JB_APL . '.index')) !== true) return $c;
			$job = Job::findOrFail($request->id)->makeHidden([]);
			$job->applicant       = $job->applicant ? explode(',', $job->applicant) : [];
			$job->visibility      = $job->visibility ? explode(',', $job->visibility) : [];
			$job->custom_question = $job->custom_question ? explode(',', $job->custom_question) : [];
			return response()->json($job, Response::HTTP_OK);
		}, ['job_id' => $request->id]);
	}

	public const STG_CG = 'stageChange';
	public function stageChange(Request $request): JsonResponse
	{
		$action = __FUNCTION__;
		return $this->measureProfile($action, function () use ($request) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return response()->json(['error' => __('Authentication required.')], Response::HTTP_UNAUTHORIZED);
			if (($c = self::guard($request, 'move job application', ViewsConstants::JB_APL . '.index')) !== true) return $c;
			JobApplication::whereKey($request->schedule_id)->update(['stage' => $request->stage]);
			return response()->json(['success' => __('Stage changed.')], Response::HTTP_OK);
		}, ['schedule_id' => $request->schedule_id, 'stage' => $request->stage]);
	}

	public const OFL_PDF = 'offerLetterPdf';
	public function offerLetterPdf(string|int $id): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		$request = request();
		return $this->measureProfile($action, function () use ($id, $request, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, PermissionsConstants::MNG_JB_APL, ViewsConstants::JB_APL . '.index')) !== true) return $c;
			$tpl = GeneratedOfferLetter::where(['lang' => $user?->currentLanguage(), DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId()])->firstOrFail();
			$tpl->content = GeneratedOfferLetter::replaceVariable($tpl->content, $this->loadOfferLetterData($id));
			$candidate = JobApplication::find($id);
			$view = ViewsConstants::JB_APL . '.template.$method';
			if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method);
			return ViewFacade::make($view, ['Offerletter' => $tpl, 'name' => $candidate]);
		}, ['id' => $id]);
	}

	public const OFL_DC = 'offerLetterDoc';
	public function offerLetterDoc(string|int $id): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		$request = request();
		return $this->measureProfile($action, function () use ($id, $request, $method) {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
			$user = $userOrRedirect;
			if (($c = self::guard($request, PermissionsConstants::MNG_JB_APL, ViewsConstants::JB_APL . '.index')) !== true) return $c;
			$tpl = GeneratedOfferLetter::where(['lang' => $user?->currentLanguage(), DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId()])->firstOrFail();
			$tpl->content = GeneratedOfferLetter::replaceVariable($tpl->content, $this->loadOfferLetterData($id));
			$candidate = JobApplication::find($id);
			$view = ViewsConstants::JB_APL . '.template.offerletterdocx';
			if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method);
			return ViewFacade::make($view, ['Offerletter' => $tpl, 'name' => $candidate]);
		}, ['id' => $id]);
	}

	/**
	 * Centralizes permission + login checks and common error handling
	 */
	private static function withAuth(
		Request $request,
		string  $perm,
		\Closure $fn
	): RedirectResponse|JsonResponse|View {
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		try {
			if (!$user?->can($perm))
				throw new AuthorizationException();

			return $fn($user);
		} catch (AuthorizationException $e) {
			return defaultPermissionDenial(
				$request,
				$e,
				__CLASS__ . '::' . __FUNCTION__
			);
		} catch (\Throwable $e) {
			return defaultUndefinedException(
				$request,
				$e,
				__CLASS__ . '::' . __FUNCTION__
			);
		}
	}

	/**
	 * DRY upload routine returning stored filename or ''
	 */
	private static function storeUpload(
		Request $request,
		string  $field,
		string  $dir
	): string {
		if (!$request->hasFile($field)) return '';

		$file = $request->file($field);
		$fileName =
			pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) .
			'_' .
			time() .
			'.' .
			$file->getClientOriginalExtension();

		$upload = Utility::uploadFile($request, $field, $fileName, $dir, []);
		return $upload['flag'] === 1 ? $upload['url'] : '';
	}

	private function employeeNumber(): RedirectResponse|int
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		$latest = Employee::whereCreatedBy($user?->creatorId())
			->latest()
			->value(UsersConstants::COL_EMP_ID);
		return ($latest ?? 0) + 1;
	}

	private function loadOfferLetterData(string|int $id): array
	{
		$onBoard = JobOnBoard::findOrFail($id);
		$application = JobApplication::findOrFail($onBoard->application);
		$jobTitle = Job::find($application->job)->title ?? '';
		$salary = PayslipType::find($onBoard->salary_type)?->name ?? '';

		return [
			'applicant_name'        => $application->name,
			'app_name'              => config('app.name'),
			'job_title'             => $jobTitle,
			'job_type'              => $onBoard->job_type ?? '',
			'start_date'            => $onBoard->joining_date,
			'workplace_location'    => optional($application->jobs->branches)->name ?? '',
			'days_of_week'          => $onBoard->days_of_week ?? '',
			'salary'                => $onBoard->salary ?? '',
			'salary_type'           => $salary,
			'salary_duration'       => $onBoard->salary_duration ?? '',
			'offer_expiration_date' => $onBoard->joining_date ?? ''
		];
	}
}
