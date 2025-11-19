<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrar datos existentes de house_id a la tabla pivote
        $images = DB::table('images')
            ->whereNotNull('house_id')
            ->select('id', 'house_id')
            ->get();

        foreach ($images as $image) {
            DB::table('image_house')->insert([
                'image_id' => $image->id,
                'house_id' => $image->house_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Eliminar la columna house_id de la tabla images
        Schema::table('images', function (Blueprint $table) {
            $table->dropForeign(['house_id']);
            $table->dropColumn('house_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar la columna house_id
        Schema::table('images', function (Blueprint $table) {
            $table->foreignId('house_id')->nullable()->constrained()->cascadeOnDelete();
        });

        // Migrar datos de vuelta desde la tabla pivote
        $pivotRecords = DB::table('image_house')
            ->select('image_id', 'house_id')
            ->get();

        foreach ($pivotRecords as $record) {
            // Solo migrar el primer registro si hay múltiples casas excluidas
            $exists = DB::table('images')
                ->where('id', $record->image_id)
                ->whereNotNull('house_id')
                ->exists();

            if (! $exists) {
                DB::table('images')
                    ->where('id', $record->image_id)
                    ->update(['house_id' => $record->house_id]);
            }
        }
    }
};
