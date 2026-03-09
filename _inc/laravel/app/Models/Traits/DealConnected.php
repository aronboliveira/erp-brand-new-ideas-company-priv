<?php

namespace App\Traits;

use App\Config\Constants\{
  ActivitiesConstants as AC,
  DatabaseConstants as DC,
};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{
  Log,
  Schema
};

trait DealConnected
{
  protected function addDealColumns(
    Blueprint $table,
    bool $unique = false,
    bool $nullable = false,
    bool $cascade = true
  ): void {
      try {
        $column = AC::COL_DL;
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
          ->on(DC::TABLE_DEALS);
        if ($nullable)
          $foreign->nullOnDelete();
        elseif ($cascade)
          $foreign->cascadeOnDelete();
        else
          $foreign->restrictOnDelete();
      } catch (\Throwable $e) {
          Log::error(static::class . '::addDealColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
      }
  }
  protected function dropDealColumnForeign(Blueprint $table, string $tableName): void
  {
      try {
        $column = AC::COL_DL;

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
          Log::error(static::class . '::dropDealColumnForeign — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
      }
  }
}
