<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const CHECKED_IN = 'checked_in';
    public const CHECKED_OUT = 'checked_out';
    public const EXPIRED = 'expired';

    public const STATUSES = [
        self::PENDING, self::APPROVED, self::REJECTED,
        self::CHECKED_IN, self::CHECKED_OUT, self::EXPIRED,
    ];

    protected $fillable = [
        'visitor_id', 'host_id', 'purpose', 'visit_date', 'expected_in', 'expected_out',
        'status', 'qr_token', 'approved_by', 'remarks',
    ];

    protected $casts = ['visit_date' => 'date'];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function log(): HasOne
    {
        return $this->hasOne(VisitLog::class);
    }

    /** Pending requests the visitor has confirmed with an OTP (what a host should see). */
    public function scopeAwaitingHost(Builder $query): Builder
    {
        return $query->where('status', self::PENDING)
            ->whereHas('visitor', fn ($q) => $q->where('is_verified', true));
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::APPROVED    => 'success',
            self::REJECTED    => 'danger',
            self::CHECKED_IN  => 'primary',
            self::CHECKED_OUT => 'secondary',
            self::EXPIRED     => 'dark',
            default           => 'warning',
        };
    }

    public function getHasPassAttribute(): bool
    {
        return in_array($this->status, [self::APPROVED, self::CHECKED_IN], true) && (bool) $this->qr_token;
    }
}
