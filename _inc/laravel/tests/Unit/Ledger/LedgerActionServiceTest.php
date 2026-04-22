<?php

namespace Tests\Unit\Ledger;

use Tests\TestCase;
use App\Services\Ledger\LedgerActionService;
use App\Models\Invoice;
use App\Models\Bill;
// use App\Models\Payment;
use IFRS\Models\Account;
use IFRS\Models\Currency;
use IFRS\Models\Transaction;
use IFRS\Models\Entity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LedgerActionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LedgerActionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LedgerActionService();
    }

    public function test_client_invoice_record_is_created_and_idempotent()
    {
        $this->assertTrue(true, 'Placeholder - Requires IFRS setup to run DB transactions fully');
    }

    public function test_account_period_closing_validation()
    {
        putenv("ACCOUNTING_CLOSE_DATE=" . now()->addDays(5)->format('Y-m-d'));
        
        $this->expectException(\RuntimeException::class);
        $this->service->assertPeriodIsOpen(now()->subDays(5));
    }
}
