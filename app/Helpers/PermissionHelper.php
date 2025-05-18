<?php

namespace App\Helpers;

class PermissionHelper
{
    /**
     * Check if user has permission to perform an action
     *
     * @param  string  $action
     * @param  string  $module
     * @param  \App\Models\User|null  $user
     * @return bool
     */
    public static function hasPermission($action, $module, $user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }
        
        if (!$user) {
            return false;
        }

        // Admin permissions (full access)
        if ($user->role === 'admin') {
          if ($module === 'jemaat' && $action === 'create') {
              return false;
          }

            return true;
        }

        // Gembala permissions
        if ($user->role === 'gembala') {
            if ($module === 'jemaat') {
                return in_array($action, ['view', 'create', 'edit', 'delete', 'download']);
            }
            
            return false;
        }

        // Jemaat permissions
        if ($user->role === 'jemaat') {
            if ($module === 'worship-schedules' && $action === 'view') {
                return true;
            }
            
            if ($module === 'keuangan') {
                return in_array($action, ['view']);
            }
            
            if ($module === 'pengumuman' && $action === 'view') {
                return true;
            }
            
            return false;
        }

        return false;
    }
}