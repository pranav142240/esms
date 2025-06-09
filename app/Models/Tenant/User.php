<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'tenant';

    /**
     * The guard name for Spatie permissions.
     *
     * @var string
     */
    protected $guard_name = 'tenant';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'is_school_owner',
        'original_admin_id',
        'avatar',
        'address',
        'date_of_birth',
        'gender',
        'emergency_contact',
        'settings',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'is_school_owner' => 'boolean',
        'settings' => 'array'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'avatar_url',
        'role_display_name'
    ];

    /**
     * Get the avatar URL attribute.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return url('storage/avatars/' . $this->avatar);
        }
        
        return $this->getDefaultAvatar();
    }

    /**
     * Get default avatar based on user's initials.
     */
    protected function getDefaultAvatar(): string
    {
        $initials = strtoupper(substr($this->name, 0, 1));
        if (strpos($this->name, ' ') !== false) {
            $nameParts = explode(' ', $this->name);
            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
        }
        
        return "https://ui-avatars.com/api/?name={$initials}&background=random&color=fff&size=128";
    }

    /**
     * Get role display name attribute.
     */
    public function getRoleDisplayNameAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'teacher' => 'Teacher',
            'student' => 'Student',
            'parent' => 'Parent',
            'accountant' => 'Accountant',
            'librarian' => 'Librarian',
            default => ucfirst($this->role)
        };
    }

    /**
     * Check if user is school owner.
     */
    public function isSchoolOwner(): bool
    {
        return $this->is_school_owner === true;
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user has specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role || parent::hasRole($role);
    }

    /**
     * Get users by role.
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Get active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get school owners.
     */
    public function scopeSchoolOwners($query)
    {
        return $query->where('is_school_owner', true);
    }

    /**
     * Get full name with role.
     */
    public function getFullNameWithRole(): string
    {
        return "{$this->name} ({$this->role_display_name})";
    }

    /**
     * Check if user can manage other users.
     */
    public function canManageUsers(): bool
    {
        return $this->is_school_owner || $this->role === 'admin' || $this->can('manage_users');
    }

    /**
     * Check if user can access admin features.
     */
    public function canAccessAdmin(): bool
    {
        return in_array($this->role, ['admin']) || $this->is_school_owner;
    }

    /**
     * Get user's permissions array.
     */
    public function getUserPermissions(): array
    {
        $rolePermissions = $this->getPermissionsViaRoles()->pluck('name')->toArray();
        $directPermissions = $this->getDirectPermissions()->pluck('name')->toArray();
        
        return array_unique(array_merge($rolePermissions, $directPermissions));
    }

    /**
     * Create tokens for API authentication.
     */
    public function createAuthToken(string $name = 'auth-token'): string
    {
        // Revoke existing tokens
        $this->tokens()->where('name', $name)->delete();
        
        // Create new token
        return $this->createToken($name, ['*'], now()->addHours(24))->plainTextToken;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            // Assign default role permissions when user is created
            if ($user->role && !$user->roles()->exists()) {
                $user->assignRole($user->role);
            }
        });
    }
}
