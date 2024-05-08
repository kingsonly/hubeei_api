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
        Schema::create('hub_subsribtion_required_fields', function (Blueprint $table) {
            $table->id();
            $table->integer("hub_registration_settings_id");
            $table->string("name");
            $table->string("type");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hub_subsribtion_required_fields');
    }
};
