<?php

namespace Webkul\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Core\Contracts\Address as AddressContract;
// use Webkul\Customer\Models\Customer; // Disabled - Customer module disabled

abstract class Address extends Model implements AddressContract
{
    /**
     * Table.
     *
     * @var string
     */
    protected $table = 'addresses';

    /**
     * Guarded.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Castable.
     *
     * @var array
     */
    protected $casts = [
        'use_for_shipping' => 'boolean',
        'default_address'  => 'boolean',
    ];

    /**
     * Get all the attributes for the attribute groups.
     */
    public function getNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Get the customer record associated with the address.
     */
    public function customer(): BelongsTo
    {
        // Customer module is disabled - return a dummy relationship
        if (class_exists(\Webkul\Customer\Models\Customer::class)) {
            return $this->belongsTo(\Webkul\Customer\Models\Customer::class);
        }
        
        // Return a dummy relationship if Customer module is not available
        return $this->belongsTo(\Illuminate\Database\Eloquent\Model::class, 'customer_id');
    }
}
