<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Permission extends SpatiePermission implements Auditable
{
    use AuditableTrait;
}