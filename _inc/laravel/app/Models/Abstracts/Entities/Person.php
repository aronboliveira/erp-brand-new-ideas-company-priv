<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @property string|null $created_by
 */
abstract class Person extends Model
{
  use HasFactory, HasUuids;
  public $incrementing = false;
  protected $keyType = 'string';
  public const FILLABLE = [
    'id',
    'name',
    'phone',
    'email',
    'email_verified_at',
    'gender',
    'country',
    'state',
    'city',
    'address',
    'created_by',
  ];
  public const CASTS = [
    'email_verified_at' => 'datetime',
  ];
  protected $fillable = self::FILLABLE;
  protected $casts = self::CASTS;
  protected static function booted(): void
  {
    parent::booted();
    static::creating(function (self $model): void {
      $model->created_by = Auth::id();
    });
  }
}
