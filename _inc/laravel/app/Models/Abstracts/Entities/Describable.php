<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{
  Factories\HasFactory,
  Model
};

abstract class Describable extends Model
{
  use HasFactory, UsesUuids;
  protected $guarded = ['id'];
  protected $fillable = [
    'title',
    'description',
    'notes',
    'created_by',
    'document_url',
    'attachments',
  ];
  protected $casts = [
    DatabaseConstants::COL_C_AT => 'datetime',
    DatabaseConstants::COL_U_AT => 'datetime',
  ];
  protected $attributes = [
    'title'       => DatabaseConstants::DEFAULT_TT,
    'description' => DatabaseConstants::DEFAULT_DESC,
    'notes'       => DatabaseConstants::DEFAULT_NOTES,
  ];
}
