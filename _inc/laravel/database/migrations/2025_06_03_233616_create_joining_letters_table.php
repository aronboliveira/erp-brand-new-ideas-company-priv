<?php

use App\Config\Constants\{DatabaseConstants as DC, TemplatesConstants as TC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

class CreateJoiningLettersTable extends Migration
{
	use HasNullableAuditColumns;
	private const TABLE = DC::TABLE_JL;
	public function up(): void
	{
		if (!Schema::hasTable(self::TABLE)) {
			Schema::create(self::TABLE, function (Blueprint $table) {
				$table->uuid('id')->primary();
				$table->string(TC::COL_LG, 10)
					->default(DC::DEFAULT_LANG)
					->index();
				$table->longText(TC::COL_CT);
				$this->addNullableAuditColumns($table);
			});
		}
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table);
		});
		Schema::dropIfExists(self::TABLE);
	}
}
