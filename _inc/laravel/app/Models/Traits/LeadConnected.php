<?php

namespace App\Traits;

use App\Config\Constants\{
  DatabaseConstants as DC,
  ProjectsConstants as PJC
};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{
  Log,
  Schema
};

trait LeadConnected
{
  protected function addLeadColumns(
    Blueprint $table,
    bool $unique = false,
    bool $nullable = false,
    bool $cascade = true
  ): void {
      try {
        $column = PJC::COL_LD_ID;
        if ($unique)
          $nullable
            ? $table->uuid($column)->nullable()->unique()
            : $table->uuid($column)->unique();
        else
          $nullable
            ? $table->uuid($column)->nullable()->index()
            : $table->uuid($column)->index();
        $foreign = $table->foreign($column)
          ->references('id')
          ->on(DC::TABLE_LEADS);
        if ($nullable)
          $foreign->nullOnDelete();
        elseif ($cascade)
          $foreign->cascadeOnDelete();
        else
          $foreign->restrictOnDelete();
      } catch (\Throwable $e) {
          Log::error(static::class . '::addLeadColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
      }
  }
  protected function dropLeadColumnForeign(Blueprint $table, string $tableName): void
  {
      try {
        $column = PJC::COL_LD_ID;

        try {
          Schema::hasColumn($tableName, $column)
            && $table->dropForeign([$column]);
        } catch (\Exception $e) {
          Log::warning(
            'Failed to drop foreign key for '
              . $column
              . ' on table '
              . $tableName
              . ': '
              . $e->getMessage()
          );
        }
      } catch (\Throwable $e) {
          Log::error(static::class . '::dropLeadColumnForeign — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
      }
  }
}
