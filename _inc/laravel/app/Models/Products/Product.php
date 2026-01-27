<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{ExtendsProductServiceTable, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use UsesUuids, HasAuditFields, ExtendsProductServiceTable;

    protected $table = DC::TABLE_PRD;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $fillable = [
        BC::COL_PRD_SV_ID,
        'name',
        'price',
        'quantity',
        'description',
        'image',
        'type',
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
    ];

    public $customField;

    public function productService(): BelongsTo
    {
        $prodServTable = DC::TABLE_PROD_SERVS;
        return $this->belongsTo(ProductService::class, BC::COL_PRD_SV_ID, 'id');
    }
}
