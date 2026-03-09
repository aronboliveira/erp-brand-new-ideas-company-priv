<?php

namespace App\Http\Controllers\Individuals;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{
	DatabaseConstants as DC,
	UsersConstants as UC,
	ViewsConstants as VW
};
use App\Models\JobStage;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
	RedirectResponse,
	Request
};
use Illuminate\Support\Facades\{
	Log,
	Validator,
	View as ViewFacade
};
use Illuminate\View\View;
use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};

class JobStageController extends Controller
{

	use ChecksLogin, ChecksPermissions;
	private const REDIRECT_INDEX = '/';
	private const PERM_MANAGE = 'manage job stage';
	private const PERM_CREATE = 'create job stage';
	private const PERM_EDIT  = 'edit job stage';
	private const PERM_DELETE = 'delete job stage';
	public const IDX = 'index';
	public const CRT = 'create';
	public const STR = 'store';
	public const SHW = 'show';
	public const EDT = 'edit';
	public const UPD = 'update';
	public const DEL = 'destroy';


	public function index(Request $req): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		return $this->measureProfile($action, function () use ($req, $action, $method) {
			if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
			if (($c = self::guard($req, self::PERM_MANAGE, self::REDIRECT_INDEX)) !== true) return $c;
			try {
				$stages = JobStage::where(DC::COL_TABLE_CREATOR, $u->creatorId())->orderBy('order')->get();
				$view = VW::JB_STG . '.' . $action;
				if (!ViewFacade::exists($view)) return defaultUndefinedException($req, new \RuntimeException('View not found'), $method);
				Log::debug($method . ' loaded', ['count' => $stages->count()]);
				return ViewFacade::make($view, compact('stages'));
			} catch (\Throwable $e) {
				Log::error($method . ' failed', ['error' => $e->getMessage()]);
				return defaultUndefinedException($req, $e, $method);
			}
		}, []);
	}

	public function create(Request $req): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		return $this->measureProfile($action, function () use ($req, $action, $method) {
			if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
			if (($c = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) !== true) return $c;
			$view = VW::JB_STG . '.' . $action;
			if (!ViewFacade::exists($view)) return defaultUndefinedException($req, new \RuntimeException('View not found'), $method);
			return ViewFacade::make($view);
		}, []);
	}

	public function show(Request $req, JobStage $jobStage): View|RedirectResponse
	{
		$action = __FUNCTION__;
		$cls = __CLASS__;
		$method = __METHOD__;
		return $this->measureProfile($action, function () use ($req, $jobStage, $action, $cls, $method) {
			Log::debug($method . ' start', [UC::COL_USER_ID => $req->user()->id, 'stage_id' => $jobStage->id]);
			try {
				if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
				if (($c = self::guard($req, self::PERM_MANAGE, self::REDIRECT_INDEX)) !== true) return $c;
				if ($jobStage->created_by !== $u->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $method);
				$view = VW::JB_STG . '.' . $action;
				if (!ViewFacade::exists($view)) return defaultUndefinedException($req, new \RuntimeException('View not found'), $method);
				return ViewFacade::make($view, compact('jobStage'));
			} catch (\Throwable $e) {
				Log::error($method . ' failed', ['error' => $e->getMessage()]);
				return defaultUndefinedException($req, $e, $cls . '::' . $action);
			}
		}, ['stage_id' => $jobStage->id]);
	}

	public function store(Request $req): RedirectResponse
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		return $this->measureProfile($action, function () use ($req, $method) {
			if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
			if (($c = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) !== true) return $c;
			$v = Validator::make($req->all(), ['title' => 'required']);
			if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
			try {
				JobStage::create(['title' => $req->input('title'), DC::COL_TABLE_CREATOR => $u->creatorId()]);
				Log::debug($method . ' created');
				return redirect()->back()->with('success', __('Job stage successfully created.'));
			} catch (\Throwable $e) {
				Log::error($method . ' failed', ['error' => $e->getMessage()]);
				return defaultUndefinedException($req, $e, $method);
			}
		}, ['title' => $req->input('title')]);
	}

	public function edit(Request $req, JobStage $jobStage): RedirectResponse|View
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		return $this->measureProfile($action, function () use ($req, $jobStage, $action, $method) {
			if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
			if (($c = self::guard($req, self::PERM_EDIT, self::REDIRECT_INDEX)) !== true) return $c;
			$view = VW::JB_STG . '.' . $action;
			if (!ViewFacade::exists($view)) return defaultUndefinedException($req, new \RuntimeException('View not found'), $method);
			return ViewFacade::make($view, compact('jobStage'));
		}, ['stage_id' => $jobStage->id]);
	}

	public function update(Request $req, JobStage $jobStage): RedirectResponse
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		return $this->measureProfile($action, function () use ($req, $jobStage, $method) {
			if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
			if (($c = self::guard($req, self::PERM_EDIT, self::REDIRECT_INDEX)) !== true) return $c;
			$v = Validator::make($req->all(), ['title' => 'required']);
			if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
			try {
				$jobStage->update(['title' => $req->input('title'), DC::COL_TABLE_CREATOR => $u->creatorId()]);
				Log::debug($method . ' updated', ['stage_id' => $jobStage->id]);
				return redirect()->back()->with('success', __('Job stage successfully updated.'));
			} catch (\Throwable $e) {
				Log::error($method . ' failed', ['error' => $e->getMessage()]);
				return defaultUndefinedException($req, $e, $method);
			}
		}, ['stage_id' => $jobStage->id, 'title' => $req->input('title')]);
	}

	public function destroy(Request $req, JobStage $jobStage): RedirectResponse
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		return $this->measureProfile($action, function () use ($req, $jobStage, $method) {
			if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
			if (($c = self::guard($req, self::PERM_DELETE, self::REDIRECT_INDEX)) !== true) return $c;
			if ($jobStage->created_by !== $u->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $method);
			try {
				$jobStage->delete();
				Log::debug($method . ' deleted', ['stage_id' => $jobStage->id]);
				return redirect()->back()->with('success', __('Job stage successfully deleted.'));
			} catch (\Throwable $e) {
				Log::error($method . ' failed', ['error' => $e->getMessage()]);
				return defaultUndefinedException($req, $e, $method);
			}
		}, ['stage_id' => $jobStage->id]);
	}

	public const ORD = 'order';
	public function order(Request $req): void
	{
		$action = __FUNCTION__;
		$method = __METHOD__;
		$this->measureProfile($action, function () use ($req, $method) {
			if ((self::_checkLogin()) instanceof RedirectResponse) return null;
			if ((self::guard($req, self::PERM_EDIT, self::REDIRECT_INDEX)) !== true) return null;
			$orderList = $req->input('order', []);
			foreach ($orderList as $position => $id) {
				try {
					JobStage::whereKey($id)->update(['order' => $position]);
					Log::debug($method . ' reordered', ['id' => $id, 'position' => $position]);
				} catch (\Throwable $e) {
					Log::error($method . ' failed for id', ['id' => $id, 'error' => $e->getMessage()]);
				}
			}
			return null;
		}, ['order' => $req->input('order', [])]);
	}
}
