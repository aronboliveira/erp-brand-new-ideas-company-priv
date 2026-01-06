<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, EmailsConstants as EC, FormsConstants as FC, ProjectsConstants as PJC};
use App\Enums\{AppModuleType, Visibility};
use App\Traits\{DescribesClientForm, DescribesHtmlLinkedEntity, HasNullableAuditColumns, TracksFailures};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

// * this is meant to describe a form itself, not its fields or responses, as well as any behavior for processing its derived FormData or non-relational client behavior (like for UI Frameworks or Vanilla JS) or any metadata (security, timestamps, logs, etc.)
class CreateFormBuildersTable extends Migration
{
    use DescribesClientForm, DescribesHtmlLinkedEntity, HasNullableAuditColumns, TracksFailures;
    private const TABLE = DC::TABLE_FORM_BUILD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->nullable(); // * nullable for tests, unique code to identify the form in client-side code or backend processing, generated dynamically as FRM-{UUID}, checking uniqueness with do/while
            $table->string('shortcode')->unique()->nullable(); // ? shortcode to embed the form in other systems or platforms, generated dynamically as [form id="{UUID}"], checking uniqueness with do/while
            $table->enum('module', array_column(AppModuleType::cases(), 'value'))->default(AppModuleType::Other->value)->nullable()->index();
            $table->string('name')->index(); // ? this is NOT the "name" html attribute, but a human-readable name for the form
            $table->enum('visibility', array_column(Visibility::cases(), 'value'))->default(Visibility::Private->value)->index(); // ? should be dynamically adjusted according to the 'status', COL_ACPT_SBM, COL_DPL_URL, etc.
            $table->string(FC::COL_CAPTCHA_PRV)->nullable(); // ? captcha provider/service used for this form, e.g., reCAPTCHA, hCaptcha, etc. If null, no captcha is used
            $table->unsignedInteger(FC::COL_RT_LMT)->default(0)->nullable(); // ? rate limit for form submissions, in number of submissions per hour, 0 means no limit
            $table->unsignedSmallInteger(FC::COL_RTT_DAYS)->default(730)->nullable(); // ? number of days to retain form submissions, 0 means retain indefinitely
            $table->dateTime(FC::COL_EXP_AT)->nullable(); // ? expiration date after which the form is no longer active or accepting submissions, automatically setting COL_IA to false and 'status' to ::Expired->value when reached
            $table->boolean(AC::COL_IA)->default(false);
            $table->boolean(FC::COL_RQ_LOGIN)->default(true)->nullable();
            $table->boolean(FC::COL_LMT_ONE_PRSN)->default(false)->nullable(); // ? limit to one submission per person (by email or user id), enforced at model/controller level
            $table->boolean(FC::COL_ACPT_SBM)->default(false)->nullable(); // ? is the form currently accepting submissions, always nullified when DPL_URL is null/doesNotExist or is invalidated by security checks OR if COL_IA is false/null
            $table->boolean(FC::COL_CSRF_CHK_REQ)->default(true)->nullable(); // ? is CSRF check required for form submissions, enforced at model/controller level
            $table->boolean(FC::COL_ALW_EDT_AFT_SB)->default(true)->nullable(); // ? allow users to edit their submission after it has been submitted, enforced at model/controller level
            $table->boolean(FC::COL_CST_RQ)->default(false)->nullable(); // ? is consent required for form submission, e.g., for GDPR compliance
            $table->string(FC::COL_CST_DOC)->nullable(); // ? consent document, validated as a URL (https:// and a known domain pattern OR the env(APP_URL)) OR file path (direct path, attr://, file://, etc.) in local storage OR stored document id in DC::TABLE_DOCS
            $table->string(FC::COL_PRV_PL_DOC)->nullable(); // ? privacy policy document, validated as a URL (https:// and a known domain pattern OR the env(APP_URL)) OR file path (direct path, attr://, file://, etc.) in local storage OR stored document id in DC::TABLE_DOCS
            $table->boolean(AC::COL_IS_LD_ACT)->default(false)->nullable(); // ? column to cache the UC::COL_IA of the linked lead, even if the FK is nullified, as long as the id exists on the table. Dinamically updated on lead change events
            $table->boolean(AC::COL_IS_DL_ACT)->default(false)->nullable(); // ? column to cache the UC::COL_IA of the linked deal, even if the FK is nullified, as long as the id exists on the table. Dinamically updated on deal change events
            $table->boolean(AC::COL_IS_SUP_ACT)->default(false)->nullable(); // ? column to cache the UC::COL_IA of the linked support, even if the FK is nullified, as long as the id exists on the table. Dinamically updated on support change events
            $table->boolean(AC::COL_IS_PRJ_ACT)->default(false)->nullable(); // ? column to cache the UC::COL_IA of the linked project, even if the FK is nullified, as long as the id exists on the table. Dinamically updated on project change events
            $table->boolean(AC::COL_IS_CTC_ACT)->default(false)->nullable(); // ? column to cache the 'status' of the linked contact, as long as the id exists on the table. Dinamically updated on contact change events
            $table->uuid(PJC::COL_LD_ID)->nullable()->index();
            $table->uuid(PJC::COL_DL_ID)->nullable()->index();
            $table->uuid(PJC::COL_SUP_ID)->nullable()->index();
            $table->uuid(PJC::COL_PJ_ID)->nullable()->index();
            $table->uuid(PJC::COL_CTC_ID)->nullable()->index();
            $table->string('receiver')->nullable()->index(); // ? email or identifier of the entity that will receive the form submissions, e.g., an email address, a webhook URL, etc.
            $table->uuid(EC::COL_RCV_TMP)->nullable();
            $table->uuid(EC::COL_SBM_TMP)->nullable();
            $table->uuid(EC::COL_FLD_TMP)->nullable();
            $table->string(FC::COL_RDR_URL)->nullable(); // ? redirect_url, (validate with https:// and a known domain pattern OR the env(APP_URL))
            $table->string('generator')->nullable(); // ? identifier of the system or module that generated the form, like a plugin name or a specific module identifier
            $table->unsignedInteger(FC::COL_SBM_CNT)->default(0)->nullable(); // ? count of how many times this form has been submitted, enforced at model level to be integer
            $table->string(FC::COL_DPL_URL)->nullable(); // ? URL where the form is deployed or can be accessed by users (validate with https:// and a known domain pattern OR the env(APP_URL))
            $table->uuid(FC::COL_DPL_BY)->nullable()->index(); // ? user who deployed the form, nullified when DPL_URL is null/doesNotExist or is invalidated by security checks
            $table->json(FC::COL_AUTH_DPLS)->nullable(); // ? roles permitted to deploy the form, nullified when DPL_URL is null/doesNotExist or is invalidated by security checks, FILTERED at Model by the UserType enum // ? if null, any user can deploy
            $table->uuid(FC::COL_PUB_BY)->nullable()->index(); // ? user who published the form, nullified when the form is unpublished // ? if no deployer is set, the COL_DPL_URL should mirror the COL_PUB_BY, as well as DPL_AT and PUB_AT
            $table->timestamp(FC::COL_PUB_AT)->nullable(); // ? timestamp when the form was published, nullified when the form is unpublished
            $table->json(FC::COL_AUTH_PUBS)->nullable(); // ? roles authorized to publish the form, nullified when the form is unpublished, FILTERED at Model by the UserType enum // ? if null, any user can publish
            $table->timestamp(FC::COL_DPL_AT)->nullable(); // ? timestamp when the form was deployed, nullified when the form is unpublished OR when COL_DPL_BY is null/doesNotExist
            $table->string(FC::COL_SBM_URL)->nullable(); // ? URL where the form submission data can be accessed or viewed (validate with https:// and a known domain pattern OR the env(APP_URL))
            $this->addClientFormColumns($table);
            $this->addHtmlLinkedColumns($table, true);
            $table->json('variables')->nullable();
            $table->json('fields')->nullable(); // ? string[] of FormField id/codes that belong to this form, stored as a json array for easy retrieval
            $table->json('scripts')->nullable(); // ? urls (validate with https:// and a known domain pattern OR the env(APP_URL))
            $table->json('styles')->nullable();  // ? urls (validate with https:// and a known domain pattern OR the env(APP_URL))
            $table->json('webhooks')->nullable();
            $table->json('notifications')->nullable(); // ? json object defining ids for the DC::TABLE_NTF
            $table->json('cookies')->nullable();
            $table->json('exports')->nullable();
            $table->json(FC::COL_OTHER_URLS)->nullable(); // ? other related urls, e.g., buckets, lakes, third-party services, etc.
            $table->json(FC::COL_BLK_IP_RG)->nullable(); // ? list of blocked IP ranges in CIDR notation
            $table->json('settings')->nullable();
            foreach (
                [
                    PJC::COL_LD_ID => DC::TABLE_LEADS,
                    PJC::COL_DL_ID => DC::TABLE_DEALS,
                    PJC::COL_SUP_ID => DC::TABLE_SUPPORTS,
                    PJC::COL_PJ_ID => DC::TABLE_PROJECTS,
                    PJC::COL_CTC_ID => DC::TABLE_CONTRACTS,
                    EC::COL_RCV_TMP => DC::TABLE_EMAIL_TEMPLATES,
                    EC::COL_SBM_TMP => DC::TABLE_EMAIL_TEMPLATES,
                    EC::COL_FLD_TMP => DC::TABLE_EMAIL_TEMPLATES,
                    FC::COL_DPL_BY => DC::TABLE_USERS,
                    FC::COL_PUB_BY => DC::TABLE_USERS,
                ] as $col => $refTable
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($refTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
            $this->addFailureTrackingColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    PJC::COL_LD_ID,
                    PJC::COL_DL_ID,
                    PJC::COL_SUP_ID,
                    PJC::COL_PJ_ID,
                    PJC::COL_CTC_ID,
                    EC::COL_RCV_TMP,
                    EC::COL_SBM_TMP,
                    EC::COL_FLD_TMP,
                    FC::COL_DPL_BY,
                    FC::COL_PUB_BY,
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
                            . ' on table '
                            . self::TABLE
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
