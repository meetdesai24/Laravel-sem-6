<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitLog extends Model
{
    protected $fillable = [
        'visit_id', 'check_in_time', 'check_out_time', 'checked_in_by', 'checked_out_by', 'remarks',
    ];

    protected $casts = [
        'check_in_time'  => 'datetime',
        'check_out_time' => 'datetime',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }
}
