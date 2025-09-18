<?php

namespace Modules\OrganizationAdmin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected $guarded = [
        'id',
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'data' => 'array',
        ];
    }

    public function account(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        // If the attribute exists in the database table, return it normally
        if (array_key_exists($key, $this->attributes) || $this->hasColumn($key)) {
            return parent::getAttribute($key);
        }

        // Otherwise, check if it's in the data array
        $data = $this->getAttribute('data') ?? [];

        return $data[$key] ?? null;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // If the attribute exists in the database table, set it normally
        if (array_key_exists($key, $this->attributes) || $this->hasColumn($key)) {
            return parent::setAttribute($key, $value);
        }

        // Otherwise, store it in the data array
        $data = $this->getAttribute('data') ?? [];
        $data[$key] = $value;

        return $this->setAttribute('data', $data);
    }

    /**
     * Determine if the given attribute exists as a column on the table.
     *
     * @param  string  $key
     * @return bool
     */
    protected function hasColumn($key)
    {
        return \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), $key);
    }
}
