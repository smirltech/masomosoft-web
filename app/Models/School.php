<?php

namespace App\Models;

use App\Traits\HasAvatar;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasUlids, HasAvatar;

    protected $fillable = [
        'name',
        'code',
        'logo',
        'address',
        'phone',
        'email',
        'website',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the URL of the school's logo.
     */
    public function getLogoAttribute($value): ?string
    {

        if (!$value && $this->avatar) {
            $this->update(['logo' => $this->avatar]);
        }
        return $value;
    }
}
