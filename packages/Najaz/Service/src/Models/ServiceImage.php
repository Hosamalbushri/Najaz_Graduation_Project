<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Najaz\Service\Contracts\ServiceImage as ServiceImageContract;
use Najaz\Service\Models\ServiceProxy;

class ServiceImage extends Model implements ServiceImageContract
{
    /**
     * Timestamp.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Fillable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'path',
        'service_id',
        'position',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['url'];

    /**
     * Get the service that owns the image.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(ServiceProxy::modelClass());
    }

    /**
     * Get image url for the service image.
     *
     * @return string
     */
    public function url()
    {
        return Storage::url($this->path);
    }

    /**
     * Get image url for the service image.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return $this->url();
    }
}

