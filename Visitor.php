<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Visitor extends Model
{
    public const ID_PROOFS = [
        'aadhaar'         => 'Aadhaar',
        'driving_licence' => 'Driving licence',
        'pan'             => 'PAN',
        'voter_id'        => 'Voter ID',
        'passport'        => 'Passport',
    ];

    protected $fillable = [
        'full_name', 'email', 'phone', 'company', 'address', 'id_proof_type', 'id_proof_number',
        'photo', 'is_verified', 'is_blacklisted', 'blacklist_reason',
    ];

    protected $casts = [
        'id_proof_number' => 'encrypted',
        'is_verified'     => 'boolean',
        'is_blacklisted'  => 'boolean',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function otpVerifications(): HasMany
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }
}
