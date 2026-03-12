<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log a user action.
     *
     * @param string $event
     * @param string|null $module
     * @param string|null $auditableType
     * @param int|null $auditableId
     * @param array|null $oldValues
     * @param array|null $newValues
     */
    public static function log($event, $module = null, $auditableType = null, $auditableId = null, $oldValues = null, $newValues = null)
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'module' => $module,
        ]);
    }
}
