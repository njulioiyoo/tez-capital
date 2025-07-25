<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Role extends SpatieRole implements Auditable
{
    use AuditableTrait;
}