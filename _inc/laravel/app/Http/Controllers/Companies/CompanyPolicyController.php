<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    ViewsConstants,
};
use App\Models\{Branch, CompanyPolicy, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    File,
    Log,
    Validator
};

final class CompanyPolicyController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = '/';

    public function index(Request $r)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'manage company policy', self::REDIRECT_INDEX)) return $c;
        try {
            $companyPolicy = CompanyPolicy::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                ->with('branches')
                ->get();
            return view(ViewsConstants::CPN_PL . '.' . __FUNCTION__, compact('companyPolicy'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(Request $r)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'create company policy', self::REDIRECT_INDEX)) return $c;
        $branch = self::branches($u->creatorId());
        return view(ViewsConstants::CPN_PL . '.' . __FUNCTION__, compact('branch'));
    }

    public function store(Request $r): RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'create company policy', self::REDIRECT_INDEX))               return $c;
        if ($c = self::v($r, ['branch' => 'required', 'title' => 'required'])) return $c;
        try {
            $file = $r->hasFile('attachment') ? self::upload($r, 'attachment') : null;
            $policy = CompanyPolicy::create([
                'branch'      => $r->branch,
                'title'       => $r->title,
                'description' => $r->description,
                'attachment'  => $file,
                DatabaseConstants::TABLE_CREATOR  => $u->creatorId()
            ]);
            /* ---------- async notifications ---------- */
            try {
                $settings = Utility::settings($u->creatorId());
                $branch  = Branch::find($r->branch);
                $payload = [
                    'company_policy_name' => $policy->title,
                    'branch_name' => $branch?->name ?? ''
                ];
                ($settings['policy_notification']   ?? false)
                    && Utility::sendSlackMsg('new_company_policy', $payload);
                ($settings['telegram_policy_notification'] ?? false)
                    && Utility::sendTelegramMsg('new_company_policy', $payload);
            } catch (\Throwable $e) {
                Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' notify ' . $e->getMessage()); // ! ALERT soft‑fail
            }
            /* ---------- webhook ---------- */
            try {
                if ($hook = Utility::webhookSetting('New Company Policy')) {
                    $ok = Utility::webhookCall($hook['url'], $policy->toJson(), $hook['method']);
                    $ok || Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' webhook failed');
                }
            } catch (\Throwable $e) {
                Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' webhook ' . $e->getMessage());
            }
            return redirect()->route(ViewsConstants::CPN_PL . '.index')
                ->with('success', __('Company policy successfully created.'));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function edit(Request $r, CompanyPolicy $companyPolicy)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'edit company policy', self::REDIRECT_INDEX)) return $c;
        $branch = self::branches($u->creatorId());
        return view(ViewsConstants::CPN_PL . '.' . __FUNCTION__, compact('branch', 'companyPolicy'));
    }

    public function update(
        Request $r,
        CompanyPolicy $companyPolicy
    ): RedirectResponse|JsonResponse {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'create company policy', self::REDIRECT_INDEX)) return $c;
        if ($c = self::v($r, ['branch' => 'required', 'title' => 'required'])) return $c;
        try {
            $data = [
                'branch' => $r->branch,
                'title' => $r->title,
                'description' => $r->description
            ];
            if ($r->hasFile('attachment'))
                $data['attachment'] = self::upload($r, 'attachment');
            $companyPolicy->update($data);
            return redirect()->route(ViewsConstants::CPN_PL . '.index')
                ->with('success', __('Company policy successfully updated.'));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(
        Request $r,
        CompanyPolicy $companyPolicy
    ): RedirectResponse {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'delete document', self::REDIRECT_INDEX)) return $c;
        if ($companyPolicy->created_by !== $u->creatorId())
            return redirect()->back()->with('error', __('Permission denied.'));
        try {
            if ($companyPolicy->attachment) {
                $file = 'uploads/companyPolicy/' . $companyPolicy->attachment;
                File::exists($file) && File::delete($file);
            }
            $companyPolicy->delete();
            return redirect()->route(ViewsConstants::CPN_PL . '.index')
                ->with('success', __('Company policy successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(): RedirectResponse
    {
        return redirect()->route(ViewsConstants::CPN_PL . '.index');
    }

    private static function v(Request $r, array $rules): ?RedirectResponse
    {
        $v = Validator::make($r->all(), $rules);
        return $v->fails()
            ? redirect()->back()->with('error', $v->getMessageBag()->first())
            : null;
    }

    private static function branches(int $creator): array
    {
        try {
            return Branch::where(DatabaseConstants::TABLE_CREATOR, $creator)
                ->pluck(CompaniesConstants::COL_BRC_NM, 'id')
                ->prepend(__('Select Branch'), '')
                ->all();
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' ' . $e->getMessage());
            return [];
        }
    }

    private static function upload(
        Request $r,
        string  $field,
        string  $dir = 'uploads/companyPolicy/'
    ): string {
        $file     = $r->file($field);
        $nameParts = pathinfo($file->getClientOriginalName());
        $name     = "{$nameParts['filename']}_" . time() . '.' . $nameParts['extension'];
        try {
            $path = Utility::uploadFile($r, $field, $name, $dir, []);
            return $path['flag'] ? $name : throw new \RuntimeException($path['msg']);
        } catch (\Throwable $e) {
            throw new \RuntimeException('upload', 0, $e); // ! ALERT bubbling msg
        }
    }
}
