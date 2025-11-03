<?php

namespace Najaz\Citizen\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
}
