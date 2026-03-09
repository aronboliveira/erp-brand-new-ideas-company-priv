<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC};
use App\Models\{User as Usr};
use Illuminate\Support\{Str as Str};
use Illuminate\Support\Facades\{Hash, Log};

trait EnsuresSystemUser
{
	protected function ensureSystemUser(): string
	{
	    try {
    		$id = DC::DEFAULT_UUID;

    		if (!Usr::where('id', $id)->exists()) {
    			$faker = fake('pt_BR');

    			$u = new Usr();
    			$u->id = $id;
    			$u->name = $faker->name();
    			$u->email = $faker->unique()->safeEmail();
    			$u->password = Hash::make(Str::password());
    			$u->save();
    		}

    		return $id;
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::ensureSystemUser — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return '';
	    }
	}
}
