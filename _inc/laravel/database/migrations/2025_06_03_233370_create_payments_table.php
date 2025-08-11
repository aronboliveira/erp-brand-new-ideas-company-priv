<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePaymentsTable extends Migration
{
    private const ENTITY = 'payment';
    private const TABLE = self::ENTITY . 's';
    private const ACC = 'account_id';
    private const COL_ACC = 'chart_' . self::ACC;
    private const COL_VENDOR = 'vendor_id';
    private const COL_CAT = 'category_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->date('date');
            $table->decimal('amount', 16, 2)->default('0.0');
            $table->uuid(self::ACC); // ! CHANGED
            $table->uuid(self::COL_ACC)
                ->default(DatabaseConstants::DEFAULT_UUID); // ! CHANGED
            $table->uuid(self::COL_VENDOR); // ! CHANGED
            $table->text('description')->nullable();
            $table->uuid(self::COL_CAT); // ! CHANGED
            $table->string('recurring')->nullable();
            $table->integer(self::ENTITY . '_method');
            $table->string('reference')->nullable();
            $table->string('add_receipt')->nullable();
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)
                ->default(DatabaseConstants::DEFAULT_UUID); // ! CHANGED
            foreach ([
                self::ACC                             => DatabaseConstants::TABLE_BANK_ACC,
                self::COL_ACC                  => DatabaseConstants::TABLE_COAS,
                self::COL_VENDOR                           => DatabaseConstants::TABLE_VENDORS,
                self::COL_CAT                         => DatabaseConstants::TABLE_PROD_SERV_CATS,
                DatabaseConstants::TABLE_CREATOR      => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::ACC,
                self::COL_ACC,
                self::COL_VENDOR,
                self::COL_CAT,
                DatabaseConstants::TABLE_CREATOR,
            ] as $column) {
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
