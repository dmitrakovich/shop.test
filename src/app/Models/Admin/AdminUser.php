<?php

namespace App\Models\Admin;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class AdminUser extends Authenticatable implements FilamentUser
{
    use HasPanelShield;
    use HasRoles;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    public function getFullName(): string
    {
        return trim("{$this->user_last_name} {$this->name} {$this->user_patronymic_name}");
    }

    // !!! old admin roles & permissions

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
     * A user has and belongs to many roles.
     */
    public function oldRoles(): BelongsToMany
    {
        $relatedModel = \Encore\Admin\Auth\Database\Role::class;

        return $this->belongsToMany($relatedModel, 'admin_role_users', 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
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
     * @param  array  $arguments
     */
    public function can($ability, $arguments = []): bool
    {
        if (empty($ability)) {
            return true;
        }

        if ($this->isAdministrator()) {
            return true;
        }

        if ($this->oldPermissions->pluck('slug')->contains($ability)) {
            return true;
        }

        return $this->oldRoles->pluck('permissions')->flatten()->pluck('slug')->contains($ability);
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
     * @return mixed
     */
    public function isAdministrator(): bool
    {
        return $this->isRole('super_admin');
    }

    /**
     * Check if user is $role.
     *
     *
     * @return mixed
     */
    public function isRole(string $role): bool
    {
        return $this->oldRoles->pluck('slug')->contains($role);
    }

    /**
     * Check if user in $oldRoles.
     *
     * @param  array  $oldRoles
     *
     * @return mixed
     */
    public function inRoles(array $roles = []): bool
    {
        return $this->oldRoles->pluck('slug')->intersect($roles)->isNotEmpty();
    }

    /**
     * If visible for roles.
     */
    public function visible(array $roles = []): bool
    {
        if (empty($roles)) {
            return true;
        }

        $roles = array_column($roles, 'slug');

        return $this->inRoles($roles) || $this->isAdministrator();
    }
}
