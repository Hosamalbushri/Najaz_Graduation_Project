<?php

namespace Najaz\Citizen\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Najaz\Citizen\Contracts\Citizen as CitizenContract;

class Citizen extends Model implements CitizenContract
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'citizens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'email',
        'phone',
        'national_id',
        'image',
        'status',
        'password',
        'api_token',
        'citizen_type_id',
        'is_verified',
        'identity_verification_status',
        'device_token',
        'token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status'                       => 'integer',
        'is_verified'                  => 'boolean',
        'identity_verification_status' => 'boolean',
        'citizen_type_id'              => 'integer',
    ];

    /**
     * Get the citizen type that owns the citizen.
     */
    public function citizenType(): BelongsTo
    {
        return $this->belongsTo(CitizenTypeProxy::modelClass(), 'citizen_type_id');
    }

    /**
     * Get the identity verification for the citizen.
     * Each citizen can have only one identity verification.
     */
    public function identityVerification(): HasOne
    {
        return $this->hasOne(IdentityVerificationProxy::modelClass(), 'citizen_id');
    }

    /**
     * Get service requests submitted by this citizen.
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(\Najaz\Request\Models\ServiceRequestProxy::modelClass(), 'citizen_id');
    }

    /**
     * Get service requests where this citizen is a beneficiary.
     */
    public function serviceRequestsAsBeneficiary(): BelongsToMany
    {
        return $this->belongsToMany(
            \Najaz\Request\Models\ServiceRequestProxy::modelClass(),
            'service_request_beneficiaries',
            'citizen_id',
            'service_request_id'
        )->withPivot('group_code')
            ->withTimestamps();
    }

    /**
     * Get notes for this citizen.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(\Najaz\Citizen\Models\CitizenNoteProxy::modelClass(), 'citizen_id');
    }
}
