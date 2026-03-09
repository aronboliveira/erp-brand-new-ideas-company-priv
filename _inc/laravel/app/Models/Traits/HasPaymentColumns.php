<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\{PaymentMethod, PaymentStatus, TransferType};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait HasPaymentColumns
{
	use HasFinancialIssuingColumns;

	protected static function bootHasPaymentColumns(): void
	{
	    try {
    		static::saving(function (Model $model): void {
    			try {
    				$tableName = $model->getTable();
    				$hasStatus = Schema::hasColumn($tableName, 'status');
    				$hasPaidBy = Schema::hasColumn($tableName, BC::COL_PD_BY);
    				$hasPaidAt = Schema::hasColumn($tableName, BC::COL_PD_AT);
    				$hasPaymentMethodLabel = Schema::hasColumn($tableName, BC::COL_PAY_MTD_LB);
    				$hasPaymentMethod = Schema::hasColumn($tableName, BC::COL_PAY_MTD);
    				if (!$hasStatus)
    					return;
    				$status = $model->getAttribute('status');
    				try {
    					$statusEnum = $status instanceof PaymentStatus
    						? $status
    						: PaymentStatus::tryFrom(is_string($status) ? $status : '');
    					if (!$statusEnum)
    						$statusEnum = PaymentStatus::Undefined;
    					$model->setAttribute('status', $statusEnum->value);
    				} catch (\Throwable $e) {
    					Log::error('HasPaymentColumns: failed to normalize status', [
    						'trait' => __TRAIT__,
    						'class' => get_class($model),
    						'table' => $tableName,
    						'status' => $status,
    						'error' => $e->getMessage(),
    					]);
    					$model->setAttribute('status', PaymentStatus::Undefined->value);
    					$statusEnum = PaymentStatus::Undefined;
    				}
    				$paidStatuses = [
    					PaymentStatus::Completed->value,
    					PaymentStatus::Refunded->value,
    					PaymentStatus::PartiallyRefunded->value,
    				];
    				$unpaidStatuses = [
    					PaymentStatus::Pending->value,
    					PaymentStatus::Failed->value,
    					PaymentStatus::Cancelled->value,
    					PaymentStatus::Expired->value,
    					PaymentStatus::Declined->value,
    					PaymentStatus::Undefined->value,
    				];
    				$isPaid = in_array($statusEnum->value, $paidStatuses, true);
    				$isUnpaid = in_array($statusEnum->value, $unpaidStatuses, true);
    				if ($isPaid) {
    					try {
    						if ($hasPaidBy) {
    							$paidBy = $model->getAttribute(BC::COL_PD_BY);
    							if (empty($paidBy)) {
    																$creator = Schema::hasColumn($tableName, DC::COL_TABLE_CREATOR)
    									? $model->getAttribute(DC::COL_TABLE_CREATOR)
    									: null;
    								$updater = Schema::hasColumn($tableName, DC::COL_TABLE_UPDATER)
    									? $model->getAttribute(DC::COL_TABLE_UPDATER)
    									: null;
    								$paidBy = $updater ?? $creator;
    								if (!empty($paidBy))
    									$model->setAttribute(BC::COL_PD_BY, $paidBy);
    							}
    						}
    						if ($hasPaidAt) {
    							$paidAt = $model->getAttribute(BC::COL_PD_AT);
    							if (empty($paidAt))
    								$model->setAttribute(BC::COL_PD_AT, now('America/Sao_Paulo'));
    						}
    						if ($hasPaidBy && $hasPaidAt) {
    							$paidBy = $model->getAttribute(BC::COL_PD_BY);
    							empty($paidBy) && $model->setAttribute(BC::COL_PD_AT, null);
    						}
    					} catch (\Throwable $e) {
    						Log::error('HasPaymentColumns: failed to set paid fields', [
    							'trait' => __TRAIT__,
    							'class' => get_class($model),
    							'table' => $tableName,
    							'status' => $statusEnum->value,
    							'error' => $e->getMessage(),
    						]);
    					}
    				} elseif ($isUnpaid) {
    					try {
    						if ($hasPaidBy)
    							$model->setAttribute(BC::COL_PD_BY, null);
    						if ($hasPaidAt)
    							$model->setAttribute(BC::COL_PD_AT, null);
    					} catch (\Throwable $e) {
    						Log::error('HasPaymentColumns: failed to clear paid fields', [
    							'trait' => __TRAIT__,
    							'class' => get_class($model),
    							'table' => $tableName,
    							'status' => $statusEnum->value,
    							'error' => $e->getMessage(),
    						]);
    					}
    				}
    				if ($hasPaymentMethodLabel) {
    					try {
    						$paymentMethodLabel = $model->getAttribute(BC::COL_PAY_MTD_LB);
    						$paymentMethodEnum = $paymentMethodLabel instanceof PaymentMethod
    							? $paymentMethodLabel
    							: PaymentMethod::tryFrom(is_string($paymentMethodLabel) ? $paymentMethodLabel : '');
    						if (!$paymentMethodEnum) {
    							$paymentMethodEnum = PaymentMethod::Other;
    							Log::notice('HasPaymentColumns: invalid payment method label, defaulting to Other', [
    								'trait' => __TRAIT__,
    								'class' => get_class($model),
    								'table' => $tableName,
    								'payment_method_label' => $paymentMethodLabel,
    							]);
    						}
    						$model->setAttribute(BC::COL_PAY_MTD_LB, $paymentMethodEnum->value);
    						if ($hasPaymentMethod) {
    							$paymentMethodNumeric = match ($paymentMethodEnum) {
    								PaymentMethod::BankTransfer => 1,
    								PaymentMethod::Pix          => 2,
    								PaymentMethod::Ted          => 3,
    								PaymentMethod::Doc          => 4,
    								PaymentMethod::WireTransfer => 5,
    								PaymentMethod::CardDebit    => 6,
    								PaymentMethod::CardCredit   => 7,
    								PaymentMethod::Cash         => 8,
    								PaymentMethod::Other        => 0,
    							};
    							$model->setAttribute(BC::COL_PAY_MTD, $paymentMethodNumeric);
    						}
    					} catch (\Throwable $e) {
    						Log::error('HasPaymentColumns: failed to normalize payment method', [
    							'trait' => __TRAIT__,
    							'class' => get_class($model),
    							'table' => $tableName,
    							'error' => $e->getMessage(),
    						]);
    						$model->setAttribute(BC::COL_PAY_MTD_LB, null);
    						if ($hasPaymentMethod)
    							$model->setAttribute(BC::COL_PAY_MTD, 0);
    					}
    				}
    				if (!$hasPaymentMethodLabel && $hasPaymentMethod) {
    					try {
    						$paymentMethodNumeric = $model->getAttribute(BC::COL_PAY_MTD);
    						$paymentMethodEnum = match ((int) $paymentMethodNumeric) {
    							1 => PaymentMethod::BankTransfer,
    							2 => PaymentMethod::Pix,
    							3 => PaymentMethod::Ted,
    							4 => PaymentMethod::Doc,
    							5 => PaymentMethod::WireTransfer,
    							6 => PaymentMethod::CardDebit,
    							7 => PaymentMethod::CardCredit,
    							8 => PaymentMethod::Cash,
    							default => PaymentMethod::Other,
    						};
    						Log::debug('HasPaymentColumns: derived payment method from numeric', [
    							'trait' => __TRAIT__,
    							'class' => get_class($model),
    							'table' => $tableName,
    							'numeric_method' => $paymentMethodNumeric,
    							'derived_method' => $paymentMethodEnum->value,
    						]);
    					} catch (\Throwable $e) {
    						Log::error('HasPaymentColumns: failed to derive payment method', [
    							'trait' => __TRAIT__,
    							'class' => get_class($model),
    							'table' => $tableName,
    							'error' => $e->getMessage(),
    						]);
    					}
    				}
    			} catch (\Throwable $e) {
    				Log::error('HasPaymentColumns: unexpected error in trait', [
    					'trait' => __TRAIT__,
    					'class' => get_class($model),
    					'table' => $model->getTable(),
    					'method' => 'static::saving',
    					'file' => $e->getFile(),
    					'line' => $e->getLine(),
    					'error' => $e->getMessage(),
    				]);
    			}
    		});
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::bootHasPaymentColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	protected function addBasicPaymentColumns(Blueprint $table, ?bool $nullableInvoice = true): void
	{
	    try {
    		$this->addBasicFinancialIssuingColumns($table);
    		$table->boolean(BC::COL_IS_SCD)->default(false)->nullable();
    		$table->boolean(BC::COL_CAN_CHG_BK)->default(false)->nullable();
    		$table->unsignedSmallInteger(BC::COL_N_INTR)->default(1)->nullable();
    		$table->unsignedSmallInteger(BC::COL_CURR_N_INTR)->default(1)->nullable();
    		$table->string(BC::COL_PPS_CD)->index()->default('300')->nullable();
    		$table->enum(BC::COL_TRF_TP, TransferType::values())->default(TransferType::Other)->nullable();
    		$table->text(BC::COL_PPS_DS)->nullable();
    		$nullableInvoice ? $table->uuid('invoice')->nullable()->index() : $table->uuid(BC::COL_INV_ID)->index();
    		$nullableInvoice ? $table->foreign('invoice')
    			->references('id')
    			->on(DC::TABLE_INVS)
    			->nullOnDelete() :
    			$table->foreign(BC::COL_INV_ID)
    			->references('id')
    			->on(DC::TABLE_INVS)
    			->restrictOnDelete();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addBasicPaymentColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	protected function addPaymentColumns(Blueprint $table, bool $nullableReconcile = true, bool $nullableInvoice = true, bool $includePayslip = true): void
	{
	    try {
    		$this->addFinancialIssuingColumns($table, $nullableReconcile);
    				$table->boolean(BC::COL_IS_SCD)->default(false)->nullable();
    		$table->boolean(BC::COL_CAN_CHG_BK)->default(false)->nullable();
    				$table->string(BC::COL_PPS_CD)->index()->default('300')->nullable();
    		$table->enum(BC::COL_TRF_TP, TransferType::values())->default(TransferType::Other)->nullable();
    		$table->text(BC::COL_PPS_DS)->nullable();
    		$table->json(BC::COL_TXS_LST)->nullable();
    		$table->unsignedTinyInteger(BC::COL_PAY_MTD)->default(0);
    		$table->enum(BC::COL_PAY_MTD_LB, PaymentMethod::values())->default(PaymentMethod::Other)->nullable();
    		$table->enum('status', PaymentStatus::values())->default(PaymentStatus::Pending->value)->nullable();
    		$table->uuid(BC::COL_PD_BY)->nullable()->index();
    		$table->timestamp(BC::COL_PD_AT)->nullable();
    		$table->unsignedSmallInteger(BC::COL_N_INTR)->default(1)->nullable();
    		$table->unsignedSmallInteger(BC::COL_CURR_N_INTR)->default(1)->nullable();
    		$nullableReconcile ? $table->timestamp(BC::COL_RCC_AT)->nullable() : $table->timestamp(BC::COL_RCC_AT);
    		$table->uuid(BC::COL_RCC_BY)->nullable();
    				$nullableInvoice ? $table->uuid('invoice')->nullable()->index() : $table->uuid(BC::COL_INV_ID)->index();
    		$includePayslip && $table->uuid('payslip')->nullable()->index();
    		$nullableInvoice ? $table->foreign('invoice')
    			->references('id')
    			->on(DC::TABLE_INVS)
    			->nullOnDelete() :
    			$table->foreign(BC::COL_INV_ID)
    			->references('id')
    			->on(DC::TABLE_INVS)
    			->restrictOnDelete();
    		$table->foreign(BC::COL_RCC_BY)
    			->references('id')
    			->on(DC::TABLE_USERS)
    			->nullOnDelete();
    		$includePayslip && $table->foreign('payslip')
    			->references('id')
    			->on(DC::TABLE_PAY_SLP)
    			->nullOnDelete();
    		$table->json('qr')->nullable();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addPaymentColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	protected function dropBasicPaymentColumnForeigns(Blueprint $table, ?string $tableName = null): void
	{
	    try {
    		$this->dropBasicFinancialIssuingColumnForeigns($table, $tableName);
    		try {
    			Schema::hasColumn($tableName ?? $table->getTable(), 'invoice')
    				&&
    				$table->dropForeign(['invoice']);
    		} catch (\Exception $e) {
    			Log::warning(
    				'Failed to drop foreign key for invoice: '
    					. $e->getMessage()
    			);
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::dropBasicPaymentColumnForeigns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	protected function dropPaymentColumnForeigns(Blueprint $table, ?string $tableName = null): void
	{
	    try {
    		$this->dropFinancialIssuingColumnForeigns($table, $tableName);
    		foreach (
    			[
    				BC::COL_RCC_BY,
    				'invoice',
    			] as $col
    		) {
    			try {
    				Schema::hasColumn($tableName ?? $table->getTable(), $col) &&
    					$table->dropForeign([$col]);
    			} catch (\Exception $e) {
    				Log::warning(
    					'Failed to drop foreign key for '
    						. $col
    						. ': '
    						. $e->getMessage()
    				);
    			}
    		}
    		if (Schema::hasColumn($tableName ?? $table->getTable(), 'payslip')) {
    			try {
    				$table->dropForeign(['payslip']);
    			} catch (\Exception $e) {
    				Log::warning(
    					'Failed to drop foreign key for payslip: '
    						. $e->getMessage()
    				);
    			}
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::dropPaymentColumnForeigns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
