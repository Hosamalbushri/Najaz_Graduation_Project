<?php

namespace Najaz\Request\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Najaz\Request\Models\ServiceRequest;

class ServiceRequestCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public ServiceRequest $serviceRequest
    ) {}
}

