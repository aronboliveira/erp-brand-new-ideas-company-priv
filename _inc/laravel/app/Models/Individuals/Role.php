<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
	use UsesUuids;
}
