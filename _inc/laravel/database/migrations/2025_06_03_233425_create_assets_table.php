<?php

use App\Config\Constants\{BillsConstants as BC, CompaniesConstants as CC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\AssetType;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateAssetsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_AST;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('serial')->nullable()->unique(); // ? nullable for tests
            $table->uuid('category')->nullable(); // ? nullable for tests
            $table->enum('type', array_column(AssetType::cases(), 'value'))->default(AssetType::Other->value)->nullable(); // ? nullable for tests
            $table->string('name');
            $table->uuid(UC::COL_EMP_ID)->nullable(); // ? nullable for tests
            $table->date(CC::COL_PRC_DT);
            $table->date(CC::COL_SPT_DT);
            $table->unsignedDecimal('amount', 15, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->text('purpose')->nullable(); // ? nullable for tests
            $table->uuid('order')->nullable(); // ? nullable for tests
            $table->uuid('transaction')->nullable(); // ? nullable for tests
            $table->uuid(BC::COL_SIGN_BY)->nullable(); // * in production, if the linked COL_EMP_ID does not link to a COL_USER_ID that represent an user of the type admin, a super admin or a company, then this field MUST be present (checked in the model), else a permission error is raised
            $table->string(BC::COL_SIGN_BY_NAME)->nullable(); // * in production, if the linked COL_EMP_ID does not link to a COL_USER_ID that represent an user of the type admin, a super admin or a company, then this field MUST be present (checked in the model), else a permission error is raised
            $table->json('attachments')->nullable();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            foreach (
                [
                    UC::COL_EMP_ID => DC::TABLE_EMPLOYEES,
                    BC::COL_SIGN_BY => DC::TABLE_EMPLOYEES,
                    'order'        => DC::TABLE_ORDERS,
                    'transaction'  => DC::TABLE_TRS,
                    'category'     => DC::TABLE_PROD_SERV_CATS
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    UC::COL_EMP_ID,
                    BC::COL_SIGN_BY,
                    'order',
                    'transaction',
                    'category',
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
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
