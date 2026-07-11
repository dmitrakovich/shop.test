<?php

namespace App\Models\Admin;

use App\Contracts\AuthorInterface;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $user_last_name Фамилия
 * @property string|null $user_patronymic_name Отчество
 * @property string|null $trust_number Номер доверенности
 * @property string|null $trust_date Дата доверенности
 * @property string $avatar
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Encore\Admin\Auth\Database\Role[] $oldRoles
 * @property-read \Illuminate\Database\Eloquent\Collection|\Encore\Admin\Auth\Database\Permission[] $oldPermissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin\AdminUser role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin\AdminUser withoutRole($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin\AdminUser permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin\AdminUser withoutPermission($permissions)
 */
class AdminUser extends Authenticatable implements AuthorInterface, FilamentUser
{
    use HasPanelShield;
    use HasRoles;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    public function getFullName(): string
    {
        return trim("{$this->user_last_name} {$this->name} {$this->user_patronymic_name}");
    }

    // !!! old admin roles & permissions
    // *** роли для старой админки используются, они мешают работать новой админке
    // *** пока старая админка не будет полностью выпилена, права корректно работать не будут
    //
    // TEMPORARY: full access for any authenticated admin user (Filament + legacy admin).
    // Revisit when the Filament migration is complete and configure Shield permissions properly.

    /**
     * Get avatar attribute.
     *
     * @param  string  $avatar
     */
    public function getAvatarAttribute($avatar): string
    {
        return admin_asset('favicon-96x96.png');
    }

    /**
     * TEMPORARY: allow every authenticated admin into the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany<\Encore\Admin\Auth\Database\Role, $this, Pivot>
     */
    public function oldRoles(): BelongsToMany
    {
        $relatedModel = \Encore\Admin\Auth\Database\Role::class;

        return $this->belongsToMany($relatedModel, 'admin_role_users', 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany<\Encore\Admin\Auth\Database\Permission, $this, Pivot>
     */
    public function oldPermissions(): BelongsToMany
    {
        $relatedModel = \Encore\Admin\Auth\Database\Permission::class;

        return $this->belongsToMany($relatedModel, 'admin_user_permissions', 'user_id', 'permission_id');
    }

    /**
     * Get all old permissions of user.
     *
     * @return mixed
     */
    public function allPermissions(): Collection
    {
        $rolesId = DB::table('admin_role_users')->where('user_id', auth()->id())->pluck('role_id');
        $permissionsId = DB::table('admin_role_permissions')->whereIn('role_id', $rolesId)->pluck('permission_id');
        $permissions = \Encore\Admin\Auth\Database\Permission::query()->whereIn('id', $permissionsId)->get();

        return $permissions;
    }

    /**
     * Check if user has permission.
     *
     * TEMPORARY: always allow — restore real checks after Filament migration.
     *
     * @param  array  $arguments
     */
    public function can($ability, $arguments = []): bool
    {
        return true;
    }

    /**
     * Check if user has no permission.
     *
     * @param  $permission
     *
     * @return bool
     */
    public function cannot($abilities, $arguments = [])
    {
        return !$this->can($abilities);
    }

    /**
     * Check if user is administrator.
     *
     * TEMPORARY: always true — restore real checks after Filament migration.
     * Legacy laravel-admin also calls isRole('administrator') via Auth\Permission.
     */
    public function isAdministrator(): bool
    {
        return true;
    }

    /**
     * Check if user is $role.
     *
     * TEMPORARY: treat every user as legacy `administrator` so Encore\Admin\Auth\Permission
     * bypasses work. Other role slugs still resolve against oldRoles.
     *
     * @return mixed
     */
    public function isRole(string $role): bool
    {
        if ($role === 'administrator') {
            return true;
        }

        return $this->oldRoles->pluck('slug')->contains($role);
    }

    /**
     * Check if user in $oldRoles.
     *
     * TEMPORARY: always true — restore real checks after Filament migration.
     *
     * @param  array  $oldRoles
     *
     * @return mixed
     */
    public function inRoles(array $roles = []): bool
    {
        return true;
    }

    /**
     * If visible for roles.
     *
     * TEMPORARY: always true — restore real checks after Filament migration.
     */
    public function visible(array $roles = []): bool
    {
        return true;
    }

    public static function getTypeName(): string
    {
        return 'Админ';
    }
}
