<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Enums\DocumentKind;
use App\Enums\MimeType;
use App\Enums\UserType;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends AbstractDocument
{
    protected $table = DC::TABLE_DOCS;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->fillable = array_merge($this->fillable, ['name', 'is_required']);
    }
    protected $with = ['user'];
    // * Migration define is_required como string; considerar boolean em evolução futura
    protected $casts = [
        'is_private'     => 'boolean',
        'size'           => 'integer',
        'expiration_date' => 'date',
        'last_accessed'  => 'date',
        'mime_type'      => MimeType::class,
        'type'           => DocumentKind::class,
    ];
}
