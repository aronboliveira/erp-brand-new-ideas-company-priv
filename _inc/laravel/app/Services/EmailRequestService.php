<?php

namespace App\Services;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	EmailsConstants as EC,
	UsersConstants as UC
};
use App\Enums\{EmailTemplateType};
use App\Models\{EmailTemplate, User, UserEmailTemplate, Utility};
use Illuminate\Database\Eloquent\{Builder, Collection, Relations\HasOne};
use Illuminate\Support\Facades\Log;

class EmailRequestService
{
	public function saveTemplate(EmailTemplate $template, array $attrs, ?User $actor = null): EmailTemplate
	{
		$actor = $this->resolveUser($actor);

		$template->fill($attrs);

		// Set audit fields outside the model
		if (!$template->exists && $actor && !$template->getAttribute(DC::COL_TABLE_CREATOR)) {
			$template->setAttribute(DC::COL_TABLE_CREATOR, $actor->id);
		}

		if ($actor) {
			$template->setAttribute(DC::COL_TABLE_UPDATER, $actor->id);
		}

		$template->save();

		return $template;
	}

	/**
	 * Get user-specific email template relationship
	 * Uses authenticated user if no user provided
	 */
	public function getUserTemplate(EmailTemplate $template, ?User $user = null): ?HasOne
	{
		try {
			$user = $user ?? auth()->user();
			if (!$user)
				return null;
			$userTemplate = UserEmailTemplate::where(EC::COL_TMP, $template->id)
				->where(UC::COL_USER_ID, $user->id)
				->first();
			if (!$userTemplate)
				return $template->hasOne(UserEmailTemplate::class, EC::COL_TMP, 'id')
					->where(UC::COL_USER_ID, '=', auth()->id());
			return $template->hasOne(UserEmailTemplate::class, EC::COL_TMP, 'id')
				->where('id', '=', $userTemplate->id);
		} catch (\Throwable $e) {
			Log::error(static::class . '::getUserTemplate failed', [
				'template_id' => $template->id,
				'user_id' => $user?->id,
				'error' => $e->getMessage(),
			]);
			return null;
		}
	}

	public function getDefaultTemplate(?User $user = null): ?EmailTemplate
	{
		$user = $user ?? auth()->user();
		if (!$user) return null;
		/** @var EmailTemplate|null */
		return $this->getAvailableTemplates($user)
			->sortBy(DC::COL_C_AT)
			->first();
	}

	public function getDefaultForType(EmailTemplateType|string|null $type, ?User $user = null): ?EmailTemplate
	{
		$user = $user ?? auth()->user();
		if (!$user) return null;
		/** @var EmailTemplate|null */
		return $this->getTemplatesByType($type, $user)
			->sortBy(DC::COL_C_AT)
			->first();
	}

	/**
	 * Get available templates for user based on their plan and permissions
	 * Uses authenticated user if no user provided
	 */
	public function getAvailableTemplates(?User $user = null): Collection
	{
		try {
			$user = $user ?? auth()->user();

			if (!$user)
				return new Collection();

			$query = EmailTemplate::query()->available();

			$userPlan = $user->plan ?? null;

			if ($userPlan) {
				$query->where(function ($q) use ($userPlan) {
					$q->whereNull('excluded_plans')
						->orWhereJsonDoesntContain('excluded_plans', $userPlan);
				});
			}

			$isEmployee = Utility::isEmployee($user);

			if (!$isEmployee) {
				$query->where(function ($q) {
					$q->whereNull('categories')
						->orWhereJsonLength('categories', 0);
				});
			}

			return $query->orderBy(DC::COL_C_AT)->get();
		} catch (\Throwable $e) {
			Log::error(static::class . '::getAvailableTemplates failed', [
				'user_id' => $user?->id,
				'error' => $e->getMessage(),
			]);
			return new Collection();
		}
	}

