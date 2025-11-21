<?php

namespace Najaz\Citizen\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Najaz\Citizen\Contracts\CitizenNote as CitizenNoteContract;

class CitizenNote extends Model implements CitizenNoteContract
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'citizen_notes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'citizen_id',
        'note',
        'citizen_notified',
        'admin_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'citizen_notified' => 'boolean',
    ];

    /**
     * Get the citizen that owns the note.
     */
    public function citizen(): BelongsTo
    {
        return $this->belongsTo(CitizenProxy::modelClass(), 'citizen_id');
    }

    /**
     * Get the admin who created the note.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(\Webkul\User\Models\Admin::class, 'admin_id');
    }
}

