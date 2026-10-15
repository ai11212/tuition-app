<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** Capabilities that can be granted to a non-admin user */
    public const CAPABILITIES = [
        'attendance' => 'Log attendance (New Attendance + View Attendance)',
        'books'      => 'Manage books (issue, edit, delete, Book Library)',
    ];

    protected $fillable = ['name','email','password','is_admin','capabilities','is_active'];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'capabilities' => 'array',
        'is_admin'     => 'boolean',
        'is_active'    => 'boolean',
    ];

    /**
     * Backward compatibility: if the roles columns are not present yet — for
     * example the code is deployed before the ALTER TABLE has been run — behave
     * exactly as the app did before roles existed: the account has full access.
     *
     * Without this, a missing column reads as null, which would refuse every
     * login and deny every ability, locking everyone out of a working system.
     */
    public function getIsAdminAttribute($value): bool
    {
        return array_key_exists('is_admin', $this->attributes) ? (bool) $value : true;
    }

    public function getIsActiveAttribute($value): bool
    {
        return array_key_exists('is_active', $this->attributes) ? (bool) $value : true;
    }

    /** Capabilities as a plain array, never null */
    public function capabilityList(): array
    {
        return is_array($this->capabilities) ? $this->capabilities : [];
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilityList(), true);
    }

    public function hasAnyCapability(array $capabilities): bool
    {
        return count(array_intersect($capabilities, $this->capabilityList())) > 0;
    }

    /** Human-readable role, for the users list and the activity log */
    public function roleLabel(): string
    {
        if ($this->is_admin) {
            return 'Admin';
        }

        $labels = array_map(fn ($c) => ucfirst($c), $this->capabilityList());

        return $labels ? implode(' + ', $labels) : 'No access';
    }
}
