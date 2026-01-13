<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateClientPermissionsTable extends Migration
{
	use HasNullableAuditColumns;
	private const TABLE = DC::TABLE_CLT_PRM;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->uuid(PJC::COL_CLIENT_ID)->index(); // ? Polymorphic key enforced at model level as a id for a row in DC::TABLE_CLIENTS or {DC::TABLE_USERS where type in [UserType::Client->value, UserType::Customer->value, UserType::Vendor->value, UserType::Company->value]}
			$table->enum('type', ['client', 'customer', 'vendor', 'company', 'user_client', 'user_customer', 'user_vendor', 'user_company'])->default('user_client')->nullable()->index();
			$table->unique([PJC::COL_CLIENT_ID, 'type']);
			$table->uuid(PJC::COL_DL_ID)->nullable();
			$table->uuid(PJC::COL_CTC_ID)->nullable();
			$table->longText('permissions'); // ? this should be filtered as a comma/newline separated list of ids or names queried against DC::TABLE_PERMISSIONS
			foreach (
				[
					PJC::COL_DL_ID => DC::TABLE_DEALS,
					PJC::COL_CTC_ID => DC::TABLE_CONTRACTS,
				] as $column => $referencedTable
			)
				$table->foreign($column)
					->references('id')
					->on($referencedTable)
					->nullOnDelete();
			$this->addNullableAuditColumns($table);
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table);
			foreach (
				[
					PJC::COL_DL_ID,
					PJC::COL_CTC_ID
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
