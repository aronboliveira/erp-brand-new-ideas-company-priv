<?php

namespace App\Traits;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

trait BankAccountConnected
{
  // TODO: add migration for account_id foreign key
  public function account(): BelongsTo
  {
    return $this->belongsTo(BankAccount::class, 'account_id');
  }

  public function getBankAccountById(string $accountId): ?BankAccount
  {
    try {
      $qs = BankAccount::whereKey($accountId);
      if (!$qs->exists()) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . " No BankAccount found with ID {$accountId} for " . static::class);
        return null;
      }
      if ($qs->count() > 1) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . " Multiple BankAccounts ({$qs->count()}) found with ID {$accountId} for " . static::class);
      }
      return $qs->first();
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . " Error fetching BankAccount by ID {$accountId}: {$e->getMessage()}");
      return null;
    }
  }

  public function getBankAccountByDetails(string $holderName, string $bankName): ?BankAccount
  {
    try {
      $qs = BankAccount::where('holder_name', $holderName)
        ->where('bank_name', $bankName);
      if (!$qs->exists()) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . " No BankAccount found for {$holderName}@{$bankName} in " . static::class);
        return null;
      }
      if ($qs->count() > 1) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . " Multiple BankAccounts ({$qs->count()}) for {$holderName}@{$bankName} in " . static::class);
      }
      return $qs->first();
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . " Error fetching BankAccount by details {$holderName}@{$bankName}: {$e->getMessage()}");
      return null;
    }
  }
}
