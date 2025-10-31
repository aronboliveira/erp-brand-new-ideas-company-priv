<?php

namespace Modules\LandingPage\Entities;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JoinUs extends Model
{
    use HasFactory, UsesUuids;

    protected $table = 'join_us';
    protected $fillable = ['query_key', 'email'];

    protected static function newFactory()
    {
        return \Modules\LandingPage\Database\factories\JoinUsFactory::new();
    }
}
