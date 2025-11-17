<?php

namespace Najaz\Request\Contracts;

interface Sequencer
{
    /**
     * Create and return the next sequence number for service request.
     */
    public function generate(): string;
}

