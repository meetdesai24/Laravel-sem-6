<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpVerification extends Model
{
    protected $fillable = ['visitor_id', 'otp_hash', 'expires_at', 'attempts', 'is_used'];

    protected $hidden = ['otp_hash'];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used'    => 'boolean',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}
