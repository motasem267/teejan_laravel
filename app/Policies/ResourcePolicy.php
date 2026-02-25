<?php

namespace App\Policies;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;

class ResourcePolicy
{
    protected string $resourceName;

    public function __construct(string $resourceName)
    {
        $this->resourceName = $resourceName;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Employee $employee): bool
    {
        return $employee->canView($this->resourceName);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Employee $employee, Model $model): bool
    {
        return $employee->canView($this->resourceName);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Employee $employee): bool
    {
        return $employee->canCreate($this->resourceName);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Employee $employee, Model $model): bool
    {
        return $employee->canEdit($this->resourceName);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Employee $employee, Model $model): bool
    {
        return $employee->canDelete($this->resourceName);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Employee $employee, Model $model): bool
    {
        return $employee->canEdit($this->resourceName);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Employee $employee, Model $model): bool
    {
        return $employee->canDelete($this->resourceName);
    }
}
