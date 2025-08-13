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
use Illuminate\Contracts\View\View;
use Illuminate\Http\{
	JsonResponse,
	RedirectResponse,
	Request
};
use Illuminate\Support\Facades\{
	Crypt,
	DB,
	Hash
};
use Symfony\Component\HttpFoundation\Response;

class JobApplicationController extends Controller
{
	use ChecksLogin;

	public function index(Request $request): RedirectResponse|View
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		return self::withAuth($request, PermissionsConstants::MNG_JB_APL, function () use ($request, $user) {
			$stages = JobStage::whereCreatedBy($user?->creatorId())
				->orderBy('order')
				->get();
			$jobs = Job::whereCreatedBy($user?->creatorId())
				->pluck('title', 'id')
				->prepend('All', '');
			$filter = [
				'start_date' => $request->start_date
					?? now()->subMonth()->toDateString(),
				'end_date'   => $request->end_date
					?? now()->addHour()->toDateTimeString(),
				'job'        => $request->job ?? ''
			];
			return view(ViewsConstants::JB_APL . '.index', compact('stages', 'jobs', 'filter'));
		});
	}

	public function create(): RedirectResponse|View
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		return self::withAuth(request(), 'create job application', function () use ($user) {
			$jobs = Job::whereCreatedBy($user?->creatorId())
				->pluck('title', 'id')
				->prepend('-', '');

			$questions = CustomQuestion::whereCreatedBy(
				$user?->creatorId()
			)->get();

			return view(ViewsConstants::JB_APL . '.create', compact('jobs', 'questions'));
		});
	}

	public function store(Request $request): RedirectResponse
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		return self::withAuth($request, 'create job application', function () use (
			$request,
			$user
		) {
			$validated = $request->validate([
				'job'    => 'required',
				'name'   => 'required',
				'email'  => 'required|email',
				'phone'  => 'required',
				'profile' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:' . SettingsConstants::MAX_U_SIZE_DEF,
				'resume'  => 'nullable|mimes:jpeg,png,jpg,gif,svg,pdf,doc,zip|max:' . SettingsConstants::MAX_U_SIZE_DEF
			]);

			$profile = self::storeUpload($request, 'profile', 'uploads/job/profile');
			$resume = self::storeUpload($request, 'resume', 'uploads/job/resume');

			$stageId = JobStage::whereCreatedBy(
				$user?->creatorId()
			)->value('id') ?? 1;

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
				'custom_question' => json_encode($request->question),
				DatabaseConstants::TABLE_CREATOR      => $user?->creatorId()
			]);

			return redirect()
				->route('job-application.index')
				->with('success', __('Job application successfully created.'));
		});
	}

	public function show(string $encId): RedirectResponse|View
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		return self::withAuth(request(), 'show job application', function () use (
			$encId,
			$user
		) {
			$id = Crypt::decrypt($encId);
			$jobApplication = JobApplication::findOrFail($id);
			$notes = JobApplicationNote::whereApplicationId($id)->get();
			$stages = JobStage::whereCreatedBy($user?->creatorId())->get();

			return view(
				ViewsConstants::JB_APL . '.show',
				compact('jobApplication', 'notes', 'stages')
			);
		});
	}

	public function destroy(
		Request            $request,
		JobApplication $jobApplication
	): RedirectResponse {
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		return self::withAuth($request, 'delete job application', function () use (
			$jobApplication,
			$user
		) {
			$jobApplication->delete();
			collect([
				$jobApplication->profile ? 'uploads/job/profile/' . $jobApplication->profile : '',
				$jobApplication->resume ? 'uploads/job/resume/' . $jobApplication->resume : ''
			])->filter()
				->each(fn($path) => Utility::changeStorageLimit(
					$user?->creatorId(),
					$path
				));

			return redirect()
				->route('job-application.index')
				->with('success', __('Job application successfully deleted.'));
		});
	}

	public function order(Request $request): RedirectResponse
	{
		return self::withAuth($request, 'move job application', function () use (
			$request
		) {
			collect($request->input('order', []))
				->each(fn($item, $key) => JobApplication::whereKey($item)
					->update(['order' => $key, 'stage' => $request->stage_id]));
			return back()->with('success', __('Order updated.'));
		});
	}

	public const ADD_SK = 'addSkill';
	public function addSkill(Request $request, string|int $id): RedirectResponse
	{
		return self::withAuth($request, 'add job application skill', function () use (
			$request,
			$id
		) {
			$request->validate(['skill' => 'required']);
			JobApplication::whereKey($id)->update(['skill' => $request->skill]);
			return back()->with('success', __('Skill added.'));
		});
	}

	public const ADD_NT = 'addNote';
	public function addNote(Request $request, string|int $id): RedirectResponse
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		return self::withAuth($request, 'add job application note', function () use (
			$request,
			$id,
			$user
		) {
			$request->validate(['note' => 'required']);
			JobApplicationNote::create([
				'application_id' => $id,
				'note'           => $request->note,
				'note_created'   => auth()->id(),
				DatabaseConstants::TABLE_CREATOR     => $user?->creatorId()
			]);
			return back()->with('success', __('Note added.'));
		});
	}

	public const DST_NT = 'destroyNote';
	public function destroyNote(string|int $id): RedirectResponse
	{
		return self::withAuth(request(), 'delete job application note', function () use (
			$id
		) {
			JobApplicationNote::whereKey($id)->delete();
			return back()->with('success', __('Note deleted.'));
		});
	}

	public function rating(Request $request, string|int $id): JsonResponse
	{
		return self::withAuth($request, 'edit job application', function () use (
			$request,
			$id
		) {
			JobApplication::whereKey($id)->update(['rating' => $request->rating]);
			return response()->json(['success' => true]);
		});
	}

	public function archive(string|int $id): RedirectResponse
	{
		return self::withAuth(request(), 'archive job application', function () use (
			$id
		) {
			$app = JobApplication::findOrFail($id);
			$app->update(['is_archive' => !$app->is_archive]);
			$route = $app->is_archive
				? 'job.application.candidate'
				: 'job-application.index';
			$msg = $app->is_archive
				? __('Added to archive.')
				: __('Removed from archive.');
			return redirect()->route($route)->with('success', $msg);
		});
	}

	public function candidate(): RedirectResponse|View
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		return self::withAuth(request(), 'manage job onBoard', function () use ($user) {
			$archived = JobApplication::whereCreatedBy($user?->creatorId())
				->whereIsArchive(1)
				->get();
			return view(ViewsConstants::JB_APL . '.candidate', compact('archived'));
		});
	}

	public const JBB_CRT = 'jobBoardCreate';
	public function jobBoardCreate(string|int $id): RedirectResponse|View
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		return view(
			ViewsConstants::JB_APL . '.onboardCreate',
			[
				'id'             => $id,
				'status'         => JobOnBoard::$status,
				'job_type'       => JobOnBoard::$job_type,
				'salary_duration' => JobOnBoard::$salary_duration,
				'salary_type'    => PayslipType::whereCreatedBy($user?->creatorId())
					->pluck('name', 'id'),
				'applications'   => InterviewSchedule::select(
					'interview_schedules.*',
					'job_applications.name'
				)
					->join(
						'job_applications',
						'interview_schedules.candidate',
						'=',
						'job_applications.id'
					)
					->where(
						'interview_schedules.created_by',
						$user?->creatorId()
					)
					->pluck('name', 'candidate')
					->prepend('-', '')
			]
		);
	}

	public const JB_OB = 'jobOnBoard';
	public function jobOnBoard(): RedirectResponse|View
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		return self::withAuth(request(), 'manage job onBoard', function () use ($user) {
			$boards = JobOnBoard::whereCreatedBy($user?->creatorId())
				->with('applications')
				->get();
			return view(ViewsConstants::JB_APL . '.onboard', compact('boards'));
		});
	}

	public const JBB_ST = 'jobBoardStore';
	public function jobBoardStore(Request $request, string|int $id): RedirectResponse
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		return self::withAuth($request, 'manage job onBoard', function () use (
			$request,
			$id,
			$user
		) {
			$validated = $request->validate([
				'joining_date'    => 'required|date',
				'job_type'        => 'required',
				'days_of_week'    => 'required|gt:0',
				'salary'          => 'required|gt:0',
				'salary_type'     => 'required',
				'salary_duration' => 'required',
				'status'          => 'required'
			]);
			$application = $id === '0' ? $request->application : $id;
			JobOnBoard::create(
				array_merge(
					$validated,
					[
						'application' => $application,
						DatabaseConstants::TABLE_CREATOR  => $user?->creatorId()
					]
				)
			);
			InterviewSchedule::whereCandidate($application)->delete();
			return redirect()->route('job.on.board')
				->with('success', __('Candidate added to board.'));
		});
	}

	public const JBB_UPD = 'jobBoardUpdate';
	public function jobBoardUpdate(Request $request, string|int $id): RedirectResponse
	{
		return self::withAuth($request, 'manage job onBoard', function () use (
			$request,
			$id
		) {
			$request->validate([
				'joining_date'    => 'required|date',
				'job_type'        => 'required',
				'days_of_week'    => 'required',
				'salary'          => 'required',
				'salary_type'     => 'required',
				'salary_duration' => 'required',
				'status'          => 'required'
			]);
			JobOnBoard::whereKey($id)->update($request->only([
				'joining_date',
				'job_type',
				'days_of_week',
				'salary',
				'salary_type',
				'salary_duration',
				'status'
			]));
			return redirect()->route('job.on.board')
				->with('success', __('Board candidate updated.'));
		});
	}

	public const JBB_ED = 'jobBoardEdit';
	public function jobBoardEdit(string|int $id): RedirectResponse|View
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		return view(
			ViewsConstants::JB_APL . '.onboardEdit',
			[
				'jobOnBoard'      => JobOnBoard::findOrFail($id),
				'status'          => JobOnBoard::$status,
				'job_type'        => JobOnBoard::$job_type,
				'salary_duration' => JobOnBoard::$salary_duration,
				'salary_type'     => PayslipType::whereCreatedBy($user?->creatorId())
					->pluck('name', 'id')
			]
		);
	}

	public const JBB_DEL = 'jobBoardDelete';
	public function jobBoardDelete(string|int $id): RedirectResponse
	{
		JobOnBoard::whereKey($id)->delete();
		return back()->with('success', __('On‑board deleted.'));
	}

	public const JBB_CV = 'jobBoardConvert';
	public function jobBoardConvert(string|int $id): RedirectResponse|View
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		$onBoard = JobOnBoard::findOrFail($id);
		return view(
			ViewsConstants::JB_APL . '.convert',
			[
				'jobOnBoard'   => $onBoard,
				'employees'    => User::whereCreatedBy($user?->creatorId())->get(),
				'employeesId'  => $user?->employeeIdFormat($this->employeeNumber()),
				'departments'  => Department::whereCreatedBy($user?->creatorId())
					->pluck(CompaniesConstants::COL_DEP_NM, 'id'),
				'designations' => Designation::whereCreatedBy($user?->creatorId())
					->pluck('name', 'id'),
				'documents'    => Document::whereCreatedBy($user?->creatorId())->get(),
				'branches'     => Branch::whereCreatedBy($user?->creatorId())
					->pluck(CompaniesConstants::COL_BRC_NM, 'id'),
				'company_settings' => Utility::settings()
			]
		);
	}

	public const JBB_CV_DT = 'jobBoardConvertData';
	public function jobBoardConvertData(
		Request   $request,
		string|int $id
	): RedirectResponse {
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		return self::withAuth(
			$request,
			'manage job onBoard',
			function () use ($request, $id, $user) {
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
					'document.*'     => 'nullable|mimes:jpeg,png,jpg,gif,svg,pdf,doc,zip|max:' . SettingsConstants::MAX_U_SIZE_DEF
				]);

				return DB::transaction(function () use ($request, $id, $user) {
					$creator = $user?->creatorId();
					$plan   = Plan::find($user?->plan);

					$exceeds = User::whereType('employee')
						->whereCreatedBy($creator)
						->count() >= $plan[PlansConstants::COL_MAX_U]
						&& $plan[PlansConstants::COL_MAX_U] !== -1;

					if ($exceeds)
						return back()->with('error', __('Employee limit reached.'));

					/* --------------------------- create user --------------------------- */
					$userData = array_merge(
						$request->only(['name', 'email']),
						[
							'password'   => Hash::make($request->password),
							'type'       => 'employee',
							UsersConstants::COL_LG       => DatabaseConstants::DEFAULT_LANG,
							DatabaseConstants::TABLE_CREATOR => $creator
						]
					);
					$user = tap(User::create($userData), fn($u) => $u->assignRole('Employee'));

					/* ------------------------- create employee ------------------------ */
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
						'tax_payer_id'
					]))
						->merge([
							UsersConstants::COL_USER_ID     => $user?->id,
							'password'    => Hash::make($request->password),
							UsersConstants::COL_EMP_ID => $this->employeeNumber(),
							'documents'   => $request->hasFile('document')
								? implode(',', array_keys($request->file('document')))
								: null,
							DatabaseConstants::TABLE_CREATOR  => $creator
						])
						->all();
					$employee = Employee::create($employeeData);

					/* ----------------- link board record to new employee -------------- */
					JobOnBoard::whereKey($id)
						->update(['convert_to_employee' => $employee->id]);

					/* ---------------------------- documents --------------------------- */
					foreach ($request->file('document', []) as $docId => $file) {
						$storedName = Utility::uploadFile(
							$request,
							"document.$docId",
							pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
								. '_' . time() . '.' . $file->getClientOriginalExtension(),
							'uploads/document',
							[]
						)['url'] ?? '';
						EmployeeDocument::create([
							UsersConstants::COL_EMP_ID    => $employee->employee_id,
							'document_id'    => $docId,
							'document_value' => $storedName,
							DatabaseConstants::TABLE_CREATOR     => $creator
						]);
					}

					/* ------------------------------- mail ----------------------------- */
					$settings = Utility::settings();
					if (($settings['new_user'] ?? false))
						$resp = Utility::sendEmailTemplate(
							'new_user',
							[$user->id > $user?->email],
							['email' => $user?->email, 'password' => $request->password]
						);

					$msg = __('Application converted.')
						. (!empty($resp)
							&& !$resp['is_success']
							&& $resp['error']
							? '<br><span class="text-danger">' . $resp['error'] . '</span>'
							: '');

					return back()->with('success', $msg);
				});
			}
		);
	}

	public const GET_BY_JB = 'getByJob';
	public function getByJob(Request $request): JsonResponse
	{
		$job = Job::findOrFail($request->id)->makeHidden([]);
		$job->applicant      = $job->applicant ? explode(',', $job->applicant) : [];
		$job->visibility     = $job->visibility ? explode(',', $job->visibility) : [];
		$job->custom_question = $job->custom_question
			? explode(',', $job->custom_question)
			: [];
		return response()->json($job);
	}

	public const STG_CG = 'stageChange';
	public function stageChange(Request $request): JsonResponse
	{
		JobApplication::whereKey($request->schedule_id)
			->update(['stage' => $request->stage]);
		return response()->json(
			['success' => __('Stage changed.')],
			Response::HTTP_OK
		);
	}

	public const OFL_PDF = 'offerLetterPdf';
	public function offerLetterPdf(string|int $id): RedirectResponse|View
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		$tpl = GeneratedOfferLetter::where([
			'lang'        => $user?->currentLanguage(),
			DatabaseConstants::TABLE_CREATOR  => $user?->creatorId()
		])->firstOrFail();
		$tpl->content = GeneratedOfferLetter::replaceVariable(
			$tpl->content,
			$this->loadOfferLetterData($id)
		);
		$candidate = JobApplication::find($id);
		return view(
			ViewsConstants::JB_APL . '.template.offerletterpdf',
			['Offerletter' => $tpl, 'name' => $candidate]
		);
	}

	public const OFL_DC = 'offerLetterDoc';
	public function offerLetterDoc(string|int $id): RedirectResponse|View
	{
		if (
			($userOrRedirect = self::_checkLogin())
			instanceof RedirectResponse
		) return $userOrRedirect;
		$user = $userOrRedirect;
		$tpl = GeneratedOfferLetter::where([
			'lang'        => $user?->currentLanguage(),
			DatabaseConstants::TABLE_CREATOR  => $user?->creatorId()
		])->firstOrFail();
		$tpl->content = GeneratedOfferLetter::replaceVariable(
			$tpl->content,
			$this->loadOfferLetterData($id)
		);
		$candidate = JobApplication::find($id);
		return view(
			ViewsConstants::JB_APL . '.template.offerletterdocx',
			['Offerletter' => $tpl, 'name' => $candidate]
		);
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
				throw new \Illuminate\Auth\Access\AuthorizationException();

			return $fn($user);
		} catch (\Illuminate\Auth\Access\AuthorizationException $e) {
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
