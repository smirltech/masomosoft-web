<?php

namespace App\Models;

use App\Enums\Devise;
use App\Enums\MinervalMonth;
use App\Enums\FraisType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;

class Frais extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory, Auditable;

    public $guarded = [];

    protected $casts = [
        'type' => FraisType::class,
        'frequence' => MinervalMonth::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'devise' => Devise::class,
    ];

    public static function montantFraisType(int $annee_id, FraisType $type)
    {
        return Perception::whereHas('frais', function ($q) use ($type) {
            $q->where('type', $type->value);
        })->where('annee_id', $annee_id)->sum('montant');
    }

    public function getLabelAttribute(): string
    {
        return $this->nom . ' - ' . $this->montant . ' ' . $this->devise->value;
    }

    public static function montantFraisTypeOf(int $annee_id, FraisType $type, int $days = 7)
    {
        $edate = Carbon::now();
        $sdate = Carbon::now()->subDays($days);

        return Perception::whereHas('frais', function ($q) use ($type) {
            $q->where('type', $type->value);
        })->where('annee_id', $annee_id)->whereDate('created_at', '>=', $sdate)->whereDate('created_at', '<=', $edate)->sum('montant');
    }

    public static function paidFraisType(int $annee_id, FraisType $type)
    {
        return Perception::whereHas('frais', function ($q) use ($type) {
            $q->where('type', $type->value);
        })->where('annee_id', $annee_id)->sum('paid');
    }


    public function paidCDF()
    {
        return $this->perceptions()->cdf()->sum('montant');
    }

    public function paidUSD()
    {
        return $this->perceptions()->usd()->sum('montant');
    }

    public static function paidFraisTypeOf(int $annee_id, FraisType $type, int $days = 7)
    {
        $edate = Carbon::now();
        $sdate = Carbon::now()->subDays($days);

        return Perception::whereHas('frais', function ($q) use ($type) {
            $q->where('type', $type->value);
        })->where('annee_id', $annee_id)->whereDate('created_at', '>=', $sdate)->whereDate('created_at', '<=', $edate)->sum('paid');
    }

    public static function sommeFraisByTypeBetween(int $annee_id, $ddebut, $dfin): array
    {
        $debut = Carbon::parse($ddebut)->startOfDay();
        $fin = Carbon::parse($dfin)->endOfDay();
        $data = [];
        foreach (FraisType::cases() as $type) {
            $data[$type->label()] = Perception::whereHas('frais', function ($q) use ($type) {
                $q->where('type', $type->value);
            })->where('annee_id', $annee_id)->whereBetween('created_at', [$debut, $fin])->sum('montant');
        }

        return $data;
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if ($model->annee_id == null) {
                $model->annee_id = Annee::id();
            }
        });
    }

    public function montantPerceptions()
    {
        return $this->perceptions()->sum('montant');
    }

    public function perceptions(): HasMany
    {
        return $this->hasMany(Perception::class);
    }

    public function montantPerceptionsOf($days = 7)
    {
        $edate = Carbon::now();
        $sdate = Carbon::now()->subDays($days);

        return $this->perceptions()->whereDate('created_at', '>=', $sdate)->whereDate('created_at', '<=', $edate)->sum('montant');
    }

//    public function classable(): MorphTo
//    {
//        return $this->morphTo();
//    }
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

//    public function getClassableAttribute(): mixed
//    {
//        if (str_ends_with($this->classable_type, 'Classe')) {
//            return Classe::find($this->classable_id);
//        } elseif (str_ends_with($this->classable_type, 'Option')) {
//            return Option::find($this->classable_id);
//        } elseif (str_ends_with($this->classable_type, 'Option')) {
//            return Option::find($this->classable_id);
//        } elseif (str_ends_with($this->classable_type, 'Section')) {
//            return Section::find($this->classable_id);
//        } else {
//            return null;
//        }
//    }
}
