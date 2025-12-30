<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{BranchConnected, EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTrainersTable extends Migration
{
    use BranchConnected, EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_TRAINERS;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid(UC::COL_USER_ID)->unique()->nullable(); // ? nullable to allow for external trainers not in the system
                $this->addBranchColumns($table, unique: false, nullable: false, prefixed: false);
                $this->addEmployeeColumns($table, unique: false, nullable: true); // ? nullable to allow for external trainers not in the system
                $table->string('firstname'); // * this should be dynamically set from the linked user/employee if present
                $table->string('lastname'); // * same
                $table->string('contact'); // * same
                $table->string('email', 254); // * same
                $table->text('address')->nullable(); // * same
                $table->text('presentation')->nullable();
                $table->text('expertise')->nullable();
                $table->uuid('registration')->nullable()->index(); // ? registration document // ? nullable for tests
                $table->json('qualifications')->nullable(); // ? list of qualification strings or objects, validated at model level
                $table->json('certificates')->nullable(); // ? list of certificate ids or names that the trainer holds, validated at model level by existing in DC::TABLE_DOCS
                $table->foreign(UC::COL_USER_ID)
                    ->references('id')
                    ->on(DC::TABLE_USERS)
                    ->nullOnDelete();
                $table->foreign('registration')
                    ->references('id')
                    ->on(DC::TABLE_DOCS)
                    ->nullOnDelete();
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropEmployeeColumnForeign($table, self::TABLE);
            $this->dropBranchColumnForeign($table, self::TABLE, prefixed: false);
            foreach ([UC::COL_USER_ID, 'registration'] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::error("Error dropping foreign for " . $col . " in " . self::TABLE . ": " . $e->getMessage());
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
