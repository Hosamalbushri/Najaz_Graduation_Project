<?php

namespace Webkul\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Core\Contracts\SubscribersList as SubscribersListContract;
use Webkul\Core\Database\Factories\SubscriberListFactory;
// use Webkul\Customer\Models\CustomerProxy; // Disabled - Customer module disabled

class SubscribersList extends Model implements SubscribersListContract
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'subscribers_list';

    /**
     * Fillable properties of the model.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'is_subscribed',
        'token',
        'customer_id',
        'channel_id',
    ];

    /**
     * Hide the token attribute to the model.
     *
     * @var array
     */
    protected $hidden = ['token'];

    /**
     * Get the customer associated with the subscription.
     */
    public function customer(): BelongsTo
    {
        // Customer module is disabled - return a dummy relationship
        if (class_exists(\Webkul\Customer\Models\CustomerProxy::class)) {
            return $this->belongsTo(\Webkul\Customer\Models\CustomerProxy::modelClass());
        }
        
        // Return a dummy relationship if Customer module is not available
        return $this->belongsTo(\Illuminate\Database\Eloquent\Model::class, 'customer_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return SubscriberListFactory::new();
    }
}
