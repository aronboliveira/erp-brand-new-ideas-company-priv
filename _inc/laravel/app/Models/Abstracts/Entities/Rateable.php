<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Support\Str;

abstract class Rateable extends Model
{
  use HasFactory;
  public bool $incrementing = false;
  protected string $keyType = 'string';
  protected array $fillable = [
    'administration',
    'administration_rating',
    'attendance',
    'attendance_rating',
    'customer_experience',
    'customer_experience_rating',
    'integrity',
    'integrity_rating',
    'marketing',
    'marketing_rating',
    'overall_rating',
    'rateable_created_by',
    'rating',
  ];

  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'rateable_created_by');
  }

  protected static function booted(): void
  {
    parent::booted();
    static::creating(function (self $model): void {
      $model->{$model->getKeyName()} = (string) Str::uuid();
    });
  }
}
