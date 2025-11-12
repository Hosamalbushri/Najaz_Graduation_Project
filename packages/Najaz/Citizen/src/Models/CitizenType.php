<?php

namespace Najaz\Citizen\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Najaz\Service\Models\ServiceProxy;
use Najaz\Citizen\Contracts\CitizenType as CitizenTypeContract;

class CitizenType extends Model implements CitizenTypeContract
{
    use HasFactory;

    protected $table = 'citizen_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'is_user_defined',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        // Add your attribute casts here
    ];

    /**
     * Get the citizens for this type.
     */
    public function citizens(): HasMany
    {
        return $this->hasMany(CitizenProxy::modelClass());
    }

    /**
     * Get the services associated with the citizen type.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            ServiceProxy::modelClass(),
            'citizen_type_service',
            'citizen_type_id',
            'service_id'
        )->withTimestamps();
    }
}
