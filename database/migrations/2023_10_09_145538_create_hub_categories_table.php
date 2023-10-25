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
        Schema::create('hub_categories', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->integer("hub_id");
            $table->integer("position")->nullable()->comment("all category should be ordered by possition");
            $table->integer("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hub_categories');
    }
};
