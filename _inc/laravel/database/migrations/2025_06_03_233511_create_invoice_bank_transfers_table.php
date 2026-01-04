<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\PaymentStatus;
use App\Traits\{HasNullableAuditColumns, TracksFailures};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

class CreateInvoiceBankTransfersTable extends Migration
{
	use HasNullableAuditColumns, TracksFailures;
	private const TABLE = DC::TABLE_INV_BANK_TRANSFERS;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->uuid(BC::COL_BNK_TRF_ID)->nullable()->index(); // * TODO this should never be null, but keeping for tests
			$table->uuid(BC::COL_INV_ID)->nullable()->index();
			$table->uuid(BC::COL_OD_ID)->nullable()->index();
			$table->decimal('amount', 16, 2)->nullable(); // * uses the linked bank transfer as the source of truth for this, if existing
			$table->enum('status', PaymentStatus::values())->default(PaymentStatus::Pending->value)->nullable(); // ? nullable for testing purposes // todo in production the Completion-like status should throw if COL_PD_BY is not an existing and authorized user, but keeping as that for now // * uses the linked bank transfer as the source of truth for this, if existing
			$table->date('date')->nullable(); // * uses the linked bank transfer BC::COL_PD_AT for this if existing
			$table->string('receipt')->nullable(); // * constrained at model level to be match a secure url (with https + domain at env(APP_URL) or known domains of storage providers) OR a file path at the local filesystem OR a id for an existing Document (model) row
			foreach (
				[
					BC::COL_BNK_TRF_ID => DC::TABLE_BNK_TRF,
					BC::COL_INV_ID => DC::TABLE_INVS,
					BC::COL_OD_ID => DC::TABLE_ORDERS,
				] as $column => $referencedTable
			)
				$table->foreign($column)
					->references('id')
					->on($referencedTable)
					->nullOnDelete();
			$table->softDeletes();
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
					BC::COL_INV_ID,
					BC::COL_OD_ID,
					BC::COL_BNK_TRF_ID,
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
