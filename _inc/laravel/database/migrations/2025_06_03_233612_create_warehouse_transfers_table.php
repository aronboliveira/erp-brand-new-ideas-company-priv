<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, TransportationMethod};
use App\Traits\{HasNullableAuditColumns, HasProductSecurityCoverage, RegistersShipping};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateWarehouseTransfersTable extends Migration
{
	use HasNullableAuditColumns, HasProductSecurityCoverage, RegistersShipping;
	private const TABLE = DC::TABLE_WRH_TRF;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string('code')->unique()->index(); // * generate at model as WRH-TRF-{UUID}-{TIMESTAMP}, checking uniqueness with do/while
			$table->uuid(BC::COL_PRD_ID)->index();
			$table->uuid(BC::COL_FROM_WRH)->index();
			$table->uuid(BC::COL_TO_WRH)->index();
			$this->addShippingColumns($table);
			$table->uuid('carrier')->nullable()->index(); // ? polymorphic key for User whereIn('type', [UserType::Company->value, UserType::Vendor->value]) or a id of a row in DC::TABLE_VENDORS
			$table->decimal(BC::COL_SVC_FEE, 10, 2)->nullable()->default(0); // ? service fee for the transfer system working
			$table->enum(BC::COL_TRP_MTD, array_column(TransportationMethod::cases(), 'value'))->default(TransportationMethod::Undefined->value)->nullable()->index(); // ? transport method for the transfer
			$table->decimal(BC::COL_TRP_CST, 10, 2)->nullable()->default(0); // ? transport cost for the transfer
			$this->addProductSecurityCoverageColumns($table);
			$table->uuid(BC::COL_REQ_BY)->nullable()->index();
			$table->dateTime(BC::COL_REQ_AT)->nullable()->index();
			$table->uuid(BC::COL_APV_BY)->nullable()->index();
			$table->dateTime(BC::COL_APV_AT)->nullable()->index();
			$table->uuid(PJC::COL_REJ_BY)->nullable()->index();
			$table->dateTime(PJC::COL_REJ_AT)->nullable()->index();
			$table->text(BC::COL_REJ_RS)->nullable(); // ? if REJ_BY is null, this must be null too
			$table->boolean(BC::COL_IS_RTNABLE)->default(false)->index();
			$table->unsignedInteger('quantity')->default(0);
			$table->unsignedInteger(BC::COL_QTY_RCV)->nullable()->default(0); // ? can NEVER be more than 'quantity' absolutely and added with BC::COL_QTY_RTN // ? nullable for tests; if null, then check the status: if EvaluationStatus::Completed->value, then assume all received; if not, then assume none received
			$table->unsignedInteger(BC::COL_QTY_RTN)->nullable()->default(0); // ? can NEVER be more than 'quantity' absolutely and added with COL_QTY_RCV // ? nullable for tests; if null, then check the status: if EvaluationStatus::Completed->value, then assume all received; if not, then assume none received
			$table->enum('status', array_column(EvaluationStatus::cases(), 'value'))->default(EvaluationStatus::Pending->value)->nullable()->index();
			$table->dateTime(BC::COL_SCHD_DT)->nullable()->index();
			$table->dateTime(BC::COL_SHIP_DT)->nullable()->index(); // ? can NEVER be lt COL_SCHD_DT if not null
			$table->dateTime(BC::COL_RCV_DT)->nullable()->index(); // ? can NEVER be lt COL_SHIP_DT if not null
			$table->boolean(BC::COL_IS_URGENT)->default(false)->index();
			$table->text('notes')->nullable();
			$table->date('date'); // ? this is redundant and should just mirror the COL_SHIP_DT
			$table->json('attachments')->nullable();
			$table->json('steps')->nullable(); // ? metadata about carrying, vendors, transport, etc.
			foreach (
				[
					BC::COL_FROM_WRH             => DC::TABLE_WHS,
					BC::COL_TO_WRH               => DC::TABLE_WHS,
					BC::COL_PRD_ID               => DC::TABLE_PROD_SERVS,
				] as $column => $referencedTable
			)
				$table->foreign($column)
					->references('id')
					->on($referencedTable)
					->cascadeOnDelete();
			$this->addNullableAuditColumns($table);
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			foreach (
				[
					BC::COL_FROM_WRH,
					BC::COL_TO_WRH,
					BC::COL_PRD_ID,
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
