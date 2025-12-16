<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Enums\NotificationTemplateType;
use App\Traits\{HasNullableAuditColumns, IsTemplate};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateNotificationTemplatesTable extends Migration
{
	use HasNullableAuditColumns, IsTemplate;
	private const TABLE = DC::TABLE_NOTIFICATION_TEMPLATES;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string('name')->index();
			$table->enum('type', array_column(NotificationTemplateType::cases(), 'value'))->default(NotificationTemplateType::Other->value)->index();
			$table->unique(['type', 'slug'])->nullable(); // ? nullable to avoid issues with existing data
			$this->addTemplateColumns($table);
			$this->addAuditColumns($table);
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table, self::TABLE);
		});
		Schema::dropIfExists(self::TABLE);
	}
}