	/**
	 * Get templates by type for authenticated user
	 */
	public function getTemplatesByType(
		EmailTemplateType|string|null $type,
		?User $user = null
	): Collection {
		try {
			$user = $user ?? auth()->user();

			if (!$user)
				return new Collection();

			$enum = $type instanceof EmailTemplateType
				? $type
				: EmailTemplateType::normalize($type);

			$query = EmailTemplate::query()
				->available()
				->ofType($enum);

			$userPlan = $user->plan ?? null;

			if ($userPlan) {
				$query->where(function ($q) use ($userPlan) {
					$q->whereNull('excluded_plans')
						->orWhereJsonDoesntContain('excluded_plans', $userPlan);
				});
			}

			return $query->orderBy(DC::COL_C_AT)->get();
		} catch (\Throwable $e) {
			Log::error(static::class . '::getTemplatesByType failed', [
				'type' => $type instanceof EmailTemplateType ? $type->value : $type,
				'user_id' => $user?->id,
				'error' => $e->getMessage(),
			]);
			return new Collection();
		}
	}

	/**
	 * Check if user can access a specific template
	 */
	public function userCanAccessTemplate(
		EmailTemplate $template,
		?User $user = null
	): bool {
		try {
			$user = $user ?? auth()->user();

			if (!$user)
				return false;

			$disabled = (bool) $template->getAttribute(AC::COL_DSB);
			if ($disabled)
				return false;

			$availableFrom = $template->getAttribute(AC::COL_AV_FROM);
			if ($availableFrom && $availableFrom > now())
				return false;

			$isEmployee = Utility::isEmployee($user);

			if ($isEmployee)
				return true;

			$excludedPlans = $template->getAttribute('excluded_plans') ?? [];
			$userPlan = $user->plan ?? null;

			if ($userPlan && in_array($userPlan, $excludedPlans, true))
				return false;

			$categories = $template->getAttribute('categories') ?? [];
			if (!empty($categories))
				return false;

			return true;
		} catch (\Throwable $e) {
			Log::error(static::class . '::userCanAccessTemplate failed', [
				'template_id' => $template->id,
				'user_id' => $user?->id,
				'error' => $e->getMessage(),
			]);
			return false;
		}
	}

	/**
	 * Check if user can edit a template
	 */
	public function userCanEditTemplate(
		EmailTemplate $template,
		?User $user = null
	): bool {
		try {
			$user = $user ?? auth()->user();

			if (!$user)
				return false;

			$isEmployee = Utility::isEmployee($user);

			if ($isEmployee)
				return true;

			return $template->getAttribute(DC::COL_TABLE_CREATOR) === $user->id;
		} catch (\Throwable $e) {
			Log::error(static::class . '::userCanEditTemplate failed', [
				'template_id' => $template->id,
				'user_id' => $user?->id,
				'error' => $e->getMessage(),
			]);
			return false;
		}
	}

	/**
	 * Check if user can delete a template
	 */
	public function userCanDeleteTemplate(
		EmailTemplate $template,
		?User $user = null
	): bool {
		try {
			$user = $user ?? auth()->user();

			if (!$user)
				return false;

			$isEmployee = Utility::isEmployee($user);

			if ($isEmployee)
				return true;

			return $template->getAttribute(DC::COL_TABLE_CREATOR) === $user->id;
		} catch (\Throwable $e) {
			Log::error(static::class . '::userCanDeleteTemplate failed', [
				'template_id' => $template->id,
				'user_id' => $user?->id,
				'error' => $e->getMessage(),
			]);
			return false;
		}
	}

	/**
	 * Get transactional templates available for user
	 */
	public function getTransactionalTemplates(?User $user = null): Collection
	{
		try {
			$user = $user ?? auth()->user();

			if (!$user)
				return new Collection();

			$query = EmailTemplate::query()
				->available()
				->transactional();

			$userPlan = $user->plan ?? null;

			if ($userPlan) {
				$query->where(function ($q) use ($userPlan) {
					$q->whereNull('excluded_plans')
						->orWhereJsonDoesntContain('excluded_plans', $userPlan);
				});
			}

			return $query->orderBy(DC::COL_C_AT)->get();
		} catch (\Throwable $e) {
			Log::error(static::class . '::getTransactionalTemplates failed', [
				'user_id' => $user?->id,
				'error' => $e->getMessage(),
			]);
			return new Collection();
		}
	}

