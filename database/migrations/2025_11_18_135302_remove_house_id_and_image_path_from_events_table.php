<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // if (Schema::hasColumn('events', 'house_id')) {
            //     $table->dropForeign(['house_id']);
            //     $table->dropColumn('house_id');
            // }

            if (Schema::hasColumn('events', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // $table->foreignId('house_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('image_path')->nullable();
        });
    }
};
