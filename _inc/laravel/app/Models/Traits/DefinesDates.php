<?php

namespace App\Traits;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	BanksConstants as BKC,
	BillsConstants as BC,
	CompaniesConstants as CPC,
	DatabaseConstants as DC,
	MessagesConstants as MC,
	ProjectsConstants as PJC,
	SupportsConstants as SPC,
	UsersConstants as UC,
};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Support\Facades\{Log, Schema};

trait DefinesDates
{
	public const DATE_COLUMNS = [
		PJC::COL_S_DT,
		PJC::COL_E_DT,
		PJC::COL_D_DATE,
		AC::COL_PST_DT,
		AC::COL_JNG_DT,
		BKC::COL_TRF_DT,
		BC::COL_BL_DT,
		BC::COL_SD_DT,
		BC::COL_POS_DT,
		BC::COL_ISS_DT,
		BC::COL_RCC_DT,
		BC::COL_BNK_EXT_DT,
		CPC::COL_CPT_DT,
		CPC::COL_WRN_DATE,
		CPC::COL_FD_DT,
		CPC::COL_PRC_DT,
		CPC::COL_SPT_DT,
		DC::COL_EXP_DT,
		PJC::COL_RVS_DT,
		PJC::COL_APR_DT,
		UC::COL_PED,
		UC::COL_TERMINATION_DT,
		UC::COL_TERMINATION_NDT,
		UC::COL_RESIGNATION_DT,
		UC::COL_PRMT_DT,
		UC::COL_TRF_DT,
		PJC::COL_SBM_AT,
		PJC::COL_REJ_AT,
		PJC::COL_APV_AT,
		PJC::COL_INV_AT,
		MC::COL_SNT_AT,
		MC::COL_RD_AT,
		AC::COL_APL_AT,
		AC::COL_LST_RVW_AT,
		PJC::COL_JND_AT,
		PJC::COL_LFT_AT,
		PJC::COL_EXP_AT,
		SPC::COL_CLSD_AT,
	];

	protected static function bootDefinesDates(): void
	{


























																																																															}

	protected static function normalizeDate($value): ?\Carbon\Carbon
	{
	    try {
    		if ($value === null)
    			return null;
    		if ($value instanceof \Carbon\Carbon)
    			return $value;
    		try {
    			return \Carbon\Carbon::parse($value);
    		} catch (\Exception $e) {
    			return null;
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeDate — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return null;
	    }
	}

	protected static function denormalizeDate(\Carbon\Carbon $carbonDate, $originalValue, string $tableName, string $columnName)
	{
	    try {
    		if ($originalValue === null)
    			return null;
    		try {
    			$columnType = strtolower(Schema::getColumnType($tableName, $columnName));
    			if ($columnType === 'date')
    				return $carbonDate->format('Y-m-d');
    			if (in_array($columnType, ['datetime', 'timestamp']))
    				return $carbonDate->format('Y-m-d H:i:s');
    			return $carbonDate;
    		} catch (\Throwable $e) {
    			if (str_contains($e->getMessage(), 'enum'))
    				Log::debug("Skipped enum column: {$tableName}.{$columnName}");
    			return $carbonDate->format('Y-m-d H:i:s');
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::denormalizeDate — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return null;
	    }
	}
	public function getDates(): array
	{
	    try {
    		if (!$this instanceof Model)
    			return [];
    		$tableName = $this->getTable();
    		if (empty($tableName) || !is_string($tableName))
    			return [];
    		$dates = [];
    		if (method_exists($this, 'getDates') && is_callable('parent::getDates')) {
    			try {
    				$parentDates = parent::getDates();
    				is_array($parentDates) && $dates = array_merge($dates, $parentDates);
    			} catch (\Throwable $e) {
    				Log::notice("Failed to fetch parent dates for model", [
    					'table' => $tableName,
    					'error' => $e->getMessage(),
    					'file' => $e->getFile(),
    					'line' => $e->getLine(),
    					'trait' => __TRAIT__,
    					'class' => static::class,
    				]);
    			}
    		}
    		try {
    			foreach (static::DATE_COLUMNS as $dateColumn) {
    				try {
    					if (Schema::hasColumn($tableName, $dateColumn) && !in_array($dateColumn, $dates, true))
    						$dates[] = $dateColumn;
    				} catch (\Throwable $e) {
    					if (str_contains($e->getMessage(), 'enum'))
    						Log::debug("Skipped enum column: {$tableName}.{$dateColumn}");
    					continue;
    				}
    			}

    			try {
    				if (Schema::hasColumn($tableName, 'from')) {
    					$fromType = strtolower(Schema::getColumnType($tableName, 'from'));
    					if (in_array($fromType, ['date', 'timestamp', 'datetime']) && !in_array('from', $dates, true))
    						$dates[] = 'from';
    				}
    			} catch (\Throwable $e) {
    				if (str_contains($e->getMessage(), 'enum'))
    					Log::debug("Skipped enum column: {$tableName}.from");
    			}

    			try {
    				if (Schema::hasColumn($tableName, 'to')) {
    					$toType = strtolower(Schema::getColumnType($tableName, 'to'));
    					if (in_array($toType, ['date', 'timestamp', 'datetime']) && !in_array('to', $dates, true))
    						$dates[] = 'to';
    				}
    			} catch (\Throwable $e) {
    				if (str_contains($e->getMessage(), 'enum'))
    					Log::debug("Skipped enum column: {$tableName}.to");
    			}

    			$dates = array_values(array_unique($dates));
    		} catch (\Throwable $e) {
    			Log::warning("Failed to fetch date columns for model", [
    				'table' => $tableName,
    				'error' => $e->getMessage(),
    				'file' => $e->getFile(),
    				'line' => $e->getLine(),
    				'trait' => __TRAIT__,
    				'class' => static::class,
    			]);
    		}
    		return $dates;
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::getDates — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}
}
