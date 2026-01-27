<?php

use App\Config\Constants\{BillsConstants as BC, BanksConstants as BKC, DatabaseConstants as DC};
use App\Enums\BillReferenceType;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBillAccountsTable extends Migration
{
	use HasNullableAuditColumns;
	private const TABLE = DC::TABLE_BL_ACC;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->uuid(BKC::COL_COA)->index(); // ? chart_account_id, as in chart of account id
			$table->uuid(BC::COL_REF_ID)->index();
			$table->enum('type', array_column(BillReferenceType::cases(), 'value'))->default(BillReferenceType::Bill->value);
			$table->decimal('price', 16, 2)->default(0.00); // ? this should be overwritten from the 'amount' field in the connect row of DC::TABLE_BILLS if the later is not empty
			$table->text('description')->nullable();
			$table->text('notes')->nullable();
			$table->json('attachments')->nullable();
			$table->json('metadata')->nullable();
			foreach (
				[
					BKC::COL_COA => DC::TABLE_COAS,
					BC::COL_REF_ID => DC::TABLE_BILLS,
				] as $column => $referencedTable
			)
				$table->foreign($column)
					->references('id')
					->on($referencedTable)
					->cascadeOnDelete();
			$this->addAuditColumns($table);
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table, self::TABLE);
			foreach (
				[
					BKC::COL_COA,
					BC::COL_REF_ID,
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
