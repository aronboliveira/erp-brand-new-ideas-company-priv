<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class Colorable extends Model
{
  use HasFactory, HasUuids;
  public $incrementing = false;
  protected $keyType = 'string';
  protected $fillable = [
    'color',
    'created_by',
  ];
  protected static function booted(): void
  {
    parent::booted();
    static::creating(function (self $model): void {
      $model->created_by = $model->created_by ?? Auth::id();
    });
  }
  public function getColorAttribute(?string $value): string
  {
    return $value ?? 'gray';
  }
}
