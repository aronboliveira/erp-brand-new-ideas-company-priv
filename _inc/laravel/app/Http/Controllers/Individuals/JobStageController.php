<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
	DatabaseConstants,
	UsersConstants,
	ViewsConstants
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
	Validator
};

class JobStageController extends Controller
{

	use ChecksLogin, ChecksPermissions;
	private const REDIRECT_INDEX = '/';
	private const PERM_MANAGE = 'manage job stage';
	private const PERM_CREATE = 'create job stage';
	private const PERM_EDIT  = 'edit job stage';
	private const PERM_DELETE = 'delete job stage';

	public function index(Request $req)
	{
		if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
		if ($c = self::guard($req, self::PERM_MANAGE, self::REDIRECT_INDEX)) return $c;
		try {
			$stages = JobStage::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
				->orderBy('order', 'asc')->get();
			return view(ViewsConstants::JB_STG . '.' . __FUNCTION__, compact('stages'));
		} catch (\Throwable $e) {
			Log::error('JobStageController::index failed: ' . $e->getMessage());
			return defaultUndefinedException(
				$req,
				$e,
				__CLASS__ . '::' . __FUNCTION__
			);
		}
	}

	public function create(Request $req)
	{
		if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
		if ($c = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) return $c;
		return view(ViewsConstants::JB_STG . '.' . __FUNCTION__);
	}

	public function show(Request $req, JobStage $jobStage): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	{
		Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
			UsersConstants::COL_USER_ID => $req->user()->id,
			'stage_id' => $jobStage->id
		]);
		try {
			if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
			if ($c = self::guard($req, self::PERM_MANAGE, self::REDIRECT_INDEX)) return $c;
			if ($jobStage->created_by !== $u->creatorId()) return defaultPermissionDenial(
				$req,
				new AuthorizationException(),
				__CLASS__ . '::' . __FUNCTION__
			);
			return view(ViewsConstants::JB_STG . '.' . __FUNCTION__, compact('jobStage'));
		} catch (\Throwable $e) {
			Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
			return defaultUndefinedException(
				$req,
				$e,
				__CLASS__ . '::' . __FUNCTION__
			);
		}
	}

	public function store(Request $req): RedirectResponse
	{
		if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
		if ($c = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) return $c;
		$v = Validator::make($req->all(), ['title' => 'required']);
		if ($v->fails()) return redirect()->back()
			->with('error', $v->errors()->first());
		try {
			JobStage::create([
				'title'      => $req->input('title'),
				DatabaseConstants::TABLE_CREATOR => $u->creatorId()
			]);
			return redirect()->back()
				->with('success', __('Job stage successfully created.'));
		} catch (\Throwable $e) {
			Log::error('JobStageController::store failed: ' . $e->getMessage());
			return defaultUndefinedException(
				$req,
				$e,
				__CLASS__ . '::' . __FUNCTION__
			);
		}
	}

	public function edit(Request $req, JobStage $jobStage)
	{
		if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
		if ($c = self::guard($req, self::PERM_EDIT, self::REDIRECT_INDEX)) return $c;
		return view(ViewsConstants::JB_STG . '.' . __FUNCTION__, compact('jobStage'));
	}

	public function update(Request $req, JobStage $jobStage): RedirectResponse
	{
		if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
		if ($c = self::guard($req, self::PERM_EDIT, self::REDIRECT_INDEX)) return $c;
		$v = Validator::make($req->all(), ['title' => 'required']);
		if ($v->fails()) return redirect()->back()
			->with('error', $v->errors()->first());
		try {
			$jobStage->update([
				'title'      => $req->input('title'),
				DatabaseConstants::TABLE_CREATOR => $u->creatorId()
			]);
			return redirect()->back()
				->with('success', __('Job stage successfully updated.'));
		} catch (\Throwable $e) {
			Log::error('JobStageController::update failed: ' . $e->getMessage());
			return defaultUndefinedException(
				$req,
				$e,
				__CLASS__ . '::' . __FUNCTION__
			);
		}
	}

	public function destroy(Request $req, JobStage $jobStage): RedirectResponse
	{
		if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
		if ($c = self::guard($req, self::PERM_DELETE, self::REDIRECT_INDEX)) return $c;
		if ($jobStage->created_by !== $u->creatorId())
			return defaultPermissionDenial(
				$req,
				new AuthorizationException(),
				__CLASS__ . '::' . __FUNCTION__
			);
		try {
			$jobStage->delete();
			return redirect()->back()
				->with('success', __('Job stage successfully deleted.'));
		} catch (\Throwable $e) {
			Log::error('JobStageController::destroy failed: ' . $e->getMessage());
			return defaultUndefinedException(
				$req,
				$e,
				__CLASS__ . '::' . __FUNCTION__
			);
		}
	}

	/**
	 * Reorder stages.
	 */
	public function order(Request $req): void
	{
		if ((self::_checkLogin()) instanceof RedirectResponse) return;
		if (self::guard($req, self::PERM_EDIT, self::REDIRECT_INDEX)) return;
		$orderList = $req->input('order', []);
		foreach ($orderList as $position => $id) {
			try {
				JobStage::where('id', $id)
					->update(['order' => $position]);
			} catch (\Throwable $e) {
				Log::error("JobStageController::order failed for id $id: " . $e->getMessage());
			}
		}
	}
}
