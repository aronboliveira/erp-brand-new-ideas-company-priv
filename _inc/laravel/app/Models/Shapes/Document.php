<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\{DocumentKind, MimeType};

class Document extends AbstractDocument
{
    protected $table = DC::TABLE_DOCS;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->fillable = array_merge($this->fillable, ['name', 'is_required']);
    }

    protected $with = ['user'];

    protected $casts = [
        DC::COL_IPV       => 'boolean',
        'size'            => 'integer',
        DC::COL_EXP_DT    => 'date',
        DC::COL_LA        => 'date',
        DC::COL_MM_TP     => MimeType::class,
        'type'            => DocumentKind::class,
    ];
}
