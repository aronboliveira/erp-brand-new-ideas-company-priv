<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePasswordResetsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE_NAME = DC::TABLE_PW_RST;

    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->string('token')->unique()->primary();
            $table->string('email', 254)->index();
            $table->timestamp(PJC::COL_SBM_AT)->nullable(); // ? submitted_at, can NEVER be lower than user.created_at, if valid (queried through email, automatically set as the last updated_at (fallback to created_at) of this table, if they are not null; else use now()) // ? a user cannot request more than 2 password resets in a rolling 1 hour window if not a UserType::SuperAdmin->value // ? a user cannot request more than 5 password resets in a rolling 24 hour window if not a UserType::SuperAdmin->value
            $table->timestamp(DC::COL_EXP_DT)->nullable()->index(); // ? expiration_date, can NEVER be lower than user.created_at + 15 min, if valid (queried through email, automatically set as the last updated_at + 15 min (fallback to created_at) of this table, if they are not null; else use now() + 15 min)
            $table->string('source')->nullable(); // ? source of the request, e.g., 'web', 'mobile', 'automatic', etc.
            $table->unsignedTinyInteger('attempts')->default(0); // ? number of attempts made to use this token. Max attempts should be 16, else break the creation and require a new token if the user is not of UserType::SuperAdmin->value
            $table->ipAddress('ip')->nullable(); // ? IP address from which the reset was requested // ? nullable for tests
            $table->foreign('email')
                ->references(UC::COL_EM)
                ->on(DC::TABLE_USERS)
                ->cascadeOnDelete();
            $table->index(['email', PJC::COL_SBM_AT]);
            $table->unique(['email', PJC::COL_SBM_AT, 'source'], 'pw_rst_email_sbm_source_unique');
            $table->unique(['email', PJC::COL_SBM_AT, 'attempts'], 'pw_rst_email_sbm_attempts_unique');
            $table->unique(['ip', PJC::COL_SBM_AT, 'source'], 'pw_rst_ip_sbm_unique');
            $table->unique(['ip', PJC::COL_SBM_AT, 'attempts'], 'pw_rst_ip_sbm_attempts_unique');
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            try {
                $this->dropAuditColumnForeigns($table, self::TABLE_NAME);
                foreach (['email'] as $column)
                    Schema::hasColumn(self::TABLE_NAME, $column) &&
                        Schema::table(self::TABLE_NAME, function (Blueprint $table) use ($column): void {
                            $table->dropForeign([$column]);
                        });
            } catch (\Exception $e) {
                Log::debug(
                    'Failed to drop foreign keys on table '
                        . self::TABLE_NAME
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
