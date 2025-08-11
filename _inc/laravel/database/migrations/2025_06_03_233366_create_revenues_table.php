<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateRevenuesTable extends Migration
{
    private const TABLE = 'revenues';
    private const COL_ACC = 'account_id';
    private const COL_CUSTOMER = 'costumer_id';
    private const COL_CAT = 'category_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->date('date');
            $table->decimal('amount', 16, 2)->default('0.0');
            $table->uuid(self::COL_ACC); // ! CHANGED
            $table->uuid(self::COL_CUSTOMER); // ! CHANGED
            $table->uuid(self::COL_CAT); // ! CHANGED
            $table->integer('payment_method');
            $table->string('reference')->nullable();
            $table->string('add_receipt')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)
                ->default(DatabaseConstants::DEFAULT_UUID); // ! CHANGED
            foreach ([
                self::COL_ACC                       => DatabaseConstants::TABLE_BANK_ACC,
                self::COL_CUSTOMER                      => DatabaseConstants::TABLE_CUSTOMERS,
                self::COL_CAT                      => DatabaseConstants::TABLE_PROD_SERV_CATS,
                DatabaseConstants::TABLE_CREATOR   => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_ACC,
                self::COL_CUSTOMER,
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
