<?php

namespace App\Policies;

use App\Models\Admin\AdminUser;

use Illuminate\Auth\Access\HandlesAuthorization;

class AdminUserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the adminUser can view any models.
     *
     * @param  \App\Models\Admin\AdminUser  $adminUser
     * @return bool
     */
    public function viewAny(AdminUser $adminUser): bool
    {
        return $adminUser->can('view_any_admin::user');
    }

    /**
     * Determine whether the adminUser can view the model.
     *
     * @param  \App\Models\Admin\AdminUser  $adminUser
     * @return bool
     */
    public function view(AdminUser $adminUser): bool
    {
        return $adminUser->can('view_admin::user');
    }

    /**
     * Determine whether the adminUser can create models.
     *
     * @param  \App\Models\Admin\AdminUser  $adminUser
     * @return bool
     */
    public function create(AdminUser $adminUser): bool
    {
        return $adminUser->can('create_admin::user');
    }

    /**
     * Determine whether the adminUser can update the model.
     *
     * @param  \App\Models\Admin\AdminUser  $adminUser
     * @return bool
     */
    public function update(AdminUser $adminUser): bool
    {
        return $adminUser->can('update_admin::user');
    }

    /**
     * Determine whether the adminUser can delete the model.
     *
     * @param  \App\Models\Admin\AdminUser  $adminUser
     * @return bool
     */
    public function delete(AdminUser $adminUser): bool
    {
        return $adminUser->can('delete_admin::user');
    }

    /**
     * Determine whether the adminUser can bulk delete.
     *
     * @param  \App\Models\Admin\AdminUser  $adminUser
     * @return bool
     */
    public function deleteAny(AdminUser $adminUser): bool
    {
        return $adminUser->can('delete_any_admin::user');
    }

    /**
     * Determine whether the adminUser can permanently delete.
     *
     * @param  \App\Models\Admin\AdminUser  $adminUser
     * @return bool
     */
    public function forceDelete(AdminUser $adminUser): bool
    {
        return $adminUser->can('force_delete_admin::user');
    }

    /**
     * Determine whether the adminUser can permanently bulk delete.
     *
     * @param  \App\Models\Admin\AdminUser  $adminUser
     * @return bool
     */
    public function forceDeleteAny(AdminUser $adminUser): bool
    {
        return $adminUser->can('force_delete_any_admin::user');
    }

    /**
     * Determine whether the adminUser can restore.
     *
     * @param  \App\Models\Admin\AdminUser  $adminUser
     * @return bool
     */
    public function restore(AdminUser $adminUser): bool
    {
        return $adminUser->can('restore_admin::user');
    }

    /**
     * Determine whether the adminUser can bulk restore.
     *
     * @param  \App\Models\Admin\AdminUser  $adminUser
     * @return bool
     */
    public function restoreAny(AdminUser $adminUser): bool
    {
        return $adminUser->can('restore_any_admin::user');
    }

    /**
     * Determine whether the adminUser can bulk restore.
     *
     * @param  \App\Models\Admin\AdminUser  $adminUser
     * @return bool
     */
    public function replicate(AdminUser $adminUser): bool
    {
        return $adminUser->can('replicate_admin::user');
    }

    /**
     * Determine whether the adminUser can reorder.
     *
     * @param  \App\Models\Admin\AdminUser  $adminUser
     * @return bool
     */
    public function reorder(AdminUser $adminUser): bool
    {
        return $adminUser->can('reorder_admin::user');
    }
}
