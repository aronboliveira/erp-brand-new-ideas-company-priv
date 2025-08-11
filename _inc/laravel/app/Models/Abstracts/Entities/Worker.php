<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

abstract class Worker extends Person
{
  use HasFactory;
  protected $casts = [
    ...parent::CASTS,
    'dob' => 'date',
  ];
  protected $fillable = [
    ...parent::FILLABLE,
    'dob',
    'skill',
  ];
}
