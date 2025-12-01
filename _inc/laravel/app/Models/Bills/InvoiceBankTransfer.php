<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};

class InvoiceBankTransfer extends Model
{
    use HasFactory;

    protected $table = DatabaseConstants::TABLE_INV_BANK_TRANSFERS;

    protected $fillable = [
        DatabaseConstants::INV_BANK_TRANSFER_INV,
        DatabaseConstants::INV_BANK_TRANSFER_ORDER,
        'amount',
        'status',
        'date',
        'receipt',
        DatabaseConstants::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'date',
    ];

    /**
     ** @return BelongsTo<Invoice, InvoiceBankTransfer>
     **/
    public function invoice(): BelongsTo // * ADDED
    {
        return $this->belongsTo(Invoice::class, DatabaseConstants::INV_BANK_TRANSFER_INV, 'id');
    }

    /**
     ** @return BelongsTo<Order, InvoiceBankTransfer>
     **/
    public function order(): BelongsTo // * ADDED
    {
        return $this->belongsTo(Order::class, DatabaseConstants::INV_BANK_TRANSFER_ORDER, 'id');
    }

    /**
     ** @return BelongsTo<User, InvoiceBankTransfer>
     **/
    public function createdBy(): BelongsTo // * ADDED
    {
        return $this->belongsTo(User::class, DatabaseConstants::COL_TABLE_CREATOR, 'id');
    }
}
