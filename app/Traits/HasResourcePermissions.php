<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasResourcePermissions
{
    /**
     * Get the permission name for this resource
     */
    protected static function getResourcePermissionName(): string
    {
        // Override this in each resource
        return 'default';
    }

    /**
     * Check if user can view any records
     */
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        if (!method_exists($user, 'hasPermission')) {
            return false;
        }

        $permissionName = static::getResourcePermissionName() . '.view';
        return $user->hasPermission($permissionName);
    }

    /**
     * Check if user can create records
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        if (!method_exists($user, 'hasPermission')) {
            return false;
        }

        $permissionName = static::getResourcePermissionName() . '.create';
        return $user->hasPermission($permissionName);
    }

    /**
     * Check if user can edit records
     */
    public static function canEdit($record): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        if (!method_exists($user, 'hasPermission')) {
            return false;
        }

        $permissionName = static::getResourcePermissionName() . '.edit';
        return $user->hasPermission($permissionName);
    }

    /**
     * Check if user can delete records
     */
    public static function canDelete($record): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        if (!method_exists($user, 'hasPermission')) {
            return false;
        }

        $permissionName = static::getResourcePermissionName() . '.delete';
        return $user->hasPermission($permissionName);
    }

    /**
     * Check if user can view a record
     */
    public static function canView($record): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        if (!method_exists($user, 'hasPermission')) {
            return false;
        }

        $permissionName = static::getResourcePermissionName() . '.view';
        return $user->hasPermission($permissionName);
    }
}
