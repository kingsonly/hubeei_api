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
        Schema::create('hub_category_contents', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("content_type");
            $table->string("content_description");
            $table->string("content");
            $table->string("thumbnail");
            $table->integer("with_engagement")->default(0);
            $table->integer("hub_category_id");
            $table->integer("position")->nullable()->comment("all content should be ordered by possition");
            $table->integer("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hub_category_contents');
    }
};
