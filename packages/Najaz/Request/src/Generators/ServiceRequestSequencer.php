<?php

namespace Najaz\Request\Generators;

use Najaz\Request\Models\ServiceRequest;

class ServiceRequestSequencer extends Sequencer
{
    /**
     * Create service request sequencer instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setAllConfigs();
    }

    /**
     * Set all configs.
     *
     * @return void
     */
    public function setAllConfigs()
    {
        $this->prefix = config('request.service_request.increment_id_prefix');

        $this->length = config('request.service_request.increment_id_length');

        $this->suffix = config('request.service_request.increment_id_suffix');

        $this->generatorClass = config('request.service_request.increment_id_generator');

        $this->lastId = $this->getLastId();
    }

    /**
     * Get last id.
     *
     * @return int
     */
    public function getLastId()
    {
        $lastRequest = ServiceRequest::query()->orderBy('id', 'desc')->limit(1)->first();

        return $lastRequest ? $lastRequest->id : 0;
    }
}

