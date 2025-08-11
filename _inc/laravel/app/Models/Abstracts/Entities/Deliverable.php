<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Support\Str;

abstract class Deliverable extends Model
{
  use HasFactory;
  protected string $keyType = 'string';
  public bool $incrementing = false;
  protected array $casts = [
    'billing_phone_verified_at' => 'datetime',
    'email_verified_at' => 'datetime',
    'is_active' => 'boolean',
    'shipping_phone_verified_at' => 'datetime',
  ];
  protected array $fillable = [
    'avatar',
    'avatar_url',
    'billing_address',
    'billing_city',
    'billing_country',
    'billing_phone',
    'billing_phone_verified_at',
    'billing_state',
    'billing_zip',
    'contact',
    'created_by',
    'email',
    'email_verified_at',
    'is_active',
    'lang',
    'name',
    'notes',
    'secondary_email',
    'shipping_address',
    'shipping_city',
    'shipping_country',
    'shipping_name',
    'shipping_phone',
    'shipping_phone_verified_at',
    'shipping_state',
    'shipping_zip',
  ];
  protected static function booted(): void
  {
    parent::booted();
    static::creating(function (self $model): void {
      $model->{$model->getKeyName()} = (string) Str::uuid();
    });
  }
}
