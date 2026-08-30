<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Annee extends Model
{
    public $guarded = [];


    protected $casts = [
        'encours' => 'boolean',
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    protected $appends = [
        'start_year',
        'end_year',
    ];



    /**
     * Renvoie l'id de l'année scolaire en cours
     */
    public static function id(): int
    {
        return self::encours()->id;
    }

    /**
     * Renvoie l'année scolaire en cours
     */
    public static function encours(): self
    {
        return self::where('encours', true)->latest()->first();

    }

    public static function start(): Carbon
    {
        return self::encours()->date_debut;

    }

    public function scopeForAnnee($query, int $anneeId)
{
    return $query->where('annee_id', $anneeId);
}

    public static function end(): Carbon
    {
        return self::encours()->date_fin;

    }
    public function annee(): BelongsTo
{
    return $this->belongsTo(Annee::class);
}

    public function getNomEditAttribute(): string
    {
        return '';
    }

    /**
     * @deprecated deprecated since version 1.0
     */
    public function getNomAttribute(): string
    {
        return $this->code;
    }

    public function getNameAttribute(): string
    {
        return $this->code;
    }

    public function getCodeAttribute(): string
    {
        return $this->start_year . '-' . $this->end_year;
    }

    public function getStartYearAttribute(): ?string
    {
        return $this->dateDebut()?->year;
    }

    // get date debut
    public function dateDebut(): ?Carbon
    {
        return Carbon::parse($this->date_debut);
    }

    public function getEndYearAttribute(): ?string
    {
        return $this->dateFin()?->year;
    }

    public function dateFin(): ?Carbon
    {
        return Carbon::parse($this->date_fin);
    }
}
