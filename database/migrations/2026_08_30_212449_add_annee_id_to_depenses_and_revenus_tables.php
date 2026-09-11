<?php

use App\Models\Annee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('depenses', function (Blueprint $table) {
            $table->foreignIdFor(Annee::class)
                ->nullable()
                ->after('user_id')
                ->constrained();
        });

        Schema::table('revenus', function (Blueprint $table) {
            $table->foreignIdFor(Annee::class)
                ->nullable()
                ->after('user_id')
                ->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('depenses', function (Blueprint $table) {
            $table->dropForeign(['annee_id']);
            $table->dropColumn('annee_id');
        });

        Schema::table('revenus', function (Blueprint $table) {
            $table->dropForeign(['annee_id']);
            $table->dropColumn('annee_id');
        });
    }
};
