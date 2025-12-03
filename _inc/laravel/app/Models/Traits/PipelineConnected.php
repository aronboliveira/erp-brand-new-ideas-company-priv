<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait PipelineConnected
{
  protected function addPipelineColumns(Blueprint $table, bool $unique = false, bool $nullable = false, bool $cascade = true): void
  {
    $unique ? ($nullable ? $table->uuid(PJC::COL_PPL_ID)->nullable()->unique() : $table->uuid(PJC::COL_PPL_ID)->index()) : ($nullable ? $table->uuid(PJC::COL_PPL_ID)->nullable()->index() : $table->uuid(PJC::COL_PPL_ID)->index());
    $nullable ?
      $table->foreign(PJC::COL_PPL_ID)
      ->references('id')
      ->on(DC::TABLE_PIPELINES)
      ->nullOnDelete() : ($cascade ?
        $table->foreign(PJC::COL_PPL_ID)
        ->references('id')
        ->on(DC::TABLE_PIPELINES)
        ->cascadeOnDelete() :
        $table->foreign(PJC::COL_PPL_ID)
        ->references('id')
        ->on(DC::TABLE_PIPELINES)
        ->restrictOnDelete());
  }
  protected function dropPipelineColumnForeign(Blueprint $table, string $tableName): void
  {
    try {
      Schema::hasColumn($tableName, PJC::COL_PPL_ID) &&
        $table->dropForeign([PJC::COL_PPL_ID]);
    } catch (\Exception $e) {
      Log::warning(
        'Failed to drop foreign key for '
          . PJC::COL_PPL_ID
          . ' on table '
          . $tableName
          . ': '
          . $e->getMessage()
      );
    }
  }
}
