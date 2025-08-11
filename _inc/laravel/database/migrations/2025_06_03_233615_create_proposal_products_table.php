<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProposalProductsTable extends Migration
{
    private const TABLE = 'proposal_products';
    private const COL_PROPOSAL = 'proposal_id';
    private const COL_PRODUCT = 'product_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                 // ! CHANGED
            $table->uuid(self::COL_PROPOSAL)->index();           // ! CHANGED
            $table->uuid(self::COL_PRODUCT)->index();            // ! CHANGED
            $table->integer('quantity');
            $table->string('tax', 50)->nullable();
            $table->float('discount')->default(0.00);
            $table->decimal('price', 16, 2)->default(0.00);   // ! CHANGED field name/type
            $table->text('description')->nullable();        // ! CHANGED added
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
            foreach ([
                self::COL_PROPOSAL               => DatabaseConstants::TABLE_PROPOSALS,
                self::COL_PRODUCT                => DatabaseConstants::TABLE_PRODUCTS,
                DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
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
                self::COL_PROPOSAL,
                self::COL_PRODUCT,
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
