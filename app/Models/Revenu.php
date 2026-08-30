<?php

namespace App\Models;

use App\Enums\Devise;
use App\Enums\RevenuType;
use App\Traits\HasMontant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Revenu extends Model implements Auditable
{
    use HasFactory, SoftDeletes, HasUlids, \OwenIt\Auditing\Auditable, HasMontant;


    public $guarded = [];

    protected $casts = [
        'type' => RevenuType::class,
        'devise' => Devise::class
    ];

    protected static function booted(): void
    {
        // set user on creating
        static::creating(function (Revenu $model) {
            $model->user_id = $model->user_id ?? auth()->id();
            $model->annee_id ??= Annee::id();
        });
    }

    public function annee(): BelongsTo
{
    return $this->belongsTo(Annee::class);
}

public function scopeForAnnee($query, int $anneeId)
{
    return $query->where('annee_id', $anneeId);
}

    public static function dataOfLast($days = 7): array
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $lDate = Carbon::now()->subDays($i);
            $data[] = self::whereDate('created_at', '=', $lDate)->sum('montant');
        }

        return $data;
    }

    public static function sommeBetween($annee_id, $ddebut, $dfin)
    {
        $debut = Carbon::parse($ddebut)->startOfDay();
        $fin = Carbon::parse($dfin)->endOfDay();

        return self::whereBetween('created_at', [$debut, $fin])->sum('montant');
    }

    public static function total($devise = null)
    {
        return self::when($devise, function ($query) use ($devise) {
            return $query->where('devise', $devise);
        })->sum('montant');
    }

    //scope of cdf
    public function scopeCDF($query)
    {
        return $query->where('devise', Devise::CDF);
    }

    //scope of usd
    public function scopeUSD($query)
    {
        return $query->where('devise', Devise::USD);
    }
}
