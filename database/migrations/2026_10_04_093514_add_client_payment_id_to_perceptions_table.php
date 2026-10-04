<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perceptions', function (Blueprint $table) {
            $table->string('client_payment_id')
                ->nullable()
                ->unique()
                ->after('reference')
                ->comment('Identifiant unique du paiement envoyé par le POS');
        });
    }

    public function down(): void
    {
        Schema::table('perceptions', function (Blueprint $table) {
            $table->dropUnique([
                'client_payment_id',
            ]);


            $table->dropColumn('client_payment_id');
        });
    }
};
