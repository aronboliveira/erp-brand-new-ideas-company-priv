<?php

use App\Config\Constants\{DatabaseConstants as DC, TemplatesConstants as TC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateGeneratedOfferLettersTable extends Migration
{
	use HasNullableAuditColumns;
	private const TABLE = DC::TABLE_GOL;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string(TC::COL_LG, 10)->default(DC::DEFAULT_LANG);
			$table->text(TC::COL_CT);
			$this->addNullableAuditColumns($table);
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table);
		});
		Schema::dropIfExists(self::TABLE);
	}
}
