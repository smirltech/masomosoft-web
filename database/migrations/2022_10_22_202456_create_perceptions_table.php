<?php

use App\Enums\Devise;
use App\Models\Annee;
use App\Models\Frais;
use App\Models\Inscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('perceptions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('reference')->unique();
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(Frais::class)->constrained();
            $table->foreignIdFor(Inscription::class)->constrained();
            $table->foreignIdFor(Annee::class)->constrained();
            $table->string('custom_property')->nullable()->comment('Par rapport à la fréquence, la perception concerne quelle periode');
            $table->double('montant')->nullable()->comment('Montant payé');
            $table->string('devise')->default(Devise::USD->value);
            $table->double('frais_montant')->nullable()->comment('Montant a payer');
            $table->string('paid_by')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->date('due_date')->default(Carbon::now()->format('Y-m-d'));
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
