<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * L'image est stockée en base64 (upload converti côté front) :
     * une colonne VARCHAR(255) est trop courte, on passe en TEXT nullable.
     */
    public function up(): void
    {
        Schema::table('collection_items', function (Blueprint $table) {
            $table->text('image')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('collection_items', function (Blueprint $table) {
            $table->string('image')->nullable(false)->change();
        });
    }
};
