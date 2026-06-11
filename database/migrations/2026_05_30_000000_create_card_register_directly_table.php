<?php

use App\Models\RegisterDirectly;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_register_directly', function (Blueprint $table) {
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('register_directly_id')->constrained('register_directlies')->cascadeOnDelete();
            $table->primary(['card_id', 'register_directly_id']);
        });

        foreach (RegisterDirectly::whereNotNull('card_id')->cursor() as $record) {
            $record->cards()->attach($record->card_id);
        }

        Schema::table('register_directlies', function (Blueprint $table) {
            $table->dropForeign(['card_id']);
            $table->dropColumn('card_id');
        });
    }

    public function down(): void
    {
        Schema::table('register_directlies', function (Blueprint $table) {
            $table->foreignId('card_id')->nullable()->constrained('cards')->cascadeOnDelete();
        });

        Schema::dropIfExists('card_register_directly');
    }
};
