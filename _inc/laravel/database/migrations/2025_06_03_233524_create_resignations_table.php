<?php

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC, UsersConstants as UC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateResignationsTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_RSG;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $this->addEmployeeColumns($table, unique: true);
            $table->date(UC::COL_RESIGNATION_NDT)->useCurrent();
            $table->date(UC::COL_RESIGNATION_DT)->default(now()->addDays(30)->format('Y-m-d'));
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->uuid(FC::COL_FM_ID)->nullable()->index(); // ? related exit interview form
            $table->foreign(FC::COL_FM_ID)
                ->references('id')
                ->on(DC::TABLE_FORM_BUILD)
                ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropEmployeeColumnForeign($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
