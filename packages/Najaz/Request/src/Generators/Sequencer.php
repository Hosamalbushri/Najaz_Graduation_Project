<?php

namespace Najaz\Request\Generators;

use Najaz\Request\Contracts\Sequencer as SequencerContract;

class Sequencer implements SequencerContract
{
    /**
     * Length.
     *
     * @var string
     */
    public $length;

    /**
     * Prefix.
     *
     * @var string
     */
    public $prefix;

    /**
     * Suffix.
     *
     * @var string
     */
    public $suffix;

    /**
     * Generator class.
     *
     * @var string
     */
    public $generatorClass;

    /**
     * Last id.
     *
     * @var int
     */
    public $lastId = 0;

    /**
     * Set length from the config.
     *
     * @param  string  $configKey
     * @return void
     */
    public function setLength($configKey)
    {
        $this->length = config($configKey);
    }

    /**
     * Set prefix from the config.
     *
     * @param  string  $configKey
     * @return void
     */
    public function setPrefix($configKey)
    {
        $this->prefix = config($configKey);
    }

    /**
     * Set suffix from the config.
     *
     * @param  string  $configKey
     * @return void
     */
    public function setSuffix($configKey)
    {
        $this->suffix = config($configKey);
    }

    /**
     * Set generator class from the config.
     *
     * @param  string  $configKey
     * @return void
     */
    public function setGeneratorClass($configKey)
    {
        $this->generatorClass = config($configKey);
    }

    /**
     * Resolve generator class.
     *
     * @return string
     */
    public function resolveGeneratorClass()
    {
        if (
            $this->generatorClass !== ''
            && class_exists($this->generatorClass)
            && in_array(SequencerContract::class, class_implements($this->generatorClass), true)
        ) {
            return app($this->generatorClass)->generate();
        }

        return $this->generate();
    }

    /**
     * Create and return the next sequence number for service request.
     */
    public function generate(): string
    {
        return $this->prefix.sprintf(
            "%0{$this->length}d",
            ($this->lastId + 1)
        ).($this->suffix);
    }
}