	/**
	 * Get marketing templates available for user
	 */
	public function getMarketingTemplates(?User $user = null): Collection
	{
		try {
			$user = $user ?? auth()->user();

			if (!$user)
				return new Collection();

			$query = EmailTemplate::query()
				->available()
				->marketing();

			$userPlan = $user->plan ?? null;

			if ($userPlan) {
				$query->where(function ($q) use ($userPlan) {
					$q->whereNull('excluded_plans')
						->orWhereJsonDoesntContain('excluded_plans', $userPlan);
				});
			}

			return $query->orderBy(DC::COL_C_AT)->get();
		} catch (\Throwable $e) {
			Log::error(static::class . '::getMarketingTemplates failed', [
				'user_id' => $user?->id,
				'error' => $e->getMessage(),
			]);
			return new Collection();
		}
	}

	/**
	 * Get automated templates available for user
	 */
	public function getAutomatedTemplates(?User $user = null): Collection
	{
		try {
			$user = $user ?? auth()->user();

			if (!$user)
				return new Collection();

			$query = EmailTemplate::query()
				->available()
				->automated();

			$userPlan = $user->plan ?? null;

			if ($userPlan) {
				$query->where(function ($q) use ($userPlan) {
					$q->whereNull('excluded_plans')
						->orWhereJsonDoesntContain('excluded_plans', $userPlan);
				});
			}

			return $query->orderBy(DC::COL_C_AT)->get();
		} catch (\Throwable $e) {
			Log::error(static::class . '::getAutomatedTemplates failed', [
				'user_id' => $user?->id,
				'error' => $e->getMessage(),
			]);
			return new Collection();
		}
	}

	/**
	 * Get templates for a specific platform
	 */
	public function getTemplatesForPlatform(
		string $platform,
		?User $user = null
	): Collection {
		try {
			$user = $user ?? auth()->user();

			if (!$user)
				return new Collection();

			$query = EmailTemplate::query()
				->available()
				->forPlatform($platform);

			$userPlan = $user->plan ?? null;

			if ($userPlan) {
				$query->where(function ($q) use ($userPlan) {
					$q->whereNull('excluded_plans')
						->orWhereJsonDoesntContain('excluded_plans', $userPlan);
				});
			}

			return $query->orderBy(DC::COL_C_AT)->get();
		} catch (\Throwable $e) {
			Log::error(static::class . '::getTemplatesForPlatform failed', [
				'platform' => $platform,
				'user_id' => $user?->id,
				'error' => $e->getMessage(),
			]);
			return new Collection();
		}
	}

	/**
	 * Create or update user-specific template customization
	 */
	public function updateUserTemplate(
		EmailTemplate $template,
		array $customizations,
		?User $user = null
	): ?UserEmailTemplate {
		try {
			$user = $user ?? auth()->user();

			if (!$user)
				return null;

			if (!$this->userCanAccessTemplate($template, $user))
				return null;

			return UserEmailTemplate::updateOrCreate(
				[
					EC::COL_TMP => $template->id,
					UC::COL_USER_ID => $user->id,
				],
				$customizations
			);
		} catch (\Throwable $e) {
			Log::error(static::class . '::updateUserTemplate failed', [
				'template_id' => $template->id,
				'user_id' => $user?->id,
				'error' => $e->getMessage(),
			]);
			return null;
		}
	}

	public function withUserTemplate(Builder $query, ?User $user = null): Builder
	{
		$user = $this->resolveUser($user);
		if (!$user)
			return $query->with('userTemplates');
		return $query->with([
			'userTemplates' => fn($q) => $q->where(UC::COL_USER_ID, $user->id),
		]);
	}

	public function userTemplateRelation(EmailTemplate $template, ?User $user = null): HasOne
	{
		$user = $user ?? auth()->user();
		$rel = $template->hasOne(UserEmailTemplate::class, EC::COL_TMP, 'id');
		if (!$user)
			return $rel->whereRaw('1 = 0');
		return $rel->where(UC::COL_USER_ID, $user->id);
	}

	private function resolveUser(?User $user = null): ?User
	{
		return $user ?? auth()->user();
	}
}
