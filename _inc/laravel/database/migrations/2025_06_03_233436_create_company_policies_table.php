<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, MessagesConstants as MC, SettingsConstants as SC};
use App\Enums\{AvailableLang, EvaluationStatus};
use App\Traits\{BranchConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{DB, Log, Schema};

class CreateCompanyPoliciesTable extends Migration
{
    use BranchConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_CPN_POL; // company_policies
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code')->nullable()->unique(); // ? nullable for now to avoid issues with existing data, ensure uniqueness at model level with do Str::uuid()->toString() while DB::table(DC::TABLE_CPN_POL)->where('code', $code)->exists()
            $table->string('title', 254)->nullable()->index();
            $table->string('url', 254)->nullable()->unique();
            $table->string('email', 254)->nullable()->index(); // ? contact email for policy-related communications
            $table->string('phone', 32)->nullable()->index(); // ? contact phone number for policy-related communications
            $table->string('summary', 1024)->nullable();
            $table->unsignedDecimal('version', 5, 2)->default(1.0);
            $table->uuid('company')->nullable()->unique(); // ? nullable for tests // ? should be check for $user->type === 'company' in model, else nullified and, FOR TESTS ONLY, a random user of type company should be assigned (adjusting the unique as well) as long as it doesn't conflict with the unique constraint
            $this->addBranchColumns($table, unique: false, nullable: true, prefixed: false); // ? this was originally not nullable, but making it nullable to allow for company-wide policies
            $table->unique(['company', 'branch'], 'company_branch_unique');
            $table->text('description');
            $table->enum(SC::DEF_LNG, array_column(AvailableLang::cases(), 'value'))->default(DC::DEFAULT_LANG)->nullable(); // default_language // ? nullable to avoid issues with existing data, enforce default at boot/save
            $table->enum('status', array_column(EvaluationStatus::cases(), 'value'))->default(EvaluationStatus::Draft->value)->nullable(); // ? nullable to avoid issues with existing data, enforce default at boot/save
            $table->string('attachment', 254)->nullable(); // * legacy, but redundant with file, and, mainly, with the correct implementation considering json storage
            $table->string('file')->nullable(); // * not clear if needed, but keeping for now
            $table->json('attachments')->nullable(); // ? for now, the attachment and file strings should be pushed here at booted if not already present and not null
            $table->json(MC::COL_AV_LG)->nullable(); // ? available_languages, filtered at model level to ensure only valid languages are stored, according to AvailableLang enum // ? nullable to avoid issues with existing data
            $table->json('acknowledgers')->nullable(); // ? list of employee IDs who have acknowledged this policy, which must have the their instance as users (queried through UC::COL_USER_ID) with the types UserType::SuperAdmin, UserType::Company, or UserType::Admin or UserType::Vendor, ELSE filtered out at model level
            $table->json(DC::COL_LEGAL_REP)->nullable(); // ? legal_representants, array of objects with name, position, and id (optional) of the company's legal representants
            $table->json('urls')->nullable(); // ? list of URLs related to this policy, e.g., links to external resources, internal documents, etc.
            $table->string(BC::COL_SIGN_BY_NAME, 254)->nullable(); // ? name of the person who signed the policy on behalf of the company // ? nullable for tests;
            $table->uuid(BC::COL_SIGN_BY)->nullable()->index(); // ? user ID of the person who signed the policy on behalf of the company // ? not necessarily an user
            $table->dateTime(BC::COL_SIGN_AT)->nullable();
            foreach (['company', BC::COL_SIGN_BY] as $foreignColumn) {
                $table->foreign($foreignColumn)
                    ->references('id')
                    ->on(DC::TABLE_USERS)
                    ->nullOnDelete();
            }
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropBranchColumnForeign($table, self::TABLE, prefixed: false);
            try {
                foreach (['company', BC::COL_SIGN_BY] as $foreignColumn) {
                    Schema::hasColumn(self::TABLE, $foreignColumn) &&
                        $table->dropForeign([$foreignColumn]);
                }
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . 'company'
                        . ' on table '
                        . self::TABLE
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
