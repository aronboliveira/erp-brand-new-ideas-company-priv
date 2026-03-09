<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Spatie\Permission\Models\Role as SpatieRole;
/**
 * @property int|null $id
 * @property string|null $name
 */

class Role extends SpatieRole
{
	use UsesUuids;
}
