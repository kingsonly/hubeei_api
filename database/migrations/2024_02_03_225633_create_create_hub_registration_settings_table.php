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
        Schema::create('create_hub_registration_settings', function (Blueprint $table) {
            $table->id();
            $table->integer("hub_id")->comment("the hub id");
            $table->string("structure")->comment("the input structure");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('create_hub_registration_settings');
    }
};
