<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait EmployeeConnected
{
  protected function addEmployeeColumns(Blueprint $table, bool $unique = false, bool $nullable = false, bool $cascade = true): void
  {
    $unique ? ($nullable ? $table->uuid(UC::COL_EMP_ID)->nullable()->unique() : $table->uuid(UC::COL_EMP_ID)->index()) : ($nullable ? $table->uuid(UC::COL_EMP_ID)->nullable()->index() : $table->uuid(UC::COL_EMP_ID)->index());
    $nullable ?
      $table->foreign(UC::COL_EMP_ID)
      ->references('id')
      ->on(DC::TABLE_EMPLOYEES)
      ->nullOnDelete() : ($cascade ?
        $table->foreign(UC::COL_EMP_ID)
        ->references('id')
        ->on(DC::TABLE_EMPLOYEES)
        ->cascadeOnDelete() :
        $table->foreign(UC::COL_EMP_ID)
        ->references('id')
        ->on(DC::TABLE_EMPLOYEES)
        ->restrictOnDelete());
  }
  protected function dropEmployeeColumnForeign(Blueprint $table, string $tableName): void
  {
    try {
      Schema::hasColumn($tableName, UC::COL_EMP_ID) &&
        $table->dropForeign([UC::COL_EMP_ID]);
    } catch (\Exception $e) {
      Log::warning(
        'Failed to drop foreign key for '
          . UC::COL_EMP_ID
          . ' on table '
          . $tableName
          . ': '
          . $e->getMessage()
      );
    }
  }
}
