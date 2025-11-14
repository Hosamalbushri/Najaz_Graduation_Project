<?php

namespace Najaz\Citizen\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Najaz\Citizen\Contracts\IdentityVerification as IdentityVerificationContract;

class IdentityVerification extends Model implements IdentityVerificationContract
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'identity_verifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'citizen_id',
        'status',
        'documents',
        'notes',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'documents'   => 'array',
    ];

    /**
     * Get the citizen that owns the identity verification.
     */
    public function citizen(): BelongsTo
    {
        return $this->belongsTo(CitizenProxy::modelClass(), 'citizen_id');
    }

    /**
     * Get the admin who reviewed the identity verification.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\Webkul\User\Models\Admin::class, 'reviewed_by');
    }
}













