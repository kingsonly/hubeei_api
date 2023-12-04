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
        Schema::create('content_views', function (Blueprint $table) {
            $table->id();
            $table->string("users_id")->comment("could be cockies or users_id");
            $table->string("content_id")->comment("the liked content");
            $table->string("users_type")->comment("either as subscriber or guest");
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_views');
    }
};
