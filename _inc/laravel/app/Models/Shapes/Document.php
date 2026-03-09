<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Support\Facades\Schema;
/**
 * @property mixed $created_by
 */

class Document extends AbstractDocument
{
    protected $table = DC::TABLE_DOCS;

    protected $casts = [
        ...self::ABSTRACT_DOCUMENT_CASTS,
        DC::COL_IPV       => 'boolean',
    ];

    protected $fillable = [
        ...self::ABSTRACT_FILE_FILLABLE,
        'number',
        DC::COL_IR,
        DC::COL_IPV,
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (self $m): void {
            if (Schema::getColumnType($m->getTable(), DC::COL_IR) === 'string') {
                $ir = $m->getAttribute(DC::COL_IR);
                if (is_string($ir)) {
                    $normalized = strtolower($ir);
                    if (in_array($normalized, ['true', '1', 'yes', 'checked'], true))
                        $m->setAttribute(DC::COL_IR, 'true');
                    elseif (in_array($normalized, ['false', '0', 'no', 'unchecked', 'undefined', 'null'], true))
                        $m->setAttribute(DC::COL_IR, 'false');
                    else $m->setAttribute(DC::COL_IR, 'false');
                }
            }
        });
    }
}
