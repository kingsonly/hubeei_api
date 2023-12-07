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
        Schema::create('engagement_options', function (Blueprint $table) {
            $table->id();
            $table->integer("engagment_id");
            $table->integer("answer");
            $table->strin("answer_rank")->comment("to asertain if an option is a possitive or a negetive option");
            $table->integer("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engagement_options');
    }
};
